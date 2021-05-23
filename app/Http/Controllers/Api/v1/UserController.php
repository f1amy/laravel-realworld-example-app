<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class UserController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @return \App\Http\Resources\UserResource
     */
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateUserRequest $request
     * @return \App\Http\Resources\UserResource
     */
    public function update(UpdateUserRequest $request)
    {
        /**
         * @var \App\Models\User $user
         */
        $user = $request->user();

        $attributes = Arr::get($request->validated(), 'user');

        if ($request->hasFile('user.image')) {
            /**
             * @var \Illuminate\Http\UploadedFile
             */
            $image = $request->file('user.image');

            $attributes['image'] = $image->path();
        }

        $user->update($attributes);

        return new UserResource($user);
    }
}
