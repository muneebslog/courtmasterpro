<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchGame extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'round_id',
        'match_no',
        'team_a_id',
        'team_b_id',
        'court_no',
        'scheduled_at',
        'started_at',
        'umpire',
        'referee',
        'ended_at',
        'winner_team_id',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function teamA(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_a_id');
    }

    public function teamB(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_b_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    // Add relation to sets
    public function sets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Set::class, 'match_id');
    }

    public function requiredSetsToWin(): int
    {
        return (int) ceil($this->round->event->best_of_sets / 2);
    }

    public function setWinsForTeam(int $teamId): int
    {
        return $this->sets()
            ->where('winner_team_id', $teamId)
            ->count();
    }

    public function hasWinner(): bool
    {
        return
            $this->setWinsForTeam($this->team_a_id) >= $this->requiredSetsToWin() ||
            $this->setWinsForTeam($this->team_b_id) >= $this->requiredSetsToWin();
    }

    public function determineWinner(): int
    {
        return $this->setWinsForTeam($this->team_a_id) >
            $this->setWinsForTeam($this->team_b_id)
            ? $this->team_a_id
            : $this->team_b_id;
    }

    // Add relation to match events
    public function matchEvents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MatchEvent::class, 'match_id');
    }
}
