<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\LogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ログイン後のリダイレクト先を指定
        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                // メール未認証の場合は認証誘導画面へ
                if ($request->user() && !$request->user()->hasVerifiedEmail()) {
                    return redirect()->route('verification.notice');
                }
                return redirect('/attendance');
            }
        });

        // 会員登録後のリダイレクト先を指定
        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
            public function toResponse($request)
            {
                // メール未認証の場合は認証誘導画面へ
                if ($request->user() && !$request->user()->hasVerifiedEmail()) {
                    return redirect()->route('verification.notice');
                }
                return redirect('/attendance');
            }
        });

        // ログアウト後のリダイレクト先を指定
        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request)
            {
                return redirect('/login');
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 会員登録にCreateNewUserを使用
        Fortify::createUsersUsing(CreateNewUser::class);

        // ログイン認証にFormRequestを適用
        Fortify::authenticateUsing(function (Request $request) {
            // FormRequestのバリデーションを適用
            $loginRequest = app(\App\Http\Requests\LoginRequest::class);
            Validator::make($request->all(), $loginRequest->rules(), $loginRequest->messages())->validate();

            // 認証処理
            $user = \App\Models\User::where('email', $request->email)->first();

            if ($user && \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
                return $user;
            }

            // 認証失敗
            throw ValidationException::withMessages([
                'email' => ['ログイン情報が登録されていません'],
            ]);
        });

        // ビューの指定
        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::loginView(function () {
            return view('auth.login');
        });

        // メール認証誘導画面
        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        // レート制限
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(5)->by($email . $request->ip());
        });
    }
}
