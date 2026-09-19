<?php

namespace App\Services\Admin;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Model;

class Audit
{
    public static function record(string $action, Model $record, array $data = []): void
    {
        $safe = array_diff_key($data, array_flip(['password', 'remember_token', 'datos_bancarios', 'comprobante', 'token', 'secret']));
        Auditoria::create([
            'admin_id' => auth('admin')->id(), 'accion' => $action,
            'entidad' => class_basename($record), 'entidad_id' => $record->getKey(),
            'datos' => $safe, 'ip' => request()->ip(),
        ]);
    }
}
