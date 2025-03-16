<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Tareas extends Model
{
    protected $table = 'tareas'; // Nombre de la tabla en la base de datos

    protected $fillable = [
        'id',
        'id_solicitud',
        'status',
        'porcentaje',
        'tarea',
        'id_usuario',
        'url',
        'created_at',
        'updated_at'
        
    ];
    public function solicitudes()
    {
        return $this->belongsTo('App\Models\Solicitudes', 'id_solicitud');
    }
    public function usuarios()
    {
        return $this->belongsTo('App\Models\Usuarios', 'id_usuario');
    }
    public function SubTareas()
    {
        return $this->hasMany('App\Models\SubTareas', 'id_tarea');
    }
}