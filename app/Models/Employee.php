<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    /**
     * define qué campos son para asignarse masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // 'id',
        'user_id',
        'profile_id',
        // 'avatar',
        'name',
        'paternal_last_name',
        'maternal_last_name',
        'phone',
        'license_number',
        'license_type',
        'license_due_date',
        // 'img_license',
        // 'img_firm',
        'payroll_number',
        'department',
        'community_id',
        'street',
        'num_ext',
        'num_int',
        'active',
        'deleted_at'
    ];
    // protected $timestamps=false:

    /** 
     * define qué campos NO deben asignarse masivamente.
     */
    protected $guarded = [
        // 'avatar',
        // 'img_license',
        // 'img_firm',
    ];


    /**
     * Los atributos que deben ocultarse para la serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // 'deleted_at',
    ];

    /**
     * Nombre de la tabla asociada al modelo.
     * @var string
     */
    protected $table = 'employees';

    /**
     * LlavePrimaria asociada a la tabla.
     * @var string
     */
    protected $primaryKey = 'id';


    public function departamentos()
    {
        return $this->belongsToMany(Departamento_CP::class, 'department_directors', 'director_id', 'department_id');
    }
}
