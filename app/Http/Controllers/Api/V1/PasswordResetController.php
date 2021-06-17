<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Model\PasswordResets;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Keychain;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;

class PasswordResetController extends Controller
{
    private $signer;

    private $privateKey;

    private $max_attempts;

    public function __construct()
    {
        $this->privateKey = env('JWT_PRIVATE_KEY');
        $this->signer = new Sha256();
        $this->max_attempts = 3;
    }

    public function forgot(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|string|min:3|max:255',
            ]);

            $user_email = strtolower(trim($request->email));
            $user = User::where('email', $user_email)->first();

            if (!$user) {
                return response(['status' => true], 200);
            }

            if (!$request->code) {
                return PasswordResetController::send($user);
            } else {
                return PasswordResetController::verify($request->code, $user);
            }
        } catch (\Throwable $th) {
            throw new Exception('Ops! ocorreu um erro!');
        }
    }

    public function send($user)
    {
        try {
            PasswordResets::where('user_id', $user->id)->delete();

            $password_reset = new PasswordResets();
            $password_reset->user_id = $user->id;
            $password_reset->code = rand(10000, 99999);
            $password_reset->save();

            $debug = (bool) env('APP_DEBUG', true);

            if ($debug == true) {
                return response()->json(
                    [
                        'code' => $password_reset->code,
                    ],
                    200
                );
            }

            try {
                $sendEmail = EmailController::send(
                    $user->email,
                    false,
                    $user->name,
                    'Recuperação de Senha',
                    '',
                    $password_reset->code,
                    'emails.password_resets'
                );
            } catch (JWTException $exception) {
                return response()->json(
                    [
                        'message' => 'erro ao enviar e-mail.',
                    ],
                    500
                );
            }

            return response(['success' => true], 200);
        } catch (\Throwable $th) {
            throw new Exception('Ops! ocorreu um erro!');
        }
    }

    public function verify($code, $user)
    {
        try {
            $code_compare = PasswordResets::where(
                'user_id',
                $user->id
            )->first();

            if (!$code_compare) {
                return response(['success' => false], 422);
            }

            if ($code == $code_compare->code) {
                try {
                    $token = (new Builder())
                        ->setIssuedAt(time())
                        ->setExpiration(time() + 1800)
                        ->set('uid', $user->id)
                        ->sign($this->signer, $this->privateKey)
                        ->getToken();

                    $token->getHeaders();
                    $token->getClaims();

                    $payload = $token->__toString();

                    return response()->json(
                        [
                            'token' => $payload,
                        ],
                        201
                    );
                } catch (JWTException $exception) {
                    return response()->json(
                        [
                            'message' => 'Não foi possível gerar o token.',
                        ],
                        500
                    );
                }
            }

            $attempts = $code_compare->attempts + 1;

            if ($attempts <= $this->max_attempts) {
                PasswordResets::where('id', $code_compare->id)->update([
                    'attempts' => $attempts,
                ]);

                return response(
                    [
                        'errors' => [
                            'code' =>
                                'Código inválido, restam ' .
                                ($attempts_remaining =
                                    $this->max_attempts -
                                    $attempts .
                                    ' tentativa(s).'),
                        ],
                    ],
                    400
                );
            }

            PasswordResets::where('user_id', $user->id)->delete();
            return response()->json(
                [
                    'errors' => [
                        'code' =>
                            'Código expirado após ' .
                            $this->max_attempts .
                            ' tentativas.',
                    ],
                ],
                422
            );
        } catch (\Throwable $th) {
            throw new Exception('Ops! ocorreu um erro!');
        }
    }

    public function reset(Request $request, AuthController $auth)
    {
        try {
            $this->validate($request, [
                'token' => 'required|string|min:3',
                'password' => 'required|string|min:3|max:255',
                'password_confirm' => 'required|string|min:3|max:255',
            ]);

            $token = (new Parser())->parse((string) $request->token);

            $validate_sign = $token->verify($this->signer, $this->privateKey);

            if (!$validate_sign) {
                return response()->json(
                    [
                        'errors' => [
                            'token' => 'token inválido',
                        ],
                    ],
                    400
                );
            }

            $user_id = $token->getClaim('uid');

            $data = new ValidationData(time(), 20);

            $validate = $token->validate($data);

            if (!$validate) {
                return response()->json(
                    [
                        'errors' => [
                            'token' => 'token expirou',
                        ],
                    ],
                    400
                );
            }

            if ($request->password !== $request->password_confirm) {
                return response()->json(
                    [
                        'errors' => [
                            'password' =>
                                'Senhas não são iguais, tente novamente.',
                        ],
                    ],
                    400
                );
            }

            $user = User::findOrFail($user_id);

            $user->password = $request->password;
            $user->save();

            PasswordResets::where('user_id', $user->id)->delete();

            return $user;
        } catch (\Throwable $th) {
            throw new Exception('Ops! ocorreu um erro!');
        }
    }
}
