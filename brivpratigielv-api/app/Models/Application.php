<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'listing_id',
        'user_id',
        'full_name',
        'email',
        'phone',
        'motivation',
        'cv_url',
        'status',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
