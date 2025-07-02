<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = "chat";
    protected $fillable = ["conversation_id","sender","receiver","message"];
    protected $cast = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
