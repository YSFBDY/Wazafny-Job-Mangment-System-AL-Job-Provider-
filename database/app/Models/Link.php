<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Link extends Model
{
    use HasFactory;
    protected $table = 'links';
    protected $primaryKey = 'link_id';

    protected $fillable = [
        'link_name',
        'link',
        'seeker_id'
    ];

    public function jobSeeker() {
        return $this->belongsTo(JobSeeker::class, 'seeker_id');
    }
}
