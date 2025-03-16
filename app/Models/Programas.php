<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Programas extends Model
{
    protected $table = 'programas'; // Nombre de la tabla en la base de datos

    protected $fillable = [
        'id',
        'nombre',
        'codigo',
        'version',
        'tipo',
        'horas',
        'link_descripcion_programa',
        'link_diseno_curricular',
    ];
    public function semillas()
    {
        return $this->hasMany(Semillas::class, 'id_programa', 'id');
    }

    // Otros métodos, relaciones, etc. que puedas necesitar
}