<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mails\RegisterUserMail;
use App\Mails\WelcomeUserRegistrationMail;
use App\Models\User;
use App\Models\PasswordReset;
use App\Services\UserService;
use App\Structs\ResponseCode;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthenticationController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request): mixed
    {
        try {

            $attr = $request->validate([
                'email' => 'required|string|email|',
                'password' => 'required|string|min:6'
            ]);

            if (!Auth::attempt($attr)) {
                return response([
                    'data' => [],
                    "success" => false,
                    'message' => trans('errors.authentication.credentials')
                ], ResponseCode::NOT_AUTHENTICATED);
            }

            $user = User::findOrFail(Auth::id());

            if(!empty($user)) {

                if(!empty($user->email_verified_at)) {

                    $token = $user->createToken(env("API_TOKEN_KEY"), [])->plainTextToken;

                    return response([
                        'data' => $user,
                        "token" => $token,
                        "success" => true,
                        'message' => trans('success.authentication.login')
                    ], ResponseCode::SUCCESS);
                } else {

                    return response([
                        'data' => [],
                        "success" => false,
                        'message' => trans('errors.authentication.email_not_verified')
                    ], ResponseCode::NOT_AUTHENTICATED);

                }

            } else {
                return response([
                    'data' => [],
                    "success" => false,
                    'message' => trans('errors.authentication.role_missing')
                ], ResponseCode::NOT_AUTHENTICATED);
            }

        } catch(\Exception $e) {

            return response([
                'data' => [],
                "success" => false,
                'message' => trans('errors.authentication.login'),
                'error' => $e->getMessage()
            ], ResponseCode::BAD_REQUEST);

        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function logout(Request $request): mixed
    {
        try {

            Auth::guard('web')->logout();

            if ($request->user()) {
                $request->user()->tokens()->where("name", env("API_TOKEN_KEY"))->delete();
            }

            return response([
                'data' => [],
                "success" => true,
                'message' => trans('success.authentication.logout')
            ], ResponseCode::SUCCESS);

        } catch (\Exception $e) {

            return response([
                'data' => [],
                "success" => false,
                'message' => trans('errors.authentication.logout'),
                'error' => $e->getMessage()
            ], ResponseCode::BAD_REQUEST);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function authenticate(Request $request) : mixed
    {
        try {

            $user = $request->user();

            if (!$user) {

                return response([
                    'data' => [],
                    "success" => false,
                    'message' => trans('errors.authentication.not_authenticated'),
                ], ResponseCode::NOT_AUTHENTICATED);
            }

            $user = User::findOrFail(Auth::id());

            return response([
                'data' => [
                    "user" =>  $user
                ],
                "success" => true,
                'message' => NULL,
            ], ResponseCode::SUCCESS);

        } catch (\Exception $e) {

            return response([
                'data' => [],
                "success" => false,
                'message' => trans('errors.authentication.logout'),
                'error' => $e->getMessage()
            ], ResponseCode::BAD_REQUEST);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function sendPasswordReset(Request $request): mixed
    {
        try {

            $request->validate([
                'email' => 'required|string|email|exists:users,email'
            ]);

            DB::table('password_reset_tokens')->where("email", $request->input("email"))->delete();

            $token = Str::UUID();
            DB::table('password_reset_tokens')->insert([
                'email' => $request->input("email"),
                'token' => $token,
                'created_at' => Carbon::now(),
            ]);

            Mail::send('mails.authentication.password-reset-link', ['token' => $token], function ($message) use ($request) {
                $message->to($request->input("email"));
                $message->subject(trans("mails.passwordReset.subject"));
            });

            return response([
                'data' => [],
                "success" => true,
                'message' => trans('success.authentication.mail_forgot_password')
            ], ResponseCode::SUCCESS);

        } catch (\Exception $e) {

            return response([
                'data' => [],
                "success" => false,
                'message' => trans('errors.authentication.mail_forgot_password'),
                'error' => $e->getMessage()
            ], ResponseCode::BAD_REQUEST);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function checkResetToken(Request $request): mixed
    {
        try {

            $request->validate([
                'token' => 'required|exists:password_reset_tokens,token'
            ]);

            return response([
                'data' => [],
                "success" => true,
                'message' => trans('success.authentication.check_reset_token')
            ], ResponseCode::SUCCESS);

        } catch (\Exception $e) {

            return response([
                'data' => [],
                "success" => false,
                'message' => trans('errors.authentication.check_reset_token'),
                'error' => $e->getMessage()
            ], ResponseCode::BAD_REQUEST);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function resetPassword(Request $request): mixed
    {
        try {

            $request->validate([
                'token' => 'required|exists:password_reset_tokens,token',
                'email' => 'required|exists:password_reset_tokens,email',
                'password' => 'required|min:6'
            ]);

            $updatePassword = DB::table('password_reset_tokens')
                ->where([
                    'email' => $request->input("email"),
                    'token' => $request->input("token"),
                ])
                ->first();

            if ($updatePassword) {

                $user = User::where('email', $request->input("email"))->update(['password' => bcrypt($request->input("password"))]);

                DB::table('password_reset_tokens')->where(['email' => $request->input("email")])->delete();

                return response([
                    'data' => [],
                    "success" => true,
                    'message' => trans('success.authentication.reset_password')
                ], ResponseCode::SUCCESS);

            } else {

                return response([
                    'data' => [],
                    "success" => false,
                    'message' => trans('errors.authentication.reset_password_not_exists'),
                ], ResponseCode::BAD_REQUEST);
            }

        } catch (\Exception $e) {

            return response([
                'data' => [],
                "success" => false,
                'message' => trans('errors.authentication.forgot_password'),
                'error' => $e->getMessage()
            ], ResponseCode::BAD_REQUEST);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request): mixed
    {

        try {

            $user = UserService::upsert($request->all());

            return response([
                'data' => $user,
                "success" => true,
                'message' => trans('success.authentication.register'),
            ], ResponseCode::SUCCESS);

        } catch (\Exception $e) {

            return response([
                'data' => [],
                "success" => false,
                'message' => trans('errors.authentication.register'),
                'error' => $e->getMessage()
            ], ResponseCode::BAD_REQUEST);
        }
    }

}
