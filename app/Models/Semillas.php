<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Semillas extends Model
{
    protected $table = 'semillas';
    protected $fillable = [
        'id',
        'id_programa',
        'codigo',
        'version',
        'linea_produccion',
        'activa',
        'video_programa',
        'codigo_interno',
        'created_at',
        'updated_at'
    ];
    public function programas()
    {
        return $this->belongsTo(Programas::class, 'id_programa', 'id');
    }
    public function sofia()
    {
        return $this->hasMany(Sofia::class, 'id', 'id_semilla');
    }
    public static function getModalityOptions()
    {
        $tableName = (new self())->getTable();
        $column = 'modality';

        $typeProgramOptions = Capsule::select("SHOW COLUMNS FROM $tableName WHERE Field = '$column'")[0]->Type;
        preg_match('/^enum\((.*)\)$/', $typeProgramOptions, $matches);
        $enumValues = explode(',', $matches[1]);
        $options = array_map(function ($value) {
            return trim($value, "'");
        }, $enumValues);

        return $options;
    }
}




