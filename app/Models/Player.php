<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'dob',
        'country',
    ];

    protected $casts = [
        'dob' => 'date',
    ];
}
