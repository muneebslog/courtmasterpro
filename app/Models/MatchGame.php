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
    public function canContinuePlaying(): bool
    {
        return !$this->hasWinner() && $this->status !== 'completed';
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
            $this->visualMatchWinsForTeam($this->team_a_id) >= $this->visualMatchesRequiredToWin() ||
            $this->visualMatchWinsForTeam($this->team_b_id) >= $this->visualMatchesRequiredToWin();
    }


    public function determineWinner(): int
    {
        if (!$this->hasWinner()) {
            throw new \LogicException('Cannot determine winner: match is not yet decided.');
        }

        return $this->visualMatchWinsForTeam($this->team_a_id) >
            $this->visualMatchWinsForTeam($this->team_b_id)
            ? $this->team_a_id
            : $this->team_b_id;
    }



    // Add relation to match events
    public function matchEvents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MatchEvent::class, 'match_id');
    }

    public function setsPerVisualMatch(): int
    {
        return 3;
    }

    public function visualMatchesRequiredToWin(): int
    {
        // Mixed = best of 5 → first to 3
        if ($this->round->event->default_discipline === 'mixed') {
            return 3;
        }

        // Singles / Doubles → single match
        return 1;
    }

    public function visualMatchWinsForTeam(int $teamId): int
    {
        $wins = 0;

        $groupedSets = $this->sets
            ->sortBy('set_no')
            ->chunk($this->setsPerVisualMatch());

        foreach ($groupedSets as $visualMatchSets) {
            $setWins = $visualMatchSets
                ->where('winner_team_id', $teamId)
                ->count();

            // visual match = best of 3 → first to 2
            if ($setWins >= 2) {
                $wins++;
            }
        }

        return $wins;
    }

    public function totalSetsAllowed(): int
    {
        if ($this->round->event->default_discipline === 'mixed') {
            return 5 * 3; // 5 visual matches × 3 sets
        }

        return (int) $this->round->event->best_of_sets;
    }

    public function visualMatchWins(): array
    {
        return [
            'A' => $this->visualMatchWinsForTeam($this->team_a_id),
            'B' => $this->visualMatchWinsForTeam($this->team_b_id),
        ];
    }
}
