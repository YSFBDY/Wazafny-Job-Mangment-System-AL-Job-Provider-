<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jobpost extends Model
{
    use HasFactory;
    protected $table = 'jobposts';
    protected $primaryKey = 'job_id';

    protected $fillable = [
        'job_title',
        'job_about',
        'job_time',
        'job_type',
        'job_status',
        'job_country',
        'job_city',
        'company_id',
    ];
    public function company() {
        return $this->belongsTo(Company::class, 'company_id', 'company_id'); 
    }

    public function jobSections() {
        return $this->hasMany(JobSection::class, 'job_id');
    }

    public function applications() {
        return $this->hasMany(Application::class, 'job_id');
    }

    public function questions() {
        return $this->hasMany(Question::class, 'job_id');
    }

    public function jobpostskills() {
        return $this->hasMany(JobpostSkill::class, 'job_id');
    }

    public function notfications() {
        return $this->hasMany(Notification::class, 'job_id');
    }

}
