<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials, remember: false)) {
            return back()
                ->withInput(['email' => $credentials['email']])
                ->withErrors(['email' => 'Неверный email или пароль.']);
        }

        $request->session()->regenerate();

        $user = $request->user();
        $role = $user?->role;

        $default = route('requests.create');

        if ($role === UserRole::Dispatcher) {
            $default = route('dispatcher.requests.index');
        } elseif ($role === UserRole::Master) {
            $default = route('master.requests.index');
        }

        return redirect()->intended($default);
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Вы вышли из системы.');
    }
}