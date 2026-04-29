<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function register(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
    }

    /**
     * @param  array{email: string, password: string}  $credentials
     *
     * @throws ValidationException
     */
    public function authenticate(array $credentials, bool $remember = false): User
    {
        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();

        if (request()->hasSession()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }
    }
}
