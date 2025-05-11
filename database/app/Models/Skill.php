<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Skill extends Model
{
    use HasFactory;

    protected $table = 'skills';
    protected $primaryKey = 'skill_id';

    protected $fillable = [
        'skill'
        
    ];


    public function seekkerskills() {
        return $this->hasMany(SeekerSkill::class, 'seeker_id');
    }

    public function jobpostskills() {
        return $this->hasMany(JobpostSkill::class, 'job_id');
    }
}
