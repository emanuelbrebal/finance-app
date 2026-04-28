<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Http\Requests\V1\Auth\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->auth->register($request->validated());

        $this->auth->authenticate([
            'email' => $user->email,
            'password' => $request->validated('password'),
        ]);

        return UserResource::make($user)
            ->response()
            ->setStatusCode(201);
    }

    public function login(LoginRequest $request): UserResource
    {
        $user = $this->auth->authenticate($request->validated());

        return UserResource::make($user);
    }

    public function logout(): JsonResponse
    {
        $this->auth->logout();

        return response()->json(null, 204);
    }

    public function me(Request $request): UserResource
    {
        return UserResource::make($request->user());
    }
}
