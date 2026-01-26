<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SetPlayer extends Model
{
    protected $fillable = [
        'set_id',
        'team_id',
        'player_id',
    ];

    public function set(): BelongsTo
    {
        return $this->belongsTo(Set::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
