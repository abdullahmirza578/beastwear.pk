<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $resetLink = null;
        $localLogMailer = app()->environment('local') && config('mail.default') === 'log';
        $status = Password::sendResetLink(
            $request->only('email'),
            $localLogMailer
                ? function (User $user, string $token) use (&$resetLink): string {
                    $resetLink = route('password.reset', [
                        'token' => $token,
                        'email' => $user->getEmailForPasswordReset(),
                    ]);

                    return Password::RESET_LINK_SENT;
                }
                : null
        );

        if ($status === Password::RESET_LINK_SENT) {
            $response = back()->with('status', 'If an account exists for that email, a password reset link has been sent.');

            if ($resetLink !== null) {
                $response->with('reset_link', $resetLink);
            }

            return $response;
        }

        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return back()->with('status', 'If an account exists for that email, a password reset link has been sent.');
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset($data, function (User $user, string $password): void {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Your password has been reset. You can now sign in.');
        }

        throw ValidationException::withMessages(['email' => __($status)]);
    }
}
