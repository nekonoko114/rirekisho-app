<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumeHistory extends Model
{
    use HasFactory;

    protected $fillable = ['resume_id', 'year', 'month', 'type', 'description', 'sort_order'];

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
}
