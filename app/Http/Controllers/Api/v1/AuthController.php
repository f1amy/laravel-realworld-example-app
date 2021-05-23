<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\NewUserRequest;
use App\Http\Resources\UserTokenResource;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register new user.
     *
     * @param NewUserRequest $request
     * @return \App\Http\Resources\UserTokenResource
     */
    public function register(NewUserRequest $request)
    {
        $attributes = Arr::get($request->validated(), 'user');

        $attributes['password'] = Hash::make($attributes['password']);

        $user = User::create($attributes);

        return new UserTokenResource($user);
    }

    /**
     * Login existing user.
     *
     * @param \App\Http\Requests\LoginRequest $request
     * @return \App\Http\Resources\UserTokenResource|\Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = Arr::get($request->validated(), 'user');

        if (Auth::attempt($credentials)) {
            return new UserTokenResource(Auth::user());
        }

        return response()->json([
            'errors' => [
                'email' => 'The provided credentials do not match our records.',
            ],
        ], 422);
    }
}
