<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'listing_id',
        'user_id',
        'reviewed_user_id',
        'rating',
        'comment',
        'review_type',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedUser()
    {
        return $this->belongsTo(User::class, 'reviewed_user_id');
    }
}
