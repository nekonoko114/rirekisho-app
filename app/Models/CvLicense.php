<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CvLicense extends Model
{
    protected $fillable = [
        'cv_id',
        'year',
        'month',
        'name',
    ];

    public function cv()
    {
        return $this->belongsTo(Cv::class);
    }
}
