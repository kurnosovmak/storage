<?php

namespace App\Services\AuthService\Contract;

use Laravel\Sanctum\NewAccessToken;

interface AuthService
{
    public function login(string $email, string $password, string $tokenName = 'web'): NewAccessToken;

    public function register(string $email, string $password, string $name, string $family, string $tokenName = 'web'): NewAccessToken;

    public function logout();
}
