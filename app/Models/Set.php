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
    // Add relation to scores
    public function scores(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SetScore::class, 'set_id');
    }


    public function getPointsForTeam(int $teamId): int
    {
        return $this->scores()
            ->where('team_id', $teamId)
            ->value('points') ?? 0;
    }

    public function hasWinner(int $teamAId, int $teamBId): bool
    {
        $a = $this->getPointsForTeam($teamAId);
        $b = $this->getPointsForTeam($teamBId);

        if (($a >= 21 || $b >= 21) && abs($a - $b) >= 2) {
            return true;
        }

        return $a === 30 || $b === 30;
    }

    public function determineWinner(int $teamAId, int $teamBId): int
    {
        return $this->getPointsForTeam($teamAId) >
            $this->getPointsForTeam($teamBId)
            ? $teamAId
            : $teamBId;
    }
}
