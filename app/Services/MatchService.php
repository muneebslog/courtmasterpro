<?php

namespace App\Services;

use App\Models\Set;
use App\Models\MatchGame;
use App\Models\MatchEvent;


class MatchService
{
    public function afterPointScored(MatchGame $match, Set $set): array
    {
        // 1️⃣ If the set is still ongoing → nothing special happened
        if (!$set->hasWinner($match->team_a_id, $match->team_b_id)) {
            return [
                'type' => 'none',
            ];
        }

        // 2️⃣ Set has ended → determine winner
        $setWinnerId = $set->determineWinner(
            $match->team_a_id,
            $match->team_b_id
        );

        // 3️⃣ Mark set as completed
        $set->update([
            'status' => 'completed',
            'winner_team_id' => $setWinnerId,
        ]);

        // 4️⃣ Log set end
        MatchEvent::create([
            'match_id' => $match->id,
            'set_id' => $set->id,
            'team_id' => $setWinnerId,
            'type' => 'set_ended',
            'description' => 'Set completed',
            'occurred_at' => now(),
        ]);

        // Refresh sets relationship to include the updated winner_team_id
        $match->load('sets');

        // 5️⃣ Check if this set win finishes the match
        if ($match->hasWinner()) {

            $matchWinnerId = $match->determineWinner();
            // 6️⃣ Mark match as completed
            $match->update([
                'status' => 'completed',
                'winner_team_id' => $matchWinnerId,
                'ended_at' => now(),
            ]);

            // 7️⃣ Log match end
            MatchEvent::create([
                'match_id' => $match->id,
                'team_id' => $matchWinnerId,
                'type' => 'match_ended',
                'description' => 'Match completed',
                'occurred_at' => now(),
            ]);

            // 🔚 Tell UI: MATCH is over
            return [
                'type' => 'match_completed',
                'match_winner_team_id' => $matchWinnerId,
            ];
        }

        // 🟡 Otherwise: only the SET ended
        return [
            'type' => 'set_completed',
            'set_winner_team_id' => $setWinnerId,
        ];
    }

    public function handleWalkover(MatchGame $match, Set $set, int $walkoverTeamId): array
    {
        // Determine winner
        $winnerTeamId = $walkoverTeamId === $match->team_a_id
            ? $match->team_b_id
            : $match->team_a_id;

        // Close current set if still live
        if ($set->status === 'live') {
            $set->update([
                'status' => 'completed',
                'winner_team_id' => $winnerTeamId,
            ]);
        }

        // End match
        $match->update([
            'status' => 'completed',
            'winner_team_id' => $winnerTeamId,
            'ended_at' => now(),
        ]);

        // Log walkover
        MatchEvent::create([
            'match_id' => $match->id,
            'set_id' => $set->id,
            'team_id' => $walkoverTeamId,
            'type' => 'walkover',
            'description' => 'Walkover declared',
            'occurred_at' => now(),
        ]);

        // Log match end
        MatchEvent::create([
            'match_id' => $match->id,
            'team_id' => $winnerTeamId,
            'type' => 'match_ended',
            'description' => 'Match ended by walkover',
            'occurred_at' => now(),
        ]);

        return [
            'type' => 'match_completed',
            'match_winner_team_id' => $winnerTeamId,
            'reason' => 'walkover',
        ];
    }
}
