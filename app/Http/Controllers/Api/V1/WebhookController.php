<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Services\VerifyReceipt;
use Exception;
use Illuminate\Support\Arr;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Formatter\JsonFormatter;

class WebhookController extends Controller
{
    public function storeGoogle(Request $request)
    {
        $notificationData = json_decode(
            base64_decode($request->message['data'])
        );

        $notification =
            $notificationData->subscriptionNotification->notificationType;

        $clientVerifyReceipt = new VerifyReceipt();
        $subscriptionID = $clientVerifyReceipt->getSubscriptionId(
            $notificationData->subscriptionNotification,
            $request->token
        );

        if ($notification == 1) {
            // (1) SUBSCRIPTION_RECOVERED: uma assinatura foi recuperada da suspensão de conta.
            $notificationType = 'SUBSCRIPTION_RECOVERED';

            $this->activate('google', $subscriptionID);
        } elseif ($notification == 2) {
            // (2) SUBSCRIPTION_RENEWED: uma assinatura ativa foi renovada.
            $notificationType = 'SUBSCRIPTION_RENEWED';

            $this->activate('google', $subscriptionID);
        } elseif ($notification == 3) {
            // (3) SUBSCRIPTION_CANCELED: uma assinatura foi cancelada de forma voluntária ou involuntária. Em um cancelamento voluntário, esse valor é enviado quando o usuário faz o cancelamento.
            $notificationType = 'SUBSCRIPTION_CANCELED';
            $this->inactivate('google', $subscriptionID);
        } elseif ($notification == 4) {
            // (4) SUBSCRIPTION_PURCHASED: uma nova assinatura foi comprada.
            $notificationType = 'SUBSCRIPTION_PURCHASED';
            $this->activate('google', $subscriptionID);
        } elseif ($notification == 5) {
            // (5) SUBSCRIPTION_ON_HOLD: uma assinatura entrou na suspensão de conta (se ativada).
            $notificationType = 'SUBSCRIPTION_ON_HOLD';
            $this->inactivate('google', $subscriptionID);
        } elseif ($notification == 6) {
            // (6) SUBSCRIPTION_IN_GRACE_PERIOD: uma assinatura entrou no período de carência (se ativado).
            $notificationType = 'SUBSCRIPTION_IN_GRACE_PERIOD';
        } elseif ($notification == 7) {
            // (7) SUBSCRIPTION_RESTARTED: o usuário reativou a assinatura em Play > Conta > Assinaturas (requer ativação da restauração de assinatura).
            $notificationType = 'SUBSCRIPTION_RESTARTED';
            $this->activate('google', $subscriptionID);
        } elseif ($notification == 8) {
            // (8) SUBSCRIPTION_PRICE_CHANGE_CONFIRMED: uma mudança no preço da assinatura foi confirmada pelo usuário.
            $notificationType = 'SUBSCRIPTION_PRICE_CHANGE_CONFIRMED';
        } elseif ($notification == 9) {
            // (9) SUBSCRIPTION_DEFERRED: o tempo de renovação de uma assinatura foi estendido.
            $notificationType = 'SUBSCRIPTION_DEFERRED';
        } elseif ($notification == 10) {
            // (10) SUBSCRIPTION_PAUSED: uma assinatura foi pausada.
            $this->inactivate('google', $subscriptionID);
            $notificationType = 'SUBSCRIPTION_PAUSED';
        } elseif ($notification == 11) {
            // (11) SUBSCRIPTION_PAUSE_SCHEDULE_CHANGED: a programação de uma pausa na assinatura foi alterada.
            $notificationType = 'SUBSCRIPTION_PAUSE_SCHEDULE_CHANGED';
            $this->inactivate('google', $subscriptionID);
        } elseif ($notification == 12) {
            // (12) SUBSCRIPTION_REVOKED: uma assinatura foi revogada pelo usuário antes do prazo de vencimento.
            $notificationType = 'SUBSCRIPTION_REVOKED';
            $this->inactivate('google', $subscriptionID);
        } elseif ($notification == 13) {
            // (13) SUBSCRIPTION_EXPIRED: a assinatura expirou.
            $notificationType = 'SUBSCRIPTION_EXPIRED';
            $this->inactivate('google', $subscriptionID);
        }

        return response($notificationType, 200);
    }

    public function storeApple(Request $request)
    {
        $shared_secret = env('APPLE_SHARED_SECRET');

        $array_original_transaction_id = [];

        $renewals_count = count(
            $request->unified_receipt['pending_renewal_info']
        );

        if ($renewals_count == 1) {
            $original_transaction_id =
                $request->unified_receipt['pending_renewal_info'][0][
                    'original_transaction_id'
                ];
            $auto_renew_status =
                $request->unified_receipt['pending_renewal_info'][0][
                    'auto_renew_status'
                ];
        } elseif ($renewals_count > 1) {
            $renewals = $request->unified_receipt['pending_renewal_info'];

            foreach ($renewals as $renewal) {
                $array_original_transaction_id = Arr::prepend(
                    $array_original_transaction_id,
                    $renewal['original_transaction_id']
                );

                if ($renewal['auto_renew_status'] == 1) {
                    $original_transaction_id =
                        $renewal['original_transaction_id'];
                    $auto_renew_status = $renewal['auto_renew_status'];
                    break;
                }
            }
        }

        if ($shared_secret == $request->password) {
            if (empty($auto_renew_status)) {
                //defino paid como false pois nenhuma assinatura esta ativa.
                $user = User::whereIn(
                    'subscription_id',
                    $array_original_transaction_id
                )->first();

                $user->premium_expire_at = null;
                $user->paid = false;
                $user->save();

                return;
            }

            if ($request->notification_type == 'INITIAL_BUY') {
                // Compra Inicial
                $this->activate('apple', $original_transaction_id);
            } elseif ($request->notification_type == 'CANCEL') {
                // Cancelamento
                $this->inactivate('apple', $original_transaction_id);
            } elseif ($request->notification_type == 'RENEWAL') {
                // Renovação
                $this->activate('apple', $original_transaction_id);
            } elseif ($request->notification_type == 'INTERACTIVE_RENEWAL') {
                // Renovação Interativa
                $this->activate('apple', $original_transaction_id);
            } elseif (
                $request->notification_type == 'DID_CHANGE_RENEWAL_PREF'
            ) {
                // Mudou a preferência de renovação
            } elseif (
                $request->notification_type == 'DID_CHANGE_RENEWAL_STATUS'
            ) {
                // Mudou o status de renovação
            }

            return response($request->notification_type, 200);
        } else {
            return response(
                [
                    'success' => false,
                    'message' => 'permission denied',
                ],
                403
            );
        }
    }

    public function activate($platform, $subscriptionID)
    {
        //log
        // Set the format
        $output = "%channel%.%level_name%: %message% %context% %extra%";
        $formatter = new LineFormatter($output);
        // Setup the logger
        $logger = new Logger('my_logger');
        $syslogHandler = new SyslogUdpHandler(
            'logs3.papertrailapp.com',
            '34704'
        );
        $syslogHandler->setFormatter($formatter);
        $logger->pushHandler($syslogHandler);

        $beautifiedJsonObjectString = $subscriptionID;

        // Use the new logger
        $logger->info("/api/v1/payments/status-change/webhook/activate", [
            'object' => $beautifiedJsonObjectString,
            Auth::id(),
        ]);

        try {
            if ($platform == 'google') {
                $user = User::where(
                    'subscription_id',
                    $subscriptionID
                )->first();

                if(!$user){
                    return response("subscription_id not found", 403);
                }
            }

            if ($platform == 'apple') {
                $user = User::where(
                    'subscription_id',
                    $subscriptionID
                )->first();

                if(!$user){
                    return response("subscription_id not found", 403);
                }

            }

            $user->premium_expire_at = null;
            $user->paid = true;
            $user->save();

            return response('success', 200);
        } catch (\Throwable $th) {
            //throw new Exception('Ops! erro ao executar o webhook!');
            return 200;
        }
    }

    public function inactivate($platform, $subscriptionID)
    {
        //try {
            if ($platform == 'google') {
                $user = User::where(
                    'subscription_id',
                    $subscriptionID
                )->first();

                if(!$user){
                    return response("subscription_id not found", 403);
                }
            }

            if ($platform == 'apple') {
                $user = User::where(
                    'subscription_id',
                    $subscriptionID
                )->first();

                if(!$user){
                    return response("subscription_id not found", 403);
                }
            }
            $user->premium_expire_at = null;
            $user->paid = false;
            $user->save();
        //} catch (\Throwable $th) {
         //   throw new Exception('Ops! erro ao executar o webhook!');
        //}
    }
}
