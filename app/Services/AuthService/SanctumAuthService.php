<?php

namespace App\Services\AuthService;

use App\Exceptions\Auth\AuthException;
use App\Models\Customer;
use App\Models\User;
use App\Services\AuthService\Contract\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;
use Laravel\Sanctum\NewAccessToken;

class SanctumAuthService implements AuthService
{
    public function __construct(protected User $user, protected Customer $customer)
    {
    }

    /**
     * @throws AuthException
     */
    public function login(string $email, string $password, string $tokenName = 'web'): NewAccessToken
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            throw new AuthException(__('auth.failed'));
        }
        /** @var User $user */
        $user = Auth::user();
        return $user->createToken($tokenName);
    }

    /**
     * @throws \Exception
     */
    public function register(string $email, string $password, string $name, string $family, string $tokenName = 'web'): NewAccessToken
    {
        try {
            DB::beginTransaction();

            $user = $this->user->fill([
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $user->save();

            $customer = $this->customer->fill([
                'name' => $name,
                'family' => $family,
            ]);
            $user->customer()->save($customer);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return $user->createToken($tokenName);
    }

    public function logout()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->currentAccessToken()->delete();
        Auth::logout();
    }
}
