<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Seeds extends Model
{
    protected $table = 'seeds';
    protected $fillable = [
        'id',
        'program_id',
        'id_seeds',
        'internal_code',
        'modality',
        'hours',
        'version_seeds',
        'version_program',
        'link_description_program',
        'video_program',
        'sofia_status',
        'sofia_date',
        'sofia_comments',
        'created_at',
        'updated_at'
    ];
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id', 'id');
    }

    public function solicitations()
    {
        return $this->hasMany(Solicitations::class, 'seeds_id', 'id');
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




