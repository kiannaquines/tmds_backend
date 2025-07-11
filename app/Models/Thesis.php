<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Thesis extends Model
{
    protected $table = "studies";
    protected $fillable = ["user_id", "title", "adviser", "department", "year", "type"];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function adviser()
    {
        return $this->belongsTo(User::class, 'adviser');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(ThesisProgress::class, 'study_id');
    }
}
