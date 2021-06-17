<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\V1\WebhookController;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use Exception;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Formatter\JsonFormatter;

class PaymentController extends Controller
{
    public function status_change(Request $request, WebhookController $webhook)
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

        $beautifiedJsonObjectString = $request;

        // Use the new logger
        $logger->info("/api/v1/payments/status-change", [
            'object' => $beautifiedJsonObjectString,
            Auth::id(),
        ]);

        try {
            $token = JWTAuth::setToken($request->token);
            JWTAuth::payload($request->token)->toArray();
            if (!($claim = JWTAuth::getPayload())) {
                return response()->json(['message' => 'type_not_found'], 404);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['message' => 'token_expired'], 404);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['message' => 'token_invalid'], 404);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['message' => 'token_absent'], 404);
        }

        if ($claim['sub'] == 'google-webhook') {
            $webhook->storeGoogle($request);
        } elseif ($claim['sub'] == 'apple-webhook') {
            $webhook->storeApple($request);
        }

        // the token is valid and we have exposed the contents
        return response()->json([
            'message' => 'token_valid',
            'type' => $claim['sub'],
        ]);
    }
}
