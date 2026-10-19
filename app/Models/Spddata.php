<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spddata extends Model
{
    public $timestamps = false;

    protected $fillable = ['Username', 'Time', 'Difficulty'];
}
