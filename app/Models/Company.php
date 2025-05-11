<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies'; 

    protected $primaryKey = 'company_id';
    protected $fillable = [
        'user_id',
        'company_name',
        'company_email',
        'company_industry',
        'company_founded',
        'company_size',
        'company_heads',
        'company_website_link',
        'company_country',
        'company_city',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jobs() {
        return $this->hasMany(Jobpost::class, 'company_id');
    }

    public function followers() {
        return $this->belongsToMany(Follower::class, 'folowers', 'companny_id', 'seeker_id');
    }

    public function isIncompleteProfile() {
        return collect($this->toArray())->contains(fn($value) => is_null($value) || $value === '');
    }
}
