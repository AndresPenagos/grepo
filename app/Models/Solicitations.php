<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitations extends Model
{
    protected $table = 'solicitations';
    protected $fillable = [
        'id',
        'states_id',
        'seeds_id',
        'link_curriculum_design',
        'link_delivery',
        'name_delivery',
        'email_delivery',
        'description',
        'created_at',
        'updated_at'
    ];
    public function seeds()
    {
        //pertenece a seeds
        return $this->belongsTo(Seeds::class, 'seeds_id', 'id');
    }
    public function states()
    {
        //pertenece a states
        return $this->belongsTo(States::class, 'states_id', 'id');
    }
    public function applicationsRevisions()
    {
        //tiene muchas applicationsRevisions
        return $this->hasMany(ApplicationsRevisions::class, 'solicitations_id', 'id');
    }
    public static function scopeWithoutApplicationsRevisions($query)
    {
        //no tiene applicationsRevisions
        return $query->doesntHave('ApplicationsRevisions');
    }

   
    
 
}


