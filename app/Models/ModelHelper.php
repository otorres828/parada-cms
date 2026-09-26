<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ModelHelper extends Model
{
    // currency
    const USD = 2;

    const ESTADO_DELETE = 0;

    const ESTADO_ACTIVE = 1;

    const ESTADO_INACTIVE = 2;

    const ESTADO_FINALIZADO = 3;

    public static function exigir(bool $condicion, string $campo, string $mensaje): void
    {
        if (! $condicion) {
            throw ValidationException::withMessages([$campo => $mensaje]);
        }
    }

    // Queries

    public static function searchActive()
    {
        return self::where('status', ModelHelper::ESTADO_ACTIVE)->get();
    }

    public static function searchNoDelete($code_id = null)
    {
        if (is_null($code_id)) {
            return self::where('status', '=', ModelHelper::ESTADO_ACTIVE)->get();
        }

        return self::where('status', '!=', ModelHelper::ESTADO_DELETE)->get();
    }

    public function deleteImageFromStorage(string $disk, string $image)
    {
        if ($image && Storage::disk($disk)->exists($image)) {
            Storage::disk($disk)->delete($image);
        }
    }

    public function getFileStorage(string $atributo, string $disk)
    {
        // Si el archivo fue guardad de manera absoliutia, devolvemos la url
        if (str_starts_with($this->$atributo, 'http')) {
            return $this->$atributo;
        }

        // si solo fue guardado el nombre de la imagen devolvemos la ruta
        if ($this->$atributo) {
            /** @var FilesystemAdapter $storage */
            $storage = Storage::disk($disk);

            if ($storage->exists($this->$atributo)) {
                return $storage->url($this->$atributo);
            }
        }

        return asset('assets/img/misc/placeholder-image.png');
    }

    public static function value($record, string $key, array $definition = []): string
    {
        $value = data_get($record, $key);

        if ($value === null || $value === '') {
            return '—';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(str_contains($key, 'hora') ? 'H:i' : 'd/m/Y H:i');
        }

        if ($key === ($definition['status'] ?? null)) {
            return (string) (($definition['statusOptions'] ?? [0 => 'Inactivo', 1 => 'Activo'])[is_bool($value) ? (string) (int) $value : (string) $value] ?? $value);
        }

        if (is_bool($value)) {
            return $value ? 'Sí' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return (string) $value;
    }
}
