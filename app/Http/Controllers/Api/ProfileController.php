<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProfileResource;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param string $username
     * @return \App\Http\Resources\Api\ProfileResource
     */
    public function show(string $username)
    {
        $profile = User::whereUsername($username)
            ->firstOrFail();

        return new ProfileResource($profile);
    }

    /**
     * Follow an author.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $username
     * @return \App\Http\Resources\Api\ProfileResource
     */
    public function follow(Request $request, string $username)
    {
        $profile = User::whereUsername($username)
            ->firstOrFail();

        $profile->followers()
            ->syncWithoutDetaching($request->user());

        return new ProfileResource($profile);
    }

    /**
     * Unfollow an author.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $username
     * @return \App\Http\Resources\Api\ProfileResource
     */
    public function unfollow(Request $request, string $username)
    {
        $profile = User::whereUsername($username)
            ->firstOrFail();

        $profile->followers()->detach($request->user());

        return new ProfileResource($profile);
    }
}
