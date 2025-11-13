<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'furigana',
        'birth_date',
        'gender',
        'phone',
        'contact_phone',
        'email',
        'address',
        'address_postal',
        'contact_address',
        'contact_postal',
        'photo_path',
        'public_token',
    ];

    /**
     * Cast attributes to appropriate types.
     * Ensures birth_date is a Carbon instance when accessed.
     */
    protected $casts = [
        'birth_date' => 'date',
    ];

    public function histories()
    {
        return $this->hasMany(ResumeHistory::class);
    }

    public function licenses()
    {
        return $this->hasMany(ResumeLicense::class);
    }

    public function profile()
    {
        return $this->hasOne(ResumeProfile::class);
    }
}
