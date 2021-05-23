<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param string $username
     * @return \App\Http\Resources\ProfileResource|\Illuminate\Http\JsonResponse
     */
    public function show(string $username)
    {
        $profile = User::whereUsername($username)
            ->first();

        if ($profile !== null) {
            return new ProfileResource($profile);
        }

        return $this->notFoundResponse();
    }

    /**
     * Follow an author.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $username
     * @return \App\Http\Resources\ProfileResource|\Illuminate\Http\JsonResponse
     */
    public function follow(Request $request, string $username)
    {
        $profile = User::whereUsername($username)
            ->first();

        if ($profile !== null) {
            /**
             * @var \App\Models\User $user
             */
            $user = $request->user();
            $profile->followers()->syncWithoutDetaching($user);

            return new ProfileResource($profile);
        }

        return $this->notFoundResponse();
    }

    /**
     * Unfollow an author.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $username
     * @return \App\Http\Resources\ProfileResource|\Illuminate\Http\JsonResponse
     */
    public function unfollow(Request $request, string $username)
    {
        $profile = User::whereUsername($username)
            ->first();

        if ($profile !== null) {
            /**
             * @var \App\Models\User $user
             */
            $user = $request->user();
            $profile->followers()->detach($user);

            return new ProfileResource($profile);
        }

        return $this->notFoundResponse();
    }

    /**
     * Return resource not found response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function notFoundResponse()
    {
        return response()->json([
            'errors' => [
                'username' => 'The specified profile does not exist.',
            ],
        ], 422);
    }
}
