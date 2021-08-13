<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Http\File as LaravelFile;
use InvalidArgumentException;
use SplFileInfo;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class File implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array<mixed> $attributes
     * @return LaravelFile|null
     */
    public function get($model, $key, $value, $attributes)
    {
        if ($value === null) {
            return null;
        }

        try {
            return new LaravelFile((string) $value);
        } catch (FileNotFoundException $e) {
            return null;
        }
    }

    /**
     * Prepare the given value for storage.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array<mixed> $attributes
     * @return string|null
     */
    public function set($model, $key, $value, $attributes)
    {
        if ($value === null) {
            return null;
        }

        if (! $value instanceof SplFileInfo) {
            throw new InvalidArgumentException('The given value is not an SplFileInfo instance.');
        }

        $fullPath = $value->getRealPath();
        if ($fullPath === false) {
            throw new InvalidArgumentException('The given file does not exist.');
        }

        return $fullPath;
    }
}
