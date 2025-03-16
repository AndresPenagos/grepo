<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Archivos extends Model
{
    
    protected $table = 'archivos';
    protected $fillable = [
        'id',
        'id_modulo',
        'modulo',
        'comentario',
        'nombre',
        'created_at',
        'updated_at'
        // Agrega aquí los nombres de las columnas que deseas que sean llenables
    ];
 
}