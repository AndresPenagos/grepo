<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationsRevisionsDetails extends Model
{
    public $timestamps = false;
    protected $table = 'applications_revisions_details';
    protected $fillable = ['id','applications_revisions_id','users_id','manager','created_at'];
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}