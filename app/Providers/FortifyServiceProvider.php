<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::loginView(fn () => inertia('authentication/LoginPage'));
        Fortify::registerView(fn () => inertia('authentication/RegisterPage'));
        Fortify::verifyEmailView(fn () => inertia('authentication/VerifyEmailPage'));
        Fortify::requestPasswordResetLinkView(fn () => inertia('authentication/ForgotPasswordPage'));
        Fortify::confirmPasswordView(fn () => inertia('authentication/ConfirmPasswordPage'));
        Fortify::resetPasswordView(fn (Request $request) => inertia('authentication/ResetPasswordPage', [
            'token' => $request->route('token'),
            'email' => $request->get('email'),
        ]));

        Fortify::authenticateUsing(function (Request $request) {

            $field = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            $user = User::where($field, $request->username)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
