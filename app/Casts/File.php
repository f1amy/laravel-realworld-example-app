<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Http\File as FileClass;
use InvalidArgumentException;
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
     * @return FileClass|null
     */
    public function get($model, $key, $value, $attributes)
    {
        if ($value !== null) {
            try {
                return new FileClass((string) $value);
            } catch (FileNotFoundException $e) {
                return null;
            }
        }

        return null;
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
        if ($value !== null) {
            if (! $value instanceof FileClass) {
                throw new InvalidArgumentException('The given value is not an File instance.');
            }

            return $value->path();
        }

        return null;
    }
}
