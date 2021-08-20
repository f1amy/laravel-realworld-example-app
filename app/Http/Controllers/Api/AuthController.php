<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\NewUserRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register new user.
     *
     * @param NewUserRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function register(NewUserRequest $request)
    {
        $attributes = $request->validated();

        $attributes['password'] = Hash::make($attributes['password']);

        $user = User::create($attributes);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Login existing user.
     *
     * @param \App\Http\Requests\Api\LoginRequest $request
     * @return \App\Http\Resources\Api\UserResource|\Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        Auth::shouldUse('web');

        if (Auth::attempt($credentials)) {
            return new UserResource(Auth::user());
        }

        return response()->json([
            'message' => 'The given data was invalid.',
            'errors' => [
                'user' => [trans('auth.failed')],
            ],
        ], 422);
    }
}
