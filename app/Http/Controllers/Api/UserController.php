<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateUserRequest;
use App\Http\Resources\Api\UserResource;
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
     * @return \App\Http\Resources\Api\UserResource
     */
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Api\UpdateUserRequest $request
     * @return \App\Http\Resources\Api\UserResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (empty($attrs = $request->validated())) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'any' => [trans('validation.required_at_least_one')],
                ],
            ], 422);
        }

        if ($image = Arr::get($attrs, 'image')) {
            $attrs['image'] = $this->storeUploadedImage($image);

            if ($attrs['image'] === null) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'image' => [trans('validation.uploaded')],
                    ],
                ], 422);
            }
        }

        $user->update($attrs);

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
