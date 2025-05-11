<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Experience extends Model
{
    use HasFactory;
    protected $table = 'experiences';
    protected $primaryKey = 'experience_id';

    protected $fillable = [
        'company',
        'job_title',
        'job_time',
        'start_date',
        'end_date',
        'seeker_id'
    ];

    public function jobseeker() {
        return $this->belongsTo(Jobseeker::class, 'seeker_id');
    }
    
}
