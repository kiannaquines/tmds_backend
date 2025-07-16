<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyStatus extends Model
{
    protected $table = "study_status";
    protected $fillable = ["student_id", "faculty_id", "study_id", "status"];

    public function student()
    {
        return $this->belongsTo(User::class);
    }

    public function faculty()
    {
        return $this->belongsTo(User::class);
    }

    public function study()
    {
        return $this->belongsTo(Thesis::class);
    }
}
