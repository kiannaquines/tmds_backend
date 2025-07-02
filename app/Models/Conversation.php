<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $table = "conversation";
    protected $fillable = ["id", "topic"];
    protected $cast = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
