<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revisions extends Model
{
    public $timestamps = false;
    protected $table = 'revisions';
    protected $fillable = ['id','name','link','general_comments','start_date','proposed_end_date','actual_end_date'];
    public function applications_revisions() {
        return $this->hasMany(ApplicationsRevisions::class, 'revisions_id');
    }
    
}