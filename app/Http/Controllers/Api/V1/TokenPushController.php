<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\TokenPush;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TokenPushController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string|min:3',
                'device_id' => 'required|string|min:3',
            ]);

            $tokenpush_exist = TokenPush::where('token_push', $request->token)
                ->where('device_id', $request->device_id)
                ->count();

            if ($tokenpush_exist) {
                return response(
                    [
                        'success' => true,
                        'message' => 'reported data already exist',
                    ],
                    200
                );
            }

            $tokenpush = new TokenPush();
            $tokenpush->token_push = $request->token;
            $tokenpush->device_id = $request->device_id;

            $tokenpush->save();

            return response($tokenpush, 201);
        } catch (\Throwable $th) {
            throw new Exception('Ops! ocorreu um erro!');
        }
    }

    public function attach(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string|min:3|max:255',
            ]);

            $tokenpush = TokenPush::where(
                'token_push',
                $request->token
            )->first();

            if (!$tokenpush) {
                $tokenpush = new TokenPush();
                $tokenpush->token_push = $request->token;
                $tokenpush->device_id = $request->device_id;
            }

            $tokenpush->user_id = Auth::id();

            $tokenpush->save();

            return response($tokenpush, 201);
        } catch (\Throwable $th) {
            throw new Exception('Ops! ocorreu um erro!');
        }
    }

    public function dettach(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string|min:3|max:255',
            ]);

            $tokenpush = TokenPush::where(
                'token_push',
                $request->token
            )->first();

            if (!$tokenpush) {
                return response(
                    [
                        'success' => false,
                        'message' => 'reported data token not exist',
                    ],
                    400
                );
            }

            if ($tokenpush->user_id !== Auth::id()) {
                return response(
                    [
                        'success' => false,
                        'message' => 'permission denied',
                    ],
                    403
                );
            }

            $tokenpush->user_id = null;

            $tokenpush->save();

            return response($tokenpush, 200);
        } catch (\Throwable $th) {
            throw new Exception('Ops! ocorreu um erro!');
        }
    }

    public function attempts(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string|min:3|max:255',
            ]);

            $tokenpush = TokenPush::where(
                'token_push',
                $request->token
            )->first();

            if (!$tokenpush) {
                return response(
                    [
                        'success' => false,
                        'message' =>
                            'reported data token or device_id not exist',
                    ],
                    400
                );
            }

            if ($tokenpush->user_id !== Auth::id()) {
                return response(
                    [
                        'success' => false,
                        'message' => 'permission denied',
                    ],
                    403
                );
            }

            $tokenpush->attempts++;

            $tokenpush->save();

            return response($tokenpush, 200);
        } catch (\Throwable $th) {
            throw new Exception('Ops! ocorreu um erro!');
        }
    }
}
