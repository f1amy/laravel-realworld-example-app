<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\LoginRequest;
use App\Http\Requests\Api\v1\NewUserRequest;
use App\Http\Resources\Api\v1\UserTokenResource;
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
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function register(NewUserRequest $request)
    {
        $attributes = Arr::get($request->validated(), 'user');

        $attributes['password'] = Hash::make($attributes['password']);

        $user = User::create($attributes);

        return (new UserTokenResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Login existing user.
     *
     * @param \App\Http\Requests\Api\v1\LoginRequest $request
     * @return \App\Http\Resources\Api\v1\UserTokenResource|\Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = Arr::get($request->validated(), 'user');

        Auth::shouldUse('web');

        if (Auth::attempt($credentials)) {
            return new UserTokenResource(Auth::user());
        }

        return response()->json([
            'message' => 'The given data was invalid.',
            'errors' => [
                'user' => [trans('auth.failed')],
            ],
        ], 422);
    }
}
