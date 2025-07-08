<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adviser extends Model
{
    protected $table = "advisers";
    protected $fillable = ["adviser", "study_id"];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
