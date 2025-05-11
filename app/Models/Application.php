<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Application extends Model
{
    use HasFactory;
    protected $table = 'applications';
    protected $primaryKey = 'application_id';
    protected $fillable = [
        'status',
        'response',
        'first_name',
        'last_name',
        'email',
        'resume',
        'country',
        'city',
        'phone',
        'seeker_id',
        'job_id'
        
    ];

    public function job() {
        return $this->belongsTo(Jobpost::class, 'job_id');
    }

    public function jobSeeker() {
        return $this->belongsTo(JobSeeker::class, 'seeker_id');
    }

    
    public function Answers() {
        return $this->hasMany(Answer::class, 'application_id');
    }


    public function getResumeAttribute($value)
    {
        $actual_link = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';
        return ($value == null ? '' : $actual_link . 'resumes/application_resumes/' . $value);
    }
    
}
