<?php

use Livewire\Component;
use App\Models\MatchGame;

new class extends Component {
    public $match;

    public $completedSets = [];
    public int $teamAWins = 0;
    public int $teamBWins = 0;
    public ?int $winnerTeamId = null;

    public function mount($match)
    {
        $match = MatchGame::with([
            'round.event.tournament',
            'teamA.players',
            'teamB.players',
            'sets.scores',
            'matchEvents.player',
        ])->findOrFail($match);
        $this->match = $match;

        $this->completedSets = $this->match->sets
            ->where('status', 'completed')
            ->sortBy('set_number');

        $this->teamAWins = $this->match->sets
            ->where('winner_team_id', $this->match->team_a_id)
            ->count();

        $this->teamBWins = $this->match->sets
            ->where('winner_team_id', $this->match->team_b_id)
            ->count();

        $this->winnerTeamId = $this->match->winner_team_id;
    }

    public function setScore($set, int $teamId): int
    {
        return $set->scores
            ->firstWhere('team_id', $teamId)
                ?->points ?? 0;
    }

    public function matchDuration(): string
    {
        if (!$this->match->started_at || !$this->match->ended_at) {
            return '—';
        }

        $seconds = $this->match->started_at->diffInSeconds($this->match->ended_at);

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes} minutes";
    }


    public function incidents()
    {
        return $this->match->matchEvents
            ->whereIn('type', ['yellow_card', 'red_card', 'injury', 'walkover']);
    }

    public function timelineEvents()
    {
        return $this->match->matchEvents
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
    }




};
?>

<div>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFC;
            color: #111827;
        }
    </style>

    <section class="min-h-screen ">
        @if ($match->status !== 'completed')
            <div class="p-6 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm font-medium text-yellow-800">
                    This match is not completed yet. Details may change.
                </p>
            </div>
        @endif


        <main class=" overflow-y-auto">

            <div class="max-w-6xl mx-auto p-8 space-y-8">

                <section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $match->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ ucfirst($match->status) }}
                                </span>

                                <span class="text-sm text-gray-500 font-medium">
                                    Match #{{ $match->id }}
                                    @if($match->court_no)
                                        • Court {{ $match->court_no }}
                                    @endif
                                </span>

                            </div>
                            <h2 class="text-2xl font-bold">
                                {{ $match->round->event->name }} - {{ $match->round->name }}
                            </h2>

                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">
                                Umpire:
                                <span class="text-gray-900 font-medium">
                                    {{ $match->umpire ?? '—' }}
                                </span>
                            </p>

                            <p class="text-sm text-gray-500">
                                Referee:
                                <span class="text-gray-900 font-medium">
                                    {{ $match->referee ?? '—' }}
                                </span>
                            </p>

                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 items-center p-8 bg-white">
                        <div class="text-center md:text-left">
                            <h3 class="text-lg font-semibold text-blue-600 mb-1">
                                {{ $match->teamA->display_name }}
                            </h3>

                            @if ($match->round->event->type == 'team')

                                <p class="text-xl font-bold">
                                    {{ $match->teamA->players->pluck('first_name')->join(' / ') }}
                                </p>
                            @endif


                            <p class="text-sm text-gray-500">
                                {{ $match->teamA->players->pluck('country')->unique()->join(', ') }}
                            </p>

                        </div>
                        <div class="text-center py-4">
                            <span class="text-sm font-bold text-gray-400 uppercase tracking-widest">VS</span>
                        </div>
                        <div class="text-center md:text-right">
                            <h3 class="text-lg font-semibold text-blue-500 mb-1">
                                {{ $match->teamB->display_name }}
                            </h3>
                            @if ($match->round->event->type == 'team')

                                <p class="text-xl font-bold">
                                    {{ $match->teamB->players->pluck('first_name')->join(' / ') }}
                                </p>
                            @endif

                            <p class="text-sm text-gray-500">
                                {{ $match->teamB->players->pluck('country')->unique()->join(', ') }}
                            </p>

                        </div>
                    </div>
                    <div class="p-8 pr-2 pt-0 -mt-3 w-full flex justify-end items-baseline">
                        <div class="">
                            <flux:button variant="primary" size="sm" color="blue" icon="arrow-down-tray">
                                Download Match Report
                            </flux:button>
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-xl border-2 border-green-500 shadow-sm p-8">
                    <div class="text-center mb-6">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 mb-2">Final Result</h3>
                        <div class="flex justify-center items-center gap-6">
                            <span class="text-5xl font-bold text-green-600">
                                {{ $teamAWins }}
                            </span>
                            —
                            <span class="text-5xl font-bold text-gray-900">
                                {{ $teamBWins }}
                            </span>
                        </div>
                        <p class="mt-4 text-lg font-semibold">
                            Winner:
                            <span class="text-green-600">
                                {{ $winnerTeamId === $match->team_a_id ? $match->teamA->display_name : $match->teamB->display_name }}
                            </span>
                        </p>

                    </div>
                    <div class="max-w-md mx-auto space-y-3">
                        @foreach ($completedSets as $set)
                                            @php
                                                $a = $this->setScore($set, $match->team_a_id);
                                                $b = $this->setScore($set, $match->team_b_id);
                                                $winner = $set->winner_team_id === $match->team_a_id ? 'A' : 'B';
                                            @endphp

                                            <div
                                                class="flex justify-between items-center p-3
                                                                                                                                                                                                                                                        {{ $winner === 'A'
                            ? 'bg-green-50 border-green-100'
                            : 'bg-gray-50 border-gray-100' }}
                                                                                                                                                                                                                                                        rounded-lg border">

                                                <span class="text-sm font-bold">
                                                    Set {{ $set->set_number }}
                                                </span>

                                                <div class="font-medium text-gray-900">
                                                    {{ $match->teamA->display_name }}
                                                    <span class="font-bold">{{ $a }}</span>
                                                    –
                                                    <span class="font-bold">{{ $b }}</span>
                                                    {{ $match->teamB->display_name }}
                                                </div>

                                                <span class="text-xs font-bold uppercase">
                                                    Winner {{ $winner }}
                                                </span>
                                            </div>
                        @endforeach

                    </div>
                </section>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <h3 class="text-lg font-bold">Match Timeline</h3>
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 relative">
                            <div class="absolute left-9 top-10 bottom-10 w-px bg-gray-200"></div>
                            <div class="space-y-8 relative">
                                @foreach ($this->timelineEvents() as $event)

                                                                <div class="flex gap-6 items-start">
                                                                    <div
                                                                        class="w-6 h-6 rounded-full
                                                                                                                                                                                                                                                                                                                                                                        {{ match ($event->type) {
                                        'match_started' => 'bg-blue-600 ring-4 ring-blue-50',
                                        'match_ended' => 'bg-green-600 ring-4 ring-green-50',
                                        'yellow_card' => 'bg-yellow-400',
                                        'red_card' => 'bg-red-600',
                                        default => 'bg-gray-300'
                                    } }}
                                                                                                                                                                                                                                                                                                                                                                        z-10">
                                                                    </div>

                                                                    <div>
                                                                        <p class="text-sm font-bold">
                                                                            {{ $event->occurred_at->format('H:i') }}
                                                                        </p>

                                                                        <p class="text-sm text-gray-900">
                                                                            {{ ucfirst(str_replace('_', ' ', $event->type)) }}
                                                                            @if($event->team)
                                                                                — {{ $event->team->display_name }}
                                                                            @endif
                                                                            @if($event->player)
                                                                                ({{ $event->player->first_name }})
                                                                            @endif
                                                                        </p>

                                                                        @if($event->team_a_points !== null)
                                                                            <p class="text-xs text-gray-500">
                                                                                Score: {{ $event->team_a_points }}–{{ $event->team_b_points }}
                                                                            </p>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                @endforeach

                            </div>
                        </div>

                    </div>

                    <div class="space-y-8">
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Metadata</h3>
                            <dl class="space-y-4">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Duration</dt>
                                    <dd class="text-sm font-medium">
                                        {{ $this->matchDuration() }}
                                    </dd>

                                </div>
                                <div class="flex justify-between border-t border-gray-50 pt-2">
                                    <dt class="text-sm text-gray-500">Shuttles Used</dt>
                                    <dd class="text-sm font-medium">
                                        {{ $match->shuttlecock_used_count }}
                                    </dd>

                                </div>
                                <div class="flex justify-between border-t border-gray-50 pt-2">
                                    <dt class="text-sm text-gray-500">Scheduled</dt>
                                    <dd class="text-sm font-medium">
                                        {{ optional($match->scheduled_at)->format('H:i') ?? '—' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between border-t border-gray-50 pt-2">
                                    <dt class="text-sm text-gray-500">Actual Start</dt>
                                    <dd class="text-sm font-medium">
                                        {{ optional($match->started_at)->format('H:i') ?? '—' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Rules & Format
                            </h3>
                            <div class="space-y-2">
                                <p class="text-sm font-medium">Best of 3 Sets</p>
                                <p class="text-sm text-gray-600">Standard BWF scoring: 21 points per set, rally point
                                    system. Sudden death at 29-29.</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl border border-red-100 shadow-sm p-6">
                            <h3 class="text-sm font-bold text-red-600 uppercase tracking-wider mb-4">Incidents</h3>
                            <div class="space-y-4">
                                @php $incidents = $this->incidents(); @endphp

                                @if ($incidents->isNotEmpty())
                                    @foreach ($incidents as $incident)
                                        <div class="p-3 bg-yellow-50 rounded-lg flex items-start gap-3">
                                            <div class="w-2 h-6 bg-yellow-400 rounded-sm"></div>
                                            <div>
                                                <p class="text-xs font-bold uppercase">
                                                    {{ str_replace('_', ' ', $incident->type) }}
                                                </p>
                                                <p class="text-sm font-medium">
                                                    {{ $incident->player?->first_name ?? 'Team Level' }}
                                                    (Set {{ $incident->set?->set_number }})
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-xs text-gray-400 italic text-center">
                                        No incidents recorded
                                    </p>
                                @endif


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

    </section>



</div>