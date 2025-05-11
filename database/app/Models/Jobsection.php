<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Jobsection extends Model
{
    use HasFactory;
    protected $table = 'jobsections';
    protected $primaryKey = 'section_id';

    protected $fillable = [
        'section_name',
        'section_description',
        'job_id',
    ];

    public function job() {
        return $this->belongsTo(Jobpost::class);
    }
}
