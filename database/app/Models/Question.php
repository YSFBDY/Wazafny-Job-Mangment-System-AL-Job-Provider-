<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;
    protected $table = 'questions';
    protected $primaryKey = 'question_id';

    protected $fillable = [
        'question',
        'job_id',
    ];



    public function job() {
        return $this->belongsTo(Jobpost::class, 'job_id');
    }

    public function Answers() {
        return $this->hasMany(Answer::class, 'application_id');
    }
}
