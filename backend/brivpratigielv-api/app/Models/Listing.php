<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'location',
        'category',
        'time_commitment',
        'spots',
        'requirements',
        'benefits',
        'is_urgent',
        'is_new',
        'is_online',
        'is_active',
        'is_completed',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
