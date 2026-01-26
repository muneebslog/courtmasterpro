<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Set extends Model
{
    protected $fillable = [
        'match_id',
        'set_number',
        'discipline',
        'winner_team_id',
        'status',
    ];

    protected $casts = [
        'set_number' => 'integer',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }
}
