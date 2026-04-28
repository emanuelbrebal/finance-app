<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateProfileRequest;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): UserResource
    {
        return UserResource::make($request->user());
    }

    public function update(UpdateProfileRequest $request): UserResource
    {
        $data = $request->validated();

        // Remove password from data if not provided — null means "don't change"
        if (empty($data['password'])) {
            unset($data['password']);
        }

        unset($data['password_confirmation']);

        $request->user()->update($data);

        return UserResource::make($request->user()->fresh());
    }
}
