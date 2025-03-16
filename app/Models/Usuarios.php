<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Usuarios extends Model
{
    protected $table = 'usuarios';
    protected $fillable = ['id', 'nombres', 'apellidos', 'email', 'password','ultimo_acceso', 'rol', 'telefono', 'foto', 'activo',  'created_at', 'updated_at'];
    protected $hidden = ['password'];

    public static function getRoleOptions()
    {
        $tableName = (new self())->getTable();
        $column = 'rol';

        $typeRoleOptions = Capsule::select("SHOW COLUMNS FROM $tableName WHERE Field = '$column'")[0]->Type;
        preg_match('/^enum\((.*)\)$/', $typeRoleOptions, $matches);
        $enumValues = explode(',', $matches[1]);
        $options = array_map(function ($value) {
            return trim($value, "'");
        }, $enumValues);

        return $options;
    }

    public static function getPhoto($usuario)
    {
        if ($usuario->photo == 'default.jpg' || $usuario->photo == 'none' || $usuario->photo == 'no-photo.jpg') {
            $usuario->photo_url = $_ENV['APP_URL'] . $_ENV['APP_LOCATION'] . '/public/assets/img/default.jpg';
        } else {
            $usuario->photo_url = $_ENV['APP_URL'] . $_ENV['APP_LOCATION'] . '/' . $_ENV['APP_STORAGE'] . '/' . 'usuarios' . '/' . $usuario->photo;
        }

        return $usuario;
    }
}
