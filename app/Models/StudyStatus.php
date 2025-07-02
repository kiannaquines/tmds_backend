<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyStatus extends Model
{
    protected $table = "study_status";
    protected $fillable = ["status"];
}
