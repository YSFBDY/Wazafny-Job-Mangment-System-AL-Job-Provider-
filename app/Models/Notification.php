<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'seeker_id',
        'job_id',
        'message',
    ];

    public function jobseeker()
    {
        return $this->belongsTo(JobSeeker::class, 'jobseeker_id');
    }

    public function jobpost()
    {
        return $this->belongsTo(JobPost::class, 'job_id');
    }


}
