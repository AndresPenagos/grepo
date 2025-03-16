<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitudes extends Model
{
    protected $table = 'Solicitudes';
    protected $fillable = [
        'id',
        'id_programa',
        'id_semilla',
        'enlace_entrega',
        'nombre_quien_entrega',
        'correo',
        'comentario',
        'created_at',
        'updated_at'
    ];
    public function Semillas()
    {
        //pertenece a seeds
        return $this->belongsTo(Semillas::class, 'id_semilla', 'id');
    }
    public function Programas()
    {
        //pertenece a programas
        return $this->belongsTo(Programas::class, 'id_programa', 'id');
    }
    public function Tareas()
    {
        //las tareas tienes solicitudes
        return $this->hasMany(Tareas::class, 'id_solicitud', 'id');
    }
    public function Sofia()
    {
        //las tareas tienes solicitudes
        return $this->hasMany(Sofia::class, 'id_semilla', 'id_semilla');
    }
}


