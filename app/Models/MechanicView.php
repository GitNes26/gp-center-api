<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MechanicView extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     * @var string
     */
    protected $table = 'mechanics_view';

    /**
     * LlavePrimaria asociada a la tabla.
     * @var string
     */
    // protected $primaryKey = 'id';
}
