<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Education extends Model
{
    use HasFactory;
    protected $table = 'educations';
    protected $primaryKey = 'education_id';

    protected $fillable = [
        'university',
        'college',
        'start_date',
        'end_date',
        'seeker_id',
    ];

    public function jobseeker() {
        return $this->belongsTo(Jobseeker::class, 'seeker_id');
    }

}
