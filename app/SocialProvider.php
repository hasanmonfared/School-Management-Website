<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SocialProvider extends Model
{
    protected $fillable=['provider_id','provider'];
    // realtion for user table
    public function user()
    {
        return $this->belongsTo(user::class);
    }
}
