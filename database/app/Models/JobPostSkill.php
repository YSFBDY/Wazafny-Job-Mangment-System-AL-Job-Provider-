<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobPostSkill extends Model
{
    use HasFactory;

    protected $table = 'jobpostskills';

    protected $fillable = [
        'job_id',
        'skill_id',
    ];

    public function jobpost() {
        return $this->belongsTo(Jobpost::class, 'job_id');
    }

    public function skill() {
        return $this->belongsTo(Skill::class, 'skill_id');
    }
    
}
