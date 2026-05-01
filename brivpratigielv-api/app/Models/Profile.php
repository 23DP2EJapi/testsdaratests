<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'full_name',
        'avatar_url',
        'bio',
        'phone',
        'last_name_change',
    ];

    protected $casts = [
        'last_name_change' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
