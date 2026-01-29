<?php

namespace App\Http\Controllers;

use App\Models\MatchGame;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class MatchReportController extends Controller
{
    public function download(int $match)
    {
        $match = MatchGame::with([
            'round.event.tournament',
            'teamA.players',
            'teamB.players',
            'sets.scores',
            'matchEvents.player',
        ])->findOrFail($match);

        // Only completed matches can be exported
        abort_if($match->status !== 'completed', 403, 'Match not completed');

        $completedSets = $match->sets
            ->where('status', 'completed')
            ->sortBy('set_number');

        // Calculate wins based on match type
        if ($match->round->event->default_discipline === 'mixed') {
            // For mixed/team: count visual match wins
            $teamAWins = $match->visualMatchWinsForTeam($match->team_a_id);
            $teamBWins = $match->visualMatchWinsForTeam($match->team_b_id);
        } else {
            // For singles/doubles: count set wins
            $teamAWins = $match->sets
                ->where('winner_team_id', $match->team_a_id)
                ->count();

            $teamBWins = $match->sets
                ->where('winner_team_id', $match->team_b_id)
                ->count();
        }

        $timelineEvents = $match->matchEvents
            ->whereIn('type', [
                'match_started',
                'set_started',
                'set_ended',
                'match_ended',
                'yellow_card',
                'red_card',
                'injury',
                'walkover',
                'timeout',
            ])
            ->sortBy('occurred_at');

        $incidents = $match->matchEvents
            ->whereIn('type', [
                'yellow_card',
                'red_card',
                'injury',
                'walkover',
            ]);

        $duration = $this->formatDuration(
            $match->started_at,
            $match->ended_at
        );

        $pdf = Pdf::loadView('reports.match-report', [
            'match' => $match,
            'completedSets' => $completedSets,
            'timelineEvents' => $timelineEvents,
            'incidents' => $incidents,
            'teamAWins' => $teamAWins,
            'teamBWins' => $teamBWins,
            'duration' => $duration,
            'generatedAt' => now()->format('d M Y, H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download(
            'Match_' . $match->id . '_Report.pdf'
        );
    }

    private function formatDuration($start, $end): string
    {
        if (!$start || !$end) {
            return '—';
        }

        $seconds = $start->diffInSeconds($end);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return $hours > 0
            ? "{$hours}h {$minutes}m"
            : "{$minutes} minutes";
    }
    public function downloadSummary(int $match)
    {
        $match = MatchGame::with([
            'round.event.tournament',
            'teamA.players',
            'teamB.players',
            'sets.scores',
            'matchEvents.player',
        ])->findOrFail($match);

        abort_if($match->status !== 'completed', 403);

        $completedSets = $match->sets
            ->where('status', 'completed')
            ->sortBy('set_number');

        // Calculate wins based on match type
        if ($match->round->event->default_discipline === 'mixed') {
            // For mixed/team: count visual match wins
            $teamAWins = $match->visualMatchWinsForTeam($match->team_a_id);
            $teamBWins = $match->visualMatchWinsForTeam($match->team_b_id);
        } else {
            // For singles/doubles: count set wins
            $teamAWins = $match->sets
                ->where('winner_team_id', $match->team_a_id)
                ->count();

            $teamBWins = $match->sets
                ->where('winner_team_id', $match->team_b_id)
                ->count();
        }

        $incidents = $match->matchEvents
            ->whereIn('type', [
                'yellow_card',
                'red_card',
                'injury',
                'walkover',
            ]);

        $duration = $this->formatDuration(
            $match->started_at,
            $match->ended_at
        );

        $pdf = Pdf::loadView('reports.match-report-concise', [
            'match' => $match,
            'completedSets' => $completedSets,
            'teamAWins' => $teamAWins,
            'teamBWins' => $teamBWins,
            'incidents' => $incidents,
            'duration' => $duration,
            'generatedAt' => now()->format('d M Y, H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download(
            'Match_' . $match->id . '_Summary.pdf'
        );
    }
}
