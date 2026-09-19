<?php

namespace App\Models;

use App\Traits\AuthenticatesModel;
use App\Traits\TraitGeneral;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends ModelHelper implements Authenticatable, Authorizable, CanResetPassword
{
    use AuthenticatesModel;
    use HasFactory;
    use TraitGeneral;

    protected $guarded = ['id'];

    const ACTIVE = 1;

    const INACTIVE = 2;

    const DELETE = 0;

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'status' => 'integer'];
    }

    public function getNameLastName()
    {
        return $this->name . ' ' . $this->lastname;
    }

    public function getGender(): string
    {
        return $this->sex === '1' ? 'Masculino' : 'Femenino';
    }

    public function getStatus(): string
    {
        return $this->status == self::ACTIVE ? 'Activo' : 'Inactivo';
    }

    public function getDateBirth(): string
    {
        return Carbon::parse($this->date_birth)->format('d-m-Y');
    }

    public static function searchID(int $id)
    {
        return self::where('status', '!=', self::DELETE)->findOrFail($id);
    }

    public static function searchEmail(string $email)
    {
        return self::where('email', $email)->first();
    }

    public function viajeros(): HasMany
    {
        return $this->hasMany(Viajero::class, 'usuario_id');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'usuario_id');
    }

    public function cupones(): HasMany
    {
        return $this->hasMany(Cupon::class, 'usuario_id');
    }

    public static function searchAdmin(string $search = '', array $filters = []): Builder
    {
        $query = self::query();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('users.id', ctype_digit($search) ? $search : -1);
                $query->orWhere('users.name', 'like', '%' . $search . '%');
                $query->orWhere('users.lastname', 'like', '%' . $search . '%');
                $query->orWhere('users.email', 'like', '%' . $search . '%');
                $query->orWhere('users.telefono', 'like', '%' . $search . '%');
            });
        }

        $status = $filters['status'] ?? $filters['estatus'] ?? $filters['estado_pago'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('users.status', $status);
        } else {
            $query->where('users.status', '!=', 0);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('users.created_at', '>=', self::date($filters['date_from']));
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('users.created_at', '<=', self::date($filters['date_to']));
        }

        return $query;
    }
}
