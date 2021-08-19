<?php

namespace Tests\Feature\Casts;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class FileCastTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        /** @var User $user */
        $user = User::factory()->withImage()->create();
        $this->user = $user;
    }

    public function testGetFileNotFound(): void
    {
        $this->assertNotNull($image = $this->user->image);

        Storage::disk('public')
            ->delete("images/{$image->getBasename()}");

        $this->user->refresh();

        $this->assertNull($this->user->image);
    }

    public function testSetNotFile(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->user->update(['image' => 'string']);
    }

    public function testSetFileNotFound(): void
    {
        $this->assertNotNull($image = $this->user->image);

        Storage::disk('public')
            ->delete("images/{$image->getBasename()}");

        $this->expectException(InvalidArgumentException::class);

        $this->user->save();
    }
}
