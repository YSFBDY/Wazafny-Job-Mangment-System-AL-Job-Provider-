<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Follower extends Model
{
    use HasFactory;

    protected $table = 'followers';
    
    protected $fillable = [
        'seeker_id',
        'company_id',
    ];

    public function jobSeeker() {
        return $this->belongsTo(JobSeeker::class, 'seeker_id');
    }

    public function company() {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
