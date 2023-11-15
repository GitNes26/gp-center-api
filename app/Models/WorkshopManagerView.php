<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkshopManagerView extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'role_id',
        'active',
        'id',
        'user_id',
        'avatar',
        'name',
        'paternal_last_name',
        'maternal_last_name',
        'phone',
        'payroll_number',
        'role',
        'read',
        'create',
        'update',
        'delete',
        'more_permissions'
    ];

     /**
     * Los atributos que deben ocultarse para la serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        // 'deleted_at',
    ];

    /**
     * Nombre de la tabla asociada al modelo.
     * @var string
     */
    protected $table = 'workshop_managers_view';

    /**
     * LlavePrimaria asociada a la tabla.
     * @var string
     */
    // protected $primaryKey = 'id';
}
