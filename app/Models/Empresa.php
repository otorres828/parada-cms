<?php

namespace App\Models;

use App\Traits\TraitGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends ModelHelper
{
    use TraitGeneral;

    public const CONTRATO_ELLOS_RECIBEN = 1;

    public const CONTRATO_NOSOTROS_RECIBIMOS = 2;

    protected $table = 'empresas';

    protected $fillable = [
        'nombre',
        'rif',
        'telefono',
        'email',
        'tipo_contrato',
        'dia_corte',
        'dia_vencimiento',
        'hora_corte',
        'hora_vencimiento',
        'bloqueada_por_cobranza_at',
        'estatus',
    ];

    protected function casts(): array
    {
        return [
            'tipo_contrato' => 'integer',
            'dia_corte' => 'integer',
            'dia_vencimiento' => 'integer',
            'bloqueada_por_cobranza_at' => 'datetime',
            'estatus' => 'integer',
        ];
    }

    public function usuariosEmpresa(): HasMany
    {
        return $this->hasMany(UsuarioEmpresa::class, 'empresa_id');
    }

    public function autobuses(): HasMany
    {
        return $this->hasMany(Autobus::class, 'empresa_id');
    }

    public function viajes(): HasMany
    {
        return $this->hasMany(Viaje::class, 'empresa_id');
    }

    public function configuracionCupones(): HasMany
    {
        return $this->hasMany(ConfiguracionCupon::class, 'empresa_id');
    }

    public function datosBancarios(): HasMany
    {
        return $this->hasMany(DatoBancario::class, 'empresa_id');
    }

    public function ordenesCobro(): HasMany
    {
        return $this->hasMany(OrdenCobro::class, 'empresa_id');
    }

    public function estaBloqueadaPorCobranza(): bool
    {
        return $this->bloqueada_por_cobranza_at !== null;
    }

    public function getTipoContrato(): string
    {
        return match ($this->tipo_contrato) {
            self::CONTRATO_ELLOS_RECIBEN => 'La empresa recibe los pagos',
            self::CONTRATO_NOSOTROS_RECIBIMOS => 'La plataforma recibe los pagos',
            default => 'Sin configurar',
        };
    }

    public function getDiaCorte(): string
    {
        return $this->getDiaSemana($this->dia_corte);
    }

    public function getDiaVencimiento(): string
    {
        return $this->getDiaSemana($this->dia_vencimiento);
    }

    private function getDiaSemana(?int $dia): string
    {
        return [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ][$dia] ?? 'Sin configurar';
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('empresas.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('empresas.nombre', 'like', '%'.$search.'%');
                $query->orWhere('empresas.email', 'like', '%'.$search.'%');
                $query->orWhere('empresas.rif', 'like', '%'.$search.'%');
            });
        }

        $status = $filters['status'] ?? $filters['estatus'] ?? $filters['estado_pago'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('empresas.estatus', $status);
        } else {
            $query->where('empresas.estatus', '!=', self::ESTADO_DELETE);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('empresas.created_at', '>=', self::date($filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('empresas.created_at', '<=', self::date($filters['date_to']));
        }

        if (! empty($filters['con_legales'])) {
            $query->withCount('documentosLegales');
        }

        return $query;
    }

    public static function dashboardSummary(): self
    {
        return self::searchAdmin()
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(CASE WHEN estatus = 1 THEN 1 ELSE 0 END), 0) as activas')
            ->first();
    }

    public static function findAdminDetail(int $empresaId): self
    {
        return self::query()
            ->with([
                'datosBancarios' => function ($query) {
                    $query->where('estatus', '!=', DatoBancario::ELIMINADO)
                        ->orderByDesc('estatus')
                        ->orderBy('tipo')
                        ->orderBy('id');
                },
            ])
            ->findOrFail($empresaId);
    }

    public function reembolsos(): HasMany
    {
        return $this->hasMany(Reembolso::class, 'empresa_id');
    }

    public function documentosLegales(): HasMany
    {
        return $this->hasMany(DocumentoLegal::class, 'empresa_id');
    }
}
