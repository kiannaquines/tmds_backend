<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thesis extends Model
{
    protected $table = "studies";
    protected $fillable = ["user_id","title","adviser","department","year","type"];
}
