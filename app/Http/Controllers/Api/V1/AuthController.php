<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EmailController;
use App\Model\UserSocial;
use App\Model\LogLogin;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public $loginAfterSignUp = true;

    public function register(Request $request, EmailController $email)
    {
        $this->validate($request, [
            'email' => 'required|string|min:3|max:255',
            'password' => 'required|string|min:3|max:255',
            'name' => 'required|string|min:3|max:255',
            'timezone' => 'string|min:3|max:255',
        ]);
        try {
            $user_email = strtolower(trim($request->email));

            $user_exists = User::where('email', $user_email)->first();

            if ($user_exists) {
                $userWithoutPassword = User::where('email', $user_email)
                    ->with('socials')
                    ->first();

                if (
                    !$userWithoutPassword->password &&
                    count($userWithoutPassword->socials)
                ) {
                    // facebook e/ou google
                    $userSocials = ucwords(
                        $userWithoutPassword->socials->implode('type', ' ou ')
                    );

                    return response()->json([
                        'success' => false,
                        'message' =>
                        'Você já possui conta com este email use o ' .
                            $userSocials .
                            ' para fazer login.',
                    ]);
                }

                if ($this->loginAfterSignUp) {
                    return $this->login($request);
                }
            }

            // User
            // dd($request, $request->phone);
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = $request->password;

            $user->phone = $request->phone;
            $user->age = $request->age;
            $user->grace_period_days = 30;
            $user->grace_period_start_at = now();



            if ($request->has('timezone')) {
                $user->timezone = $request->timezone;
            }
            $user->profession = $request->profession;
            $user->interest = $request->interest;
            // dd($user);
            $user->save();

            $email->send(
                $user->email,
                false,
                $user->name,
                'Bem-vindo à Setta! Preparado para mudar sua vida?',
                '',
                $request->name,
                'emails.welcome'
            );

            if ($this->loginAfterSignUp) {
                return $this->login($request);
            }

            return response()->json(
                [
                    'data' => $user,
                ],
                200
            );
        } catch (\Throwable $th) {
            throw new Exception('Ops! ocorreu um erro!');
        }
    }

    public function login(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|string|min:3|max:255',
                'password' => 'required|string|min:3|max:255',
            ]);

            $user_email = strtolower(trim($request->email));
            $user_exists = User::where('email', $user_email)->first();

            if (!$user_exists) {
                return response()->json(
                    [
                        'errors' => [
                            'email' =>
                            'Email não encontrado, você precisa se cadastrar para fazer login.',
                        ],
                    ],
                    401
                );
            }

            if ($user_exists && !$user_exists->password) {
                return response()->json(
                    [
                        'errors' => [
                            'password' =>
                            'Você precisa  criar uma senha para se cadastrar.',
                        ],
                    ],
                    401
                );
            }

            $credentials = [
                'email' => $user_exists->email,
                'password' => $request->password,
            ];
            $token = null;

            if (!($token = JWTAuth::attempt($credentials))) {
                return response()->json(
                    [
                        'errors' => [
                            'password' =>
                            'Verifique sua senha e tente novamente.',
                        ],
                    ],
                    401
                );
            }

            $user = User::where('email', $user_email)->first();

            $user_social = UserSocial::where('user_id', $user->id)
                ->where('type', 'setta')
                ->first();

            if (!$user_social) {
                $user_social = new UserSocial();
                $user_social->user_id = $user->id;
                $user_social->type = 'setta';
                $user_social->save();
            }

            $log_login = new LogLogin();
            $log_login->user_id = Auth::user()->id;
            $log_login->timezone = Auth::user()->timezone;
            $log_login->save();

            return response()->json([
                'token' => $token,
                'user' => $user,
            ]);
        } catch (\Throwable $th) {
            throw new Exception('Ops! ocorreu um erro!');
        }
    }

    public function logout(Request $request)
    {
        $token = substr($request->header('Authorization'), 7);

        try {
            JWTAuth::invalidate($token);

            return response()->json([
                'message' => 'Usuário deslogado com sucesso',
            ]);
        } catch (JWTException $exception) {
            return response()->json(
                [
                    'message' => 'Não foi possível fazer logout',
                ],
                500
            );
        }
    }

    public function profile()
    {
        return response(Auth::user(), 200);
    }

    public function verify_token(Request $request)
    {
        try {
            $this->validate($request, [
                'token' => 'required|string|min:3',
            ]);

            try {
                $token = JWTAuth::setToken($request->token);
                JWTAuth::payload($request->token)->toArray();
                if (!($claim = JWTAuth::getPayload())) {
                    return response()->json(
                        ['message' => 'user_not_found'],
                        404
                    );
                }
            } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                return response()->json(['message' => 'token_expired'], 404);
            } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                return response()->json(['message' => 'token_invalid'], 404);
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                return response()->json(['message' => 'token_absent'], 404);
            }

            $user = User::where('id', $claim['sub'])->first();

            $log_login = new LogLogin();
            $log_login->user_id = $user->id;
            $log_login->timezone = $user->timezone;
            $log_login->save();

            // the token is valid and we have exposed the contents
            return response()->json([
                'message' => 'token_valid',
                'user' => $user,
            ]);
        } catch (\Throwable $th) {
            throw new Exception('Ops! ocorreu um erro!');
        }
    }
}
