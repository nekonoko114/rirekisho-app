<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cv extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'desired_position',
        'motivation',
        'public_token',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function histories()
    {
        return $this->hasMany(CvHistory::class);
    }

    public function licenses()
    {
        return $this->hasMany(CvLicense::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cv) {
            if (empty($cv->public_token)) {
                $cv->public_token = \Illuminate\Support\Str::random(32);
            }
        });
    }
}
