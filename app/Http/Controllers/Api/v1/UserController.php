<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\UpdateUserRequest;
use App\Http\Resources\Api\v1\UserResource;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @return \App\Http\Resources\Api\v1\UserResource
     */
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Api\v1\UpdateUserRequest $request
     * @return \App\Http\Resources\Api\v1\UserResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $attributes = Arr::get($request->validated(), 'user');

        if (Arr::has($attributes, 'image')) {
            $attributes['image'] = $this->storeUploadedImage($attributes['image']);

            if ($attributes['image'] === null) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'user.image' => [trans('validation.uploaded')],
                    ],
                ], 422);
            }
        }

        $user->update($attributes);

        return new UserResource($user);
    }

    /**
     * Store uploaded image file.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return \Illuminate\Http\File|null
     */
    protected function storeUploadedImage(UploadedFile $file): ?File
    {
        if ($file->isValid()) {
            $relPath = $file->store('images', 'public');

            if ($relPath !== false) {
                $fullPath = Storage::disk('public')->path($relPath);

                return new File($fullPath);
            }
        }

        return null;
    }
}
