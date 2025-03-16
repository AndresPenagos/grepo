<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class SubTareas extends Model
{
    protected $table = 'subtareas'; // Nombre de la tabla en la base de datos

    protected $fillable = [
        'id',
        'id_tarea',
        'status',
        'porcentaje',
        'tarea',
        'id_usuario',
        'fecha_inicio',
        'fecha_final',
        'url',
        'created_at',
        'updated_at'
    ];
    public function Tareas()
    {
        return $this->belongsTo('App\Models\Tareas', 'id_tarea');
    }
    public function usuarios()
    {
        return $this->belongsTo('App\Models\Usuarios', 'id_usuario');
    }
    
}