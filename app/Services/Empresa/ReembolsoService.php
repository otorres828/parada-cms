<?php

namespace App\Services\Empresa;

use App\Models\Empresa;
use App\Models\PagoReserva;
use App\Models\Reembolso;
use App\Models\Reserva;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReembolsoService
{
    private static function fail(string $message): never
    {
        throw ValidationException::withMessages(['monto' => $message]);
    }

    public static function crear(array $data): Reembolso
    {
        Validator::make($data, [
            'pago_reserva_id' => 'required|integer|exists:pagos_reservas,id',
            'motivo' => 'required|string|min:10|max:2000',
        ])->validate();

        return DB::transaction(function () use ($data) {
            $pago = PagoReserva::with('reserva.programacion.viaje')->whereKey($data['pago_reserva_id'])->lockForUpdate()->firstOrFail();
            $reserva = $pago->reserva;
            if ($reserva->estado_pago !== Reserva::ESTADO_PAGO_PAGADO) {
                self::fail('La reserva no está pagada.');
            }
            if (Reembolso::where('pago_reserva_id', $pago->id)->exists()) {
                self::fail('Ya existe una solicitud para este pago.');
            }
            $empresa = Empresa::whereKey($reserva->programacion->viaje->empresa_id)->lockForUpdate()->firstOrFail();
            $record = Reembolso::create([
                'pago_reserva_id' => $pago->id,
                'empresa_id' => $empresa->id,
                'admin_id' => auth('admin')->id(),
                'monto' => $pago->total,
                'moneda' => 'USD',
                'estatus' => 'pendiente',
                'motivo' => $data['motivo'],
            ]);
            Audit::record('reembolso.solicitado', $record);

            return $record;
        });
    }

    public static function revisar(int $id, string $decision, string $comment, ?string $reference, ?string $proof): void
    {
        if (! in_array($decision, ['aprobado', 'rechazado', 'pagado'], true)) {
            self::fail('Resolución inválida.');
        }

        DB::transaction(function () use ($id, $decision, $comment, $reference, $proof) {
            $record = Reembolso::with('pagoReserva.reserva')->whereKey($id)->lockForUpdate()->firstOrFail();
            $allowed = $record->estatus === 'pendiente' ? ['aprobado', 'rechazado'] : ($record->estatus === 'aprobado' ? ['pagado', 'rechazado'] : []);
            if (! in_array($decision, $allowed, true)) {
                self::fail('La solicitud ya cambió de estado. Actualiza la pantalla.');
            }
            if ($decision === 'pagado') {
                if (! $reference || ! $proof) {
                    self::fail('Indica la referencia y adjunta el comprobante de la transferencia realizada.');
                }
                if (Reembolso::where('referencia', $reference)->whereKeyNot($id)->exists()) {
                    self::fail('Esta referencia ya está registrada.');
                }
                $reserva = Reserva::whereKey($record->pagoReserva->reserva_id)->lockForUpdate()->firstOrFail();
                if ($reserva->estado_pago !== Reserva::ESTADO_PAGO_PAGADO) {
                    self::fail('La reserva ya no admite reembolso.');
                }
                $reserva->update(['estado_pago' => Reserva::ESTADO_PAGO_REEMBOLSADO]);
                $record->referencia = $reference;
                $record->comprobante = $proof;
            }
            $record->estatus = $decision;
            $record->comentario = $comment;
            $record->revisado_por = auth('admin')->id();
            $record->fecha_resolucion = now();
            $record->save();
            Audit::record('reembolsos.'.$decision, $record, ['comentario' => $comment]);
        });
    }
}
