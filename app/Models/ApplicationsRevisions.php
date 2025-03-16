<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationsRevisions extends Model
{
    public $timestamps = false;
    protected $table = 'applications_revisions';
    protected $fillable = ['id','revisions_id','solicitations_id'];

    public function revisions() {
        return $this->belongsTo(Revision::class, 'revisions_id');
    }
    public function applications_revisions_details()
    {
        return $this->hasMany(ApplicationsRevisionsDetails::class, 'applications_revisions_id');
    }
    public function solicitations()
    {
        return $this->belongsTo(Solicitations::class, 'solicitations_id');
    } 
 
}