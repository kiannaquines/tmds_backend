<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThesisProgress extends Model
{
    protected $table = "thesis_progress";
    protected $fillable = [
        "study_id",
        "check_by",
        "comment",
        "status",
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function study()
    {
        return $this->belongsTo(Thesis::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
