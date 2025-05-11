<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeekerSkill extends Model
{
    use HasFactory;

    protected $table = 'seekerskills';

    protected $fillable = [
        'seeker_id',
        'skill_id',
    ];


    public function jobSeeker() {
        return $this->belongsTo(JobSeeker::class, 'seeker_id');
    }

    public function skill() {
        return $this->belongsTo(Skill::class, 'skill_id');
    }

}
