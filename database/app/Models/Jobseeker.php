<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Jobseeker extends Model
{
    use HasFactory;

    protected $table = 'jobseekers';

    protected $primaryKey = 'seeker_id';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'resume',
        'country',
        'city'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function applications() {
        return $this->hasMany(Application::class, 'seeker_id');
    }

    public function seekkerskills() {
        return $this->belongsToMany(Skill::class, 'seekerskills', 'seeker_id', 'skill_id');
    }

    public function links() {
        return $this->hasMany(Link::class, 'seeker_id');
    }

    public function expriences() {
        return $this->hasMany(Experience::class, 'seeker_id');
    }

    public function educations() {
        return $this->hasMany(Education::class, 'seeker_id');
    }

    public function notfications() {
        return $this->hasMany(Notification::class, 'seeker_id');
    }

    public function followers() {
        return $this->belongsToMany(Follower::class, 'folowers', 'seeker_id', 'companny_id');

    }

    public function getResumeAttribute($value)
    {
        $actual_link = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';
        return ($value == null ? '' : $actual_link . 'resumes/profile_resumes/' . $value);
    }
}
