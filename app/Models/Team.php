<?php

namespace App\Models;

use App\Models\Event;
use App\Models\Player;
use App\Models\MatchGame;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'seed_number',
        'is_assigned',
    ];

    protected $casts = [
        'seed_number' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    // Add relationship to players
    public function players(): BelongsToMany
    {
        // Second argument is the table name, which is 'team_players'
        return $this->belongsToMany(Player::class, 'team_players');
    }

    // Matches where this team played as Team A
    public function matchesAsA(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'team_a_id');
    }

    // Matches where this team played as Team B
    public function matchesAsB(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'team_b_id');
    }
    public function getAllMatchesAttribute()
    {
        return $this->matchesAsA->merge($this->matchesAsB);
    }

    public function getDisplayNameAttribute(): string
    {
        // 1️⃣ If team name exists, return it
        if (!empty($this->name)) {
            return $this->name;
        }

        // 2️⃣ Get players' first names
        $players = $this->players->pluck('first_name');

        // 3️⃣ No players edge-case (just in case)
        if ($players->isEmpty()) {
            return 'Unknown Team';
        }

        // 4️⃣ Single player
        if ($players->count() === 1) {
            return $players->first();
        }

        // 5️⃣ Doubles or more → join with &
        return $players->implode(' & ');
    }
}
