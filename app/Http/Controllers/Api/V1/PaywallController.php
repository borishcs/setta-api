<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EmailController;
use App\Http\Requests\Paywall\SubscribeStoreRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PaywallController extends Controller
{
    private $stripe;

    public function __construct()
    {
        try {
            $this->stripe = new \Stripe\StripeClient(env('STRIPE_KEY'));
        } catch (\Exception $err) {
            return [
                'success' => false,
                'error' => $err,
            ];
        }
    }

    public function getStripeId($user)
    {
        $findCustomer = $this->stripe->customers->all([
            'email' => $user->email,
        ]);

        if (!count($findCustomer->data)) {
            return false;
        }

        $user->stripe_id = $findCustomer->data[0]->id;
        $user->save();

        return true;
    }

    public function products()
    {
        return $this->stripe->products->all();
    }

    public function plans()
    {
        return $this->stripe->plans->all();
    }

    public function coupons()
    {
        return $this->stripe->coupons->all();
    }

    public function coupon_verify($code)
    {
        return $this->stripe->coupons->retrieve($code, []);
    }

    public function customer()
    {
        $user = auth()->user();

        if (!$user->stripe_id || $user->stripe_id == '') {
            $this->getStripeId($user);
        }

        return $this->stripe->customers->retrieve($user->stripe_id, []);
    }

    public function status()
    {
        $user = auth()->user();

        if (!$user->stripe_id || $user->stripe_id == '') {
            $this->getStripeId($user);
        }

        try {
            $subscriptions = $this->stripe->subscriptions->all([
                'customer' => $user->stripe_id,
            ]);

            if (!$subscriptions) {
                return response(
                    ['response' => 'Não foi possível se conectar ao servidor.'],
                    403
                );
            }

            if (!isset($subscriptions->data) || !count($subscriptions->data)) {
                return response(
                    ['response' => 'Nenhuma assinatura encontrada'],
                    200
                );
            }

            $last_subscription = $subscriptions->data[0];

            $subscription_start = Carbon::parse(
                $last_subscription->current_period_start
            )->format('Y-m-d H:i:s');

            $subscription_end = Carbon::parse(
                $last_subscription->current_period_end
            )->format('Y-m-d H:i:s');

            return [
                'status' => $last_subscription->status,
                'start' => $subscription_start,
                'end' => $subscription_end,
                'plan' => $last_subscription->plan,
            ];
        } catch (Exception $err) {
            return response($err, 403);
        }
    }

    public function subscribe(SubscribeStoreRequest $request)
    {
        $user = auth()->user();
        $data = [];

        if (!$user->stripe_id || $user->stripe_id == '') {
            $this->getStripeId($user);
        }

        $data['customer'] = $user->stripe_id;
        $data['items'] = [
            [
                'quantity' => 1,
                'price' => $request->plan,
            ],
        ];

        if ($request->has('coupon')) {
            $data['coupon'] = $request->coupon;
        }

        // Verificar se usuário já possui alguma assinatura ativada
        $user_subscriptions = $this->stripe->subscriptions->all([
            'customer' => $user->stripe_id,
        ]);

        if (
            $user_subscriptions &&
            count($user_subscriptions->data) &&
            $user_subscriptions->data[0]
        ) {
            $current_subscription_status = $user_subscriptions->data[0]->status;

            if ($current_subscription_status === 'active') {
                return response(
                    ['response' => 'Usuário já possui uma assinatura ativa.'],
                    200
                );
            }
        }

        // Criar assinatura
        $subscription = $this->stripe->subscriptions->create($data);

        if (!$subscription->id) {
            return response(
                ['response' => 'Não foi possível realizar o pagamento.'],
                403
            );
        }

        $current_period_end = Carbon::createFromTimestamp(
            $subscription->current_period_end
        )->toDateTimeString();

        $user->stripe_subscription_end = $current_period_end;
        $user->save();

        return response($subscription, 201);
    }

    public function integrate(Request $request, EmailController $email)
    {
        if (!$request->has('api_version') || !$request->has('data')) {
            abort(404);
        }

        $supportedTypes = [
            'customer.subscription.updated',
            'customer.subscription.trial_will_end',
            'customer.subscription.pending_update_expired',
            'customer.subscription.pending_update_applied',
            'customer.subscription.deleted',
            'customer.subscription.created',
        ];

        $type = $request->type;

        if (!in_array($type, $supportedTypes)) {
            abort(404);
        }

        $webhook = $request->data;

        $status = $webhook['object']['status'];
        $customer_id = $webhook['object']['customer'];
        $subscription_end = $webhook['object']['current_period_end'];

        $user = User::where('stripe_id', $customer_id)->first();

        $email->send('financeiro@setta.co', 'Stripe', 'Paywall - Stripe', [
            'user_id' => $user->id,
            'customer_id' => $customer_id,
            'status' => $status,
            'type' => $type,
            'response' => $request->all(),
        ]);

        // Assinatura desativada => remover data de finalização
        if ($status !== 'active') {
            $user->stripe_subscription_end = null;
            $user->save();
            return true;
        }

        // Assinatura ativada => atualizar data de finalização
        $user->stripe_subscription_end = Carbon::parse(
            $subscription_end
        )->format('Y-m-d H:i:s');
        $user->save();

        return response('ready', 200);
    }
}
