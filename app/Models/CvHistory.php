<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CvHistory extends Model
{
    protected $fillable = [
        'cv_id',
        'company_name',
        'start_year',
        'start_month',
        'end_year',
        'end_month',
        'job_description',
    ];

    public function cv()
    {
        return $this->belongsTo(Cv::class);
    }
}
