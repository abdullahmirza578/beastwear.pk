<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\User;

class CustomerAuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('account');
        }

        $this->storeIntendedRedirect($request);

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);
        $remember = $request->boolean('remember');
        unset($credentials['remember']);
        $credentials['role'] = 'customer';
        $credentials['status'] = 'active';

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'The provided customer credentials are incorrect.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('account'));
    }

    public function showRegistration(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('account');
        }

        $this->storeIntendedRedirect($request);

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', 'string', 'min:8'],
            'terms' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'status' => 'active',
        ]);

        $user->cart()->create();
        $user->wishlist()->create();
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('account'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function storeIntendedRedirect(Request $request): void
    {
        $redirect = $request->query('redirect');
        $path = is_string($redirect) ? parse_url($redirect, PHP_URL_PATH) : null;
        if (is_string($path)
            && str_starts_with($path, '/')
            && ! str_starts_with($path, '//')
            && ! str_contains($path, '\\')
            && parse_url($redirect, PHP_URL_HOST) === null
            && parse_url($redirect, PHP_URL_SCHEME) === null) {
            $request->session()->put('url.intended', url($redirect));
        }
    }
}
