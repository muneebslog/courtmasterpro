<?php

use Livewire\Component;
use App\Models\MatchGame;
use App\Models\Set;
use App\Models\SetScore;
use App\Models\MatchEvent;
use App\Services\MatchService;

new class extends Component {
    public $match;
    public Set $set;

    public int $scoreA = 0;
    public int $scoreB = 0;

    public string $umpire_name = '';
    public string $referee_name = '';

    public bool $showStartModal = false;
    public string $actionCategory = 'card'; // card | injury | walkover | timeout
    public ?string $cardType = null;        // yellow_card | red_card
    public ?int $actionTeamId = null;
    public ?int $actionPlayerId = null;
    public $selectedTeamPlayers = [];

    public $completedSets = [];


    public $showplayerSelect = false;

    public bool $showSetWinnerModal = false;
    public bool $showMatchWinnerScreen = false;

    public ?int $lastSetWinnerTeamId = null;
    public ?int $matchWinnerTeamId = null;

    public int $teamAWins = 0;
    public int $teamBWins = 0;



    public function mount(int $match)
    {
        $this->match = MatchGame::with(['teamA.players', 'teamB.players'])->findOrFail($match);

        // ✅ CASE 1: MATCH COMPLETED → show winner screen
        if ($this->match->status === 'completed') {

            $this->match->load([
                'sets.scores',
                'teamA',
                'teamB',
            ]);

            $this->completedSets = $this->match->sets
                ->sortBy('set_number');

            $this->matchWinnerTeamId = $this->match->winner_team_id;
            $this->showMatchWinnerScreen = true;

            return;
        }

        // ✅ CASE 2: MATCH NOT STARTED → show start modal
        if ($this->match->status === 'scheduled') {
            $this->showStartModal = true;
        }

        // ✅ CASE 3: MATCH LIVE → load current set
        $this->set = $this->match->sets()
            ->where('status', 'live')
            ->firstOrCreate(
                ['set_number' => 1],
                ['status' => 'live']
            );

        $this->scoreA = $this->set->scores()
            ->where('team_id', $this->match->team_a_id)
            ->value('points') ?? 0;

        $this->scoreB = $this->set->scores()
            ->where('team_id', $this->match->team_b_id)
            ->value('points') ?? 0;
    }

    public function getSetScore($set, int $teamId): int
    {
        return $set->scores
            ->firstWhere('team_id', $teamId)
                ?->points ?? 0;
    }


    protected function resolveActionType(): string
    {
        return match ($this->actionCategory) {
            'card' => $this->cardType ?? 'yellow_card',
            'injury' => 'injury',
            'walkover' => 'walkover',
            'timeout' => 'timeout',
            default => 'injury',
        };
    }

    public function updatedActionTeamId()
    {
        if (!$this->actionTeamId) {
            $this->actionPlayerId = null;
            $this->selectedTeamPlayers = [];
            return;
        }
        //check if the acttion team id is team a id or team b id

        if ($this->actionTeamId == $this->match->team_a_id) {
            $this->actionPlayerId = null;
            $this->selectedTeamPlayers = $this->match->teamA->players;
            $this->showplayerSelect = true;
        } elseif ($this->actionTeamId == $this->match->team_b_id) {
            $this->actionPlayerId = null;
            $this->selectedTeamPlayers = $this->match->teamB->players;
            $this->showplayerSelect = true;
        } else {
            $this->selectedTeamPlayers = [];
            $this->showplayerSelect = false;
            $this->actionPlayerId = null;
            return;
        }
    }


    public function submitMatchAction(): void
    {
        if ($this->actionCategory === 'card' && !$this->cardType) {
            $this->addError('cardType', 'Select card type.');
            return;
        }

        if ($this->actionCategory === 'walkover') {

            if (!$this->actionTeamId) {
                $this->addError('actionTeamId', 'Select a team for walkover.');
                return;
            }

            $result = app(MatchService::class)
                ->handleWalkover($this->match, $this->set, $this->actionTeamId);

            $this->matchWinnerTeamId = $result['match_winner_team_id'];
            $this->showMatchWinnerScreen = true;

            return;
        }


        // Reset only what makes sense
        $this->cardType = null;
        $this->actionPlayerId = null;
    }


    public function startMatch()
    {
        $this->validate([
            'umpire_name' => 'required|string|max:255',
            'referee_name' => 'nullable|string|max:255',
        ]);
        $this->match->update([
            'started_at' => now(),
            'status' => 'live',
            'umpire' => $this->umpire_name,
            'referee' => $this->referee_name,
        ]);



        $this->log('match_started', null, 'Match started');
        $this->showStartModal = false;
    }

    public function increment(string $team)
    {
        $this->updateScore($team, +1);

    }

    public function decrement(string $team)
    {
        $currentScore = $team === 'A' ? $this->scoreA : $this->scoreB;
        if ($currentScore <= 0) {
            return;
        } else {
            $this->updateScore($team, -1);
        }

    }

    protected function updateScore(string $team, int $delta)
    {
        $teamId = $team === 'A' ? $this->match->team_a_id : $this->match->team_b_id;

        $score = SetScore::firstOrCreate([
            'set_id' => $this->set->id,
            'team_id' => $teamId,
        ]);

        $score->increment('points', $delta);

        $this->scoreA = $this->set->scores()->where('team_id', $this->match->team_a_id)->value('points') ?? 0;

        $this->scoreB = $this->set->scores()->where('team_id', $this->match->team_b_id)->value('points') ?? 0;


        $result = app(MatchService::class)
            ->afterPointScored($this->match, $this->set);
        if ($result['type'] === 'set_completed') {

            $this->lastSetWinnerTeamId = $result['set_winner_team_id'];

            // update scoreboard
            $this->teamAWins = $this->match->setWinsForTeam($this->match->team_a_id);
            $this->teamBWins = $this->match->setWinsForTeam($this->match->team_b_id);

            // pause the game
            $this->showSetWinnerModal = true;
        }

        if ($result['type'] === 'match_completed') {

            // Reload fresh relations
            $this->match->load([
                'sets.scores',
                'teamA',
                'teamB',
            ]);

            $this->completedSets = $this->match->sets
                ->sortBy('set_number');

            $this->matchWinnerTeamId = $result['match_winner_team_id'];
            $this->showMatchWinnerScreen = true;
        }



        $this->log('point_scored', $teamId, 'Point scored');
    }

    public function scoringLocked(): bool
    {
        return $this->showSetWinnerModal
            || $this->showMatchWinnerScreen
            || $this->match->status === 'completed';
    }

    protected function log(string $type, ?int $teamId, string $desc, ?int $playerId = null): void
    {
        MatchEvent::create([
            'match_id' => $this->match->id,
            'set_id' => $this->set->id,
            'team_id' => $teamId,
            'type' => $type,
            'description' => $desc,
            'team_a_points' => $this->scoreA,
            'team_b_points' => $this->scoreB,
            'occurred_at' => now(),
            'player_id' => $playerId,
        ]);
    }


    public function getEventLogProperty()
    {
        return MatchEvent::where('match_id', $this->match->id)
            ->latest('occurred_at')
            ->limit(20)
            ->get();
    }

    public function confirmStartNextSet()
    {
        $maxSets = $this->match->round->event->best_of_sets;
        if ($this->set->set_number >= $maxSets) {
            return; // or throw an exception
        }

        $this->set = $this->match->sets()->create([
            'set_number' => $this->set->set_number + 1,
            'discipline' => $this->set->discipline,
            'status' => 'live',
        ]);

        // 👇 RESET LIVEWIRE SCORE STATE
        $this->scoreA = 0;
        $this->scoreB = 0;

        $this->log('set_started', null, 'Set started');

        $this->showSetWinnerModal = false;
    }


};
?>

<div class="">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFC;
            color: #111827;
        }
    </style>

    <section>

        <flux:modal wire:model.self="showStartModal" name="startMatchModal" class=" backdrop-blur-sm">
            <form wire:submit.prevent="startMatch" class="space-y-6">
                <div>
                    <flux:heading size="lg">Start Match</flux:heading>
                    <flux:text class="mt-2">Verify match details before initiating live scoring.</flux:text>
                </div>
                <flux:input label="Umpire Name" wire:model="umpire_name" placeholder="Umpire Name" />
                <flux:input label="Referee Name" wire:model="referee_name" placeholder="Referee Name" />
                <div class="flex">
                    <flux:spacer />
                    <flux:button type="submit" variant="primary">Save changes</flux:button>
                </div>
            </form>
        </flux:modal>

        <header class="sticky top-0 z-40 bg-white border-b border-slate-200 px-6 py-4 shadow-sm">
            <div class="max-w-7xl mx-auto flex items-center justify-between">

                {{-- LEFT: MATCH META --}}
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">
                        Match #{{ $match->id }}
                        • {{ $match->round->name }}
                    </span>

                    <span class="text-sm font-medium text-slate-500">
                        Set {{ $set?->set_number }} of {{ $match->round->event->best_of_sets }}
                    </span>
                </div>

                {{-- CENTER: TEAMS --}}
                <div class="text-xl font-bold tracking-tight text-center">
                    {{ $match->teamA->display_name }}
                    <span class="text-slate-400 px-2">VS</span>
                    {{ $match->teamB->display_name }}
                </div>

                {{-- RIGHT: STATUS --}}
                <div>
                    @if ($match->status === 'live')
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                                                                                                                                                                                                bg-green-100 text-green-700 border border-green-200">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                            LIVE
                        </span>
                    @elseif ($match->status === 'completed')
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                                                                                                                                                                                                bg-slate-100 text-slate-700 border border-slate-200">
                            COMPLETED
                        </span>
                    @else
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                                                                                                                                                                                                bg-yellow-100 text-yellow-700 border border-yellow-200">
                            NOT STARTED
                        </span>
                    @endif
                </div>

            </div>
        </header>


        <main class="max-w-7xl mx-auto p-6  gap-6">

            <section class="col-span-12 lg:col-span-8 space-y-6">

                <div class="grid grid-cols-2 gap-6">

                    {{-- TEAM A --}}
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 flex flex-col items-center">
                        <h3 class="text-lg font-semibold text-slate-500 mb-1">
                            {{ $match->teamA->name }}
                        </h3>

                        <p class="text-sm text-slate-400 mb-6">
                            {{ $match->teamA->players->pluck('first_name')->join(' / ') }}
                        </p>

                        <div class="text-9xl font-bold text-slate-900 mb-8">
                            {{ $scoreA }}
                        </div>

                        <div class="flex gap-4 w-full">
                            <div class="
                            w-full">

                                <flux:button :disabled="$this->scoringLocked()" variant="primary" color="red"
                                    class=" w-full" wire:click="decrement('A')">
                                    <span class="text-2xl font-bold">
                                        −</span>
                                </flux:button>

                            </div>
                            <div class="w-full">

                                <flux:button :disabled="$this->scoringLocked()" variant="primary" color="green"
                                    class="w-full" wire:click="increment('A')">
                                    <span class="text-3xl font-bold">
                                        +</span>
                                </flux:button>
                            </div>

                        </div>
                    </div>

                    {{-- TEAM B --}}
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 flex flex-col items-center">
                        <h3 class="text-lg font-semibold text-slate-500 mb-1">
                            {{ $match->teamB->name }}
                        </h3>

                        <p class="text-sm text-slate-400 mb-6">
                            {{ $match->teamB->players->pluck('first_name')->join(' / ') }}
                        </p>

                        <div class="text-9xl font-bold text-slate-900 mb-8">
                            {{ $scoreB }}
                        </div>

                        <div class="flex gap-4 w-full">
                            <flux:button :disabled="$this->scoringLocked()" variant="primary" color="red"
                                wire:click="decrement('B')" class=" w-full">
                                <span class="text-2xl font-bold">
                                    −
                                </span>
                            </flux:button>

                            <flux:button :disabled="$this->scoringLocked()" variant="primary" color="green"
                                wire:click="increment('B')" class=" w-full">
                                <span class="text-3xl font-bold">
                                    +
                                </span>
                            </flux:button>
                        </div>
                    </div>
                </div>



            </section>



            <div class="grid grid-cols-2 mt-4 gap-6">

                {{-- MATCH ACTIONS --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h4 class="text-sm font-bold text-slate-400 uppercase mb-4">
                        Match Actions
                    </h4>

                    <form wire:submit.prevent="submitMatchAction" class="space-y-6">

                        {{-- STEP 1: CATEGORY --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-2">
                                What happened?
                            </label>

                            <flux:radio.group wire:model.live="actionCategory" variant="segmented">
                                <flux:radio label="Card" value="card" icon="exclamation-circle" />
                                <flux:radio label="Injury" value="injury" icon="heart" />
                                <flux:radio label="Walkover" value="walkover" icon="flag" />
                            </flux:radio.group>
                        </div>

                        {{-- STEP 2: CARD TYPE --}}
                        @if ($actionCategory === 'card')
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-2">
                                    Card Type
                                </label>

                                <flux:radio.group wire:model.live="cardType" variant="segmented">
                                    <flux:radio label="Yellow Card" value="yellow_card" icon="exclamation-triangle" />
                                    <flux:radio label="Red Card" value="red_card" icon="x-circle" />
                                </flux:radio.group>
                            </div>
                        @endif

                        {{-- STEP 3: TEAM --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-2">
                                Team
                            </label>

                            <flux:radio.group wire:model.live="actionTeamId" variant="segmented">
                                <flux:radio label="{{ $match->teamA->display_name }}" value="{{ $match->teamA->id }}" />
                                <flux:radio label="{{ $match->teamB->display_name }}" value="{{ $match->teamB->id }}" />
                            </flux:radio.group>
                        </div>

                        {{-- STEP 4: PLAYER (OPTIONAL) --}}
                        <div>
                            @if ($actionCategory != 'walkover' && $this->showplayerSelect)
                                <div>

                                    <flux:select label='Player (optional)' wire:model="actionPlayerId"
                                        class="w-full rounded-lg border-slate-300 text-sm
                                                                                                                            focus:ring-blue-500 focus:border-blue-500
                                                                                                                            disabled:bg-slate-100" :disabled="!$actionTeamId">
                                        <flux:select.option value="">
                                            — Team Level Action —
                                        </flux:select.option>

                                        @foreach ($this->selectedTeamPlayers as $player)
                                            <flux:select.option value="{{ $player->id }}">
                                                {{ $player->first_name }} {{ $player->last_name }}
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>

                                    @if (!$actionTeamId)
                                        <p class="text-xs text-slate-400 mt-1">
                                            Select a team to choose a player
                                        </p>
                                    @endif
                                </div>
                            @endif

                        </div>

                        {{-- SUBMIT --}}
                        <div class="pt-2">
                            <flux:button type="submit" variant="primary" class="w-full">
                                Log Action
                            </flux:button>
                        </div>

                    </form>
                </div>


                <div class="">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 h-full flex flex-col">
                        <div class="p-6 border-b border-slate-100">
                            <h3 class="font-bold text-slate-900">Event Log</h3>
                        </div>


                        @php
                            $eventUi = [
                                'match_started' => [
                                    'bg' => 'bg-blue-50/60',
                                    'dot' => 'bg-blue-500',
                                    'label' => 'Match Started',
                                ],
                                'match_ended' => [
                                    'bg' => 'bg-slate-100',
                                    'dot' => 'bg-slate-600',
                                    'label' => 'Match Ended',
                                ],
                                'set_started' => [
                                    'bg' => 'bg-indigo-50/60',
                                    'dot' => 'bg-indigo-500',
                                    'label' => 'Set Started',
                                ],
                                'set_ended' => [
                                    'bg' => 'bg-indigo-100/60',
                                    'dot' => 'bg-indigo-700',
                                    'label' => 'Set Ended',
                                ],
                                'point_scored' => [
                                    'bg' => 'bg-green-50/60',
                                    'dot' => 'bg-green-500',
                                    'label' => 'Point Scored',
                                ],
                                'yellow_card' => [
                                    'bg' => 'bg-yellow-50/60',
                                    'dot' => 'bg-yellow-500',
                                    'label' => 'Yellow Card',
                                ],
                                'red_card' => [
                                    'bg' => 'bg-red-50/60',
                                    'dot' => 'bg-red-600',
                                    'label' => 'Red Card',
                                ],
                                'injury' => [
                                    'bg' => 'bg-orange-50/60',
                                    'dot' => 'bg-orange-500',
                                    'label' => 'Injury',
                                ],
                                'timeout' => [
                                    'bg' => 'bg-slate-50',
                                    'dot' => 'bg-slate-400',
                                    'label' => 'Timeout',
                                ],
                                'walkover' => [
                                    'bg' => 'bg-red-100',
                                    'dot' => 'bg-red-800',
                                    'label' => 'Walkover',
                                ],
                            ];
                        @endphp

                        <div class="p-0 flex-1 max-h-96 overflow-y-scroll">
                            <ul class="divide-y divide-slate-50">

                                @foreach ($this->eventLog as $event)
                                    @php
                                        $ui = $eventUi[$event->type] ?? [
                                            'bg' => 'bg-slate-50',
                                            'dot' => 'bg-slate-400',
                                            'label' => ucfirst(str_replace('_', ' ', $event->type)),
                                        ];
                                    @endphp


                                    <li class="p-4 flex items-start gap-4 {{ $ui['bg'] }}">
                                        <div class="w-2 h-2 mt-2 {{ $ui['dot'] }} rounded-full"></div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-0.5">
                                                <p class="text-sm font-semibold text-slate-800">
                                                    {{ $ui['label'] }}
                                                    @if($event->team)
                                                        – {{ $event->team->display_name }}
                                                    @endif
                                                    @if($event->player)
                                                        ({{ $event->player->first_name }} {{ $event->player->last_name }})
                                                    @endif
                                                </p>
                                                <span
                                                    class="text-[10px] font-bold bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded uppercase tracking-wider">{{ $event->team_a_points }}-{{ $event->team_b_points }}</span>
                                            </div>
                                            <p class="text-xs text-slate-400">{{ $event->occurred_at->diffForHumans() }}</p>
                                        </div>
                                    </li>

                                @endforeach
                            </ul>
                        </div>
                        {{-- <div class="p-4 bg-slate-50 mt-auto rounded-b-xl">
                            <button
                                class="w-full py-3 bg-white border border-slate-200 text-slate-600 font-semibold rounded-lg text-sm hover:bg-white/50 transition">View
                                Full History</button>
                        </div> --}}
                    </div>
                </div>
            </div>
        </main>
    </section>
    <flux:modal :dismissible="false" wire:model.self="showSetWinnerModal">
        <div class="space-y-4 text-center">

            <h2 class="text-xl font-bold">
                Set Completed
            </h2>

            <p>
                Winner:
                <strong>
                    {{ $lastSetWinnerTeamId === $match->team_a_id
    ? $match->teamA->display_name
    : $match->teamB->display_name }}
                </strong>
            </p>

            <p class="text-sm text-slate-500">
                Sets: {{ $teamAWins }} – {{ $teamBWins }}
            </p>

            <flux:button variant="primary" wire:click="confirmStartNextSet" class="w-full">
                Start Next Set
            </flux:button>

        </div>
    </flux:modal>
    @if ($showMatchWinnerScreen)
        <div class="fixed inset-0 bg-white z-50 flex items-center justify-center">
            <div class="max-w-md w-full space-y-6 text-center">

                {{-- Title --}}
                <h1 class="text-4xl font-bold">
                    Match Completed
                </h1>

                {{-- Winner --}}
                <p class="text-xl">
                    👑 Winner:
                    <strong>
                        {{ $matchWinnerTeamId === $match->team_a_id
            ? $match->teamA->display_name
            : $match->teamB->display_name }}
                    </strong>
                </p>

                {{-- Set summary --}}
                <div class="bg-slate-50 rounded-lg p-4 space-y-2">
                    <h3 class="font-semibold text-slate-700">
                        Set Scores
                    </h3>
                    @if (!empty($completedSets))



                        @foreach ($completedSets as $set)
                            <div class="flex justify-between text-sm">
                                <span>
                                    Set {{ $set->set_number }}
                                </span>

                                <span class="font-mono font-semibold">
                                    {{ $this->getSetScore($set, $match->team_a_id) }}
                                    –
                                    {{ $this->getSetScore($set, $match->team_b_id) }}
                                </span>
                            </div>
                        @endforeach
                    @endif

                </div>

                {{-- Final sets won --}}
                <p class="text-sm text-slate-500">
                    Final Result:
                    {{ $match->setWinsForTeam($match->team_a_id) }}
                    –
                    {{ $match->setWinsForTeam($match->team_b_id) }}
                </p>

                {{-- Action --}}
                <flux:button variant="primary" href="{{ route('events.show', $match->round->event->id) }}" class="w-full">
                    Go to Dashboard
                </flux:button>
                {{-- Action --}}
                <flux:button href="{{ route('matches.show', $match->id) }}" class="w-full">
                    See Event Details
                </flux:button>

            </div>
        </div>
    @endif


</div>