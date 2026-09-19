<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionAdminAdmin extends ModelHelper
{
    protected $table = 'permission_admin_admin';

    public $timestamps = false;

    protected $fillable = [
        'permission_id',
        'admin_id',
        'status',
    ];

    public function permission(): BelongsTo
    {
        return $this->belongsTo(PermissionAdmin::class, 'permission_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
