<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumeLicense extends Model
{
    use HasFactory;

    protected $fillable = ['resume_id','year','month','name','details'];

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
}
