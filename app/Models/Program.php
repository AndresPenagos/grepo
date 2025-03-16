<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Program extends Model
{
    protected $table = 'program';
    protected $fillable = ['id', 'id_code', 'name', 'type_program', 'updated_at', 'created_at'];

    public function seeds()
    {
        return $this->hasOne(Seeds::class, 'program_id', 'id');
    }

    public static function getTypeProgramOptions()
    {
        $tableName = (new self())->getTable();
        $column = 'type_program';

        $typeProgramOptions = Capsule::select("SHOW COLUMNS FROM $tableName WHERE Field = '$column'")[0]->Type;
        preg_match('/^enum\((.*)\)$/', $typeProgramOptions, $matches);
        $enumValues = explode(',', $matches[1]);
        $options = array_map(function ($value) {
            return trim($value, "'");
        }, $enumValues);

        return $options;
    }
}
