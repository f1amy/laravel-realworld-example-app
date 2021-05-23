<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ProfileResource
 *
 * @package App\Http\Resources
 * @property \App\Models\User $resource
 */
class ProfileResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string
     */
    public static $wrap = 'profile';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        /**
         * @var \App\Models\User|null $user
         */
        $user = $request->user('api');

        return [
            'username' => $this->resource->username,
            'bio' => $this->resource->bio,
            'image' => $this->resource->image,
            'following' => $this->when($user !== null, function () use ($user) {
                /**
                 * @var \App\Models\User $user
                 */
                return $user->following($this->resource);
            }),
        ];
    }
}
