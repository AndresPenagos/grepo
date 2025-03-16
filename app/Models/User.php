<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['id','name','last_name','email','password','last_access', 'role', 'phone','photo','updated_at','created_at'];
    protected $hidden = ['password'];

    public static function getRoleOptions()
    {
        $tableName = (new self())->getTable();
        $column = 'role';

        $typeProgramOptions = Capsule::select("SHOW COLUMNS FROM $tableName WHERE Field = '$column'")[0]->Type;
        preg_match('/^enum\((.*)\)$/', $typeProgramOptions, $matches);
        $enumValues = explode(',', $matches[1]);
        $options = array_map(function ($value) {
            return trim($value, "'");
        }, $enumValues);

        return $options;
    }
    public static function getPhoto($user){
        if($user->photo == 'default.jpg' or $user->photo == 'none' or $user->photo == 'no-photo.jpg'){
            $user->photo_url = $_ENV['APP_URL']. $_ENV['APP_LOCATION'].'/public/assets/img/default.jpg';
        }else{
            $user->photo_url = $_ENV['APP_URL'].$_ENV['APP_LOCATION'].'/' . $_ENV['APP_STORAGE'] .'/'.'users' .'/'.  $user->foto;
        }
        return $user;
    }
}