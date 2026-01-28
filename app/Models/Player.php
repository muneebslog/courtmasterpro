<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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


    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_players');
    }
}
