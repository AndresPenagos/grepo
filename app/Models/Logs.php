<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    protected $table = 'logs';
    protected $fillable = ['id','user_id','module_id','action','description','updated_at','created_at'];
    
}