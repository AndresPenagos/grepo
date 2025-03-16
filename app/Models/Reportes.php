<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Reportes extends Model
{
    protected $table = 'reportes';
    protected $fillable = [
        'id',
        'id_subtarea',
        'id_usuario',
        'horas',
        'dia',
        'descripcion',
        'created_at',
        'updated_at'
        // Agrega aquí los nombres de las columnas que deseas que sean llenables
    ];
    public function subtareas()
    {
        return $this->belongsTo('App\Models\SubTareas', 'id_subtarea');
    }
}
