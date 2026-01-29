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

        // Logic for Singles/Doubles (Best of 3 sets)
        if ($this->round->event->default_discipline !== 'mixed') {
            $setWins = $this->sets->where('winner_team_id', $teamId)->count();
            return ($setWins >= 2) ? 1 : 0;
        }

        // Logic for Mixed/Team (Multiple Visual Matches)
        // We group sets: 1-3, 4-6, 7-9, 10-12, 13-15
        $groupedSets = $this->sets->groupBy(function ($set) {
            return ceil($set->set_number / 3);
        });

        foreach ($groupedSets as $visualMatchIndex => $setsInThisMatch) {
            $winsInThisMatch = $setsInThisMatch->where('winner_team_id', $teamId)->count();
            if ($winsInThisMatch >= 2) {
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

    /**
     * Get which visual match (1-5) a set number belongs to
     * For mixed: set 1-3 = match 1, set 4-6 = match 2, etc.
     * For singles/doubles: always returns 1
     */
    public function getVisualMatchNumber(int $setNumber): int
    {
        if ($this->round->event->default_discipline !== 'mixed') {
            return 1; // Singles/Doubles only have 1 match
        }
        return (int) ceil($setNumber / 3);
    }

    /**
     * Get which set (1-3) within the current visual match
     * For mixed: set 4 → returns 1, set 5 → returns 2, set 6 → returns 3
     * For singles/doubles: returns the actual set number
     */
    public function getSetWithinVisualMatch(int $setNumber): int
    {
        if ($this->round->event->default_discipline !== 'mixed') {
            return $setNumber; // Return actual set number for Singles/Doubles
        }
        $remainder = $setNumber % 3;
        return $remainder === 0 ? 3 : $remainder;
    }

    /**
     * Get the status of a specific visual match (1-5)
     * Returns how many sets each team won in that match
     */
    public function getVisualMatchStatus(int $matchNumber): array
    {
        if ($this->round->event->default_discipline !== 'mixed') {
            // For singles/doubles, just return overall set wins
            return [
                'team_a_wins' => $this->setWinsForTeam($this->team_a_id),
                'team_b_wins' => $this->setWinsForTeam($this->team_b_id),
                'winner' => $this->winner_team_id,
                'is_complete' => $this->status === 'completed',
            ];
        }

        // Get sets for this visual match: 1-3, 4-6, 7-9, 10-12, or 13-15
        $startSet = ($matchNumber - 1) * 3 + 1;
        $endSet = $matchNumber * 3;

        $setsInMatch = $this->sets()
            ->whereBetween('set_number', [$startSet, $endSet])
            ->get();

        $teamAWins = $setsInMatch->where('winner_team_id', $this->team_a_id)->count();
        $teamBWins = $setsInMatch->where('winner_team_id', $this->team_b_id)->count();

        return [
            'team_a_wins' => $teamAWins,
            'team_b_wins' => $teamBWins,
            'winner' => $teamAWins >= 2 ? $this->team_a_id : ($teamBWins >= 2 ? $this->team_b_id : null),
            'is_complete' => $teamAWins >= 2 || $teamBWins >= 2,
        ];
    }
}
