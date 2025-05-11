<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Answer extends Model
{
    use HasFactory;
    protected $table = 'answers';
    protected $primaryKey = 'answer_id';
    protected $fillable = [
        'answer',
        'question_id',
        'application_id'];

        

        
        public function application() {
            return $this->belongsTo(Application::class, 'application_id');
        }

        public function question() {
            return $this->belongsTo(Question::class, 'question_id');
        }




}
