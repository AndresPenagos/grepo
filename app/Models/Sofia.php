<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sofia extends Model
{
    protected $table = 'sofia';
    protected $fillable = [
        'id',
        'id_programa',
        'id_semilla',
        'comentarios',
        'created_at',
        'updated_at'
    ];
    public function semillas()
    {
        //pertenece a seeds
        return $this->belongsTo(Semillas::class, 'id_semilla', 'id');
    }
    public function solicitud()
    {
        //pertenece a seeds
        return $this->belongsTo(Solicitud::class, 'id_semilla', 'id');
    }
    public function programas()
    {
        //pertenece a programas
        return $this->belongsTo(Programas::class, 'id_programa', 'id');
    }
}


