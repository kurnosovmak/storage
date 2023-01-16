<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Auth\LoginRequest;
use App\Http\Requests\Customer\Auth\RegisterRequest;
use App\Http\Resources\Api\TokenResource;
use App\Http\Resources\Message\ErrorMessageResource;
use App\Services\AuthService\Contract\AuthService;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\NewAccessToken;

class AuthController extends Controller
{

    public function __construct(protected AuthService $authService)
    {
    }

    public function login(LoginRequest $request): ErrorMessageResource|TokenResource
    {
        $cred = $request->only(['email', 'password']);

        try {
            $token = $this->authService->login($cred['email'], $cred['password']);
        } catch (\Exception $exception) {
            return new ErrorMessageResource($exception->getMessage());
        }

        return new TokenResource($token);
    }

    public function register(RegisterRequest $request): ErrorMessageResource|TokenResource
    {
        $data = $request->only(['email', 'password', 'name', 'family']);

        try {
            $token = $this->authService->register($data['email'], $data['password'], $data['name'], $data['family']);
        } catch (\Exception $exception) {
            return new ErrorMessageResource($exception->getMessage());
        }

        return new TokenResource($token);
    }

    public function logout(): ErrorMessageResource|JsonResponse
    {
        try {
            $this->authService->logout();
        } catch (\Exception $exception) {
            return new ErrorMessageResource($exception->getMessage());
        }
        return response()->json(null, 204);
    }
}
