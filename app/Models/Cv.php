<?php

namespace App\Models;

use App\Models\Concerns\HasPublicToken;
use Illuminate\Database\Eloquent\Model;

class Cv extends Model
{
    use HasPublicToken;

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

        // Unlike Resume, every CV gets a public token on creation
        static::creating(function ($cv) {
            if (empty($cv->public_token)) {
                $cv->public_token = static::generateUniquePublicToken();
            }
        });
    }
}
