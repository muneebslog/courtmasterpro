<?php

use Flux\Flux;
use Livewire\Component;
use App\Models\Event;
use App\Models\Round;
use App\Models\MatchGame;
use Illuminate\Support\Facades\DB;
use App\Models\Team;



new class extends Component {
    public Event $event;
    public $event_id;
    public $editingMatchId;
    public $firstRoundMatches = 0;
    public $roundPreview = [];
    public $renderKey = 0;

    public $selectedMatchId = null;
    public $teamAId = null;
    public $teamBId = null;
    public $isBye = false;

    public bool $showEditEventModal = false;
    public bool $showDeleteEventModal = false;

    // edit form
    public string $edit_name = '';
    public string $edit_type = '';
    public string $edit_default_discipline = '';
    public int $edit_best_of_sets;
    public string $edit_status = '';

    public function canEditEvent(): bool
    {
        return $this->event->rounds->isEmpty();
    }

    public function canDeleteEvent(): bool
    {
        return $this->event->status === 'upcoming'
            && $this->event->rounds->count() === 0;
    }

    public function openEditEvent()
    {
        abort_unless($this->canManage(), 403);

        if (!$this->canEditEvent()) {
            return;
        }

        $this->edit_name = $this->event->name;
        $this->edit_type = $this->event->type;
        $this->edit_default_discipline = $this->event->default_discipline;
        $this->edit_best_of_sets = $this->event->best_of_sets;
        $this->edit_status = $this->event->status;

        $this->showEditEventModal = true;
    }

    public function canManage(): bool
    {
        $user = auth()->user();

        return $user->isProjectOwner() || $user->hasRole('admin');
    }

    public function isUmpire(): bool
    {
        $user = auth()->user();

        return $user->isProjectOwner() || $user->hasRole('umpire') || $user->hasRole('admin');
    }

    public function updateEvent()
    {
        abort_unless($this->canManage(), 403);
        if (!$this->canEditEvent()) {
            return;
        }

        $this->validate([
            'edit_name' => 'required|string|max:255',
            'edit_type' => 'required|in:individual,team',
            'edit_default_discipline' => 'required|in:singles,doubles,mixed',
            'edit_best_of_sets' => 'required|in:1,3,5',
            'edit_status' => 'required|in:draft,live,completed',
        ]);

        $this->event->update([
            'name' => $this->edit_name,
            'type' => $this->edit_type,
            'default_discipline' => $this->edit_default_discipline,
            'best_of_sets' => $this->edit_best_of_sets,
            'status' => $this->edit_status,
        ]);

        $this->loadEvent();
        $this->showEditEventModal = false;
    }

    public function deleteEvent()
    {
        abort_unless($this->canManage(), 403);

        if (!$this->canDeleteEvent()) {
            return;
        }

        $tournamentId = $this->event->tournament_id;

        $this->event->delete();

        return redirect()->route('tournaments.show', $tournamentId);
    }





    public function mount(Event $event)
    {
        $this->event_id = $event->id;
        $this->loadEvent();
    }


    public function getStatsProperty()
    {
        $matches = $this->event->rounds->flatMap->matches;

        return [
            'players' => $this->event->teams
                ->flatMap->players
                ->unique('id')
                ->count(),

            'matches' => $matches->count(),
            'teams' => $this->event->teams->count(),

            'completed' => $matches->where('status', 'completed')->count(),

            'live' => $matches->where('status', 'live')->count(),
        ];
    }

    public function getAssignableTeamsProperty()
    {
        $teams = $this->event->teams;

        if (!$this->editingMatchId) {
            return $teams->where('is_assigned', false);
        }

        return $teams->filter(
            fn($team) =>
            !$team->is_assigned
            || in_array($team->id, [$this->teamAId, $this->teamBId])
        );
    }



    public function updatedFirstRoundMatches()
    {
        $this->roundPreview = [];

        if ($this->firstRoundMatches < 1) {
            return;
        }

        $matches = $this->firstRoundMatches;
        $roundIndex = 0;

        while ($matches >= 1) {
            $this->roundPreview[] = [
                'name' => $this->roundName($matches),
                'matches' => $matches,
                'order_no' => $roundIndex + 1,
            ];

            if ($matches === 1)
                break;

            $matches = intdiv($matches, 2);
            $roundIndex++;
        }
    }

    protected function roundName(int $matches): string
    {
        return match ($matches) {
            32 => 'R32',
            16 => 'R16',
            8 => 'R8',
            4 => 'Quarter Finals',
            2 => 'Semi Finals',
            1 => 'Final',
            default => 'Round of ' . ($matches),
        };
    }
    public function generateMatches()
    {
        abort_unless($this->canManage(), 403);

        if (empty($this->roundPreview)) {
            return;
        }

        // Safety: prevent regeneration
        if ($this->event->rounds()->exists()) {
            $this->addError('firstRoundMatches', 'Matches have already been generated for this event.');
            return;
        }

        DB::transaction(function () {

            foreach ($this->roundPreview as $roundData) {

                $round = Round::create([
                    'event_id' => $this->event->id,
                    'name' => $roundData['name'],
                    'order_no' => $roundData['order_no'],
                ]);

                for ($i = 1; $i <= $roundData['matches']; $i++) {
                    MatchGame::create([
                        'round_id' => $round->id,
                        'match_no' => $i,
                        'status' => 'scheduled',
                    ]);
                }

            }

        });

        // Only runs if EVERYTHING succeeded
        $this->reset('firstRoundMatches', 'roundPreview');

        Flux::modal('generate-matches')->close();

        // Reload event with new rounds and matches
        $this->loadEvent();

    }

    public function openAssignTeamsModal(int $matchId)
    {
        $match = MatchGame::with(['teamA', 'teamB'])->findOrFail($matchId);

        $this->selectedMatchId = $match->id;
        $this->teamAId = $match->team_a_id;
        $this->teamBId = $match->team_b_id;
        $this->isBye = $match->status === 'bye';

        Flux::modal('assign-teams')->show();
    }

    public function assignTeams()
    {
        abort_unless($this->canManage(), 403);

        $match = MatchGame::findOrFail($this->selectedMatchId);

        DB::transaction(function () use ($match) {

            // 🧹 UNASSIGN previously assigned teams (if editing)
            Team::whereIn('id', array_filter([
                $match->team_a_id,
                $match->team_b_id,
            ]))->update(['is_assigned' => false]);

            if ($this->isBye) {

                $this->validate([
                    'teamAId' => 'required',
                ]);

                $match->update([
                    'team_a_id' => $this->teamAId,
                    'team_b_id' => null,
                    'status' => 'bye',
                ]);

                // ✅ assign only team A
                Team::where('id', $this->teamAId)
                    ->update(['is_assigned' => true]);

            } else {

                $this->validate([
                    'teamAId' => 'required|different:teamBId',
                    'teamBId' => 'required',
                ]);

                $match->update([
                    'team_a_id' => $this->teamAId,
                    'team_b_id' => $this->teamBId,
                    'status' => 'scheduled',
                ]);

                // ✅ assign both teams
                Team::whereIn('id', [
                    $this->teamAId,
                    $this->teamBId,
                ])->update(['is_assigned' => true]);
            }
        });

        // Reset modal state
        $this->reset('selectedMatchId', 'teamAId', 'teamBId', 'isBye');

        Flux::modal('assign-teams')->close();

        $this->loadEvent(); // refresh UI
    }




    protected function loadEvent()
    {
        $this->event = Event::with([
            'tournament',
            'teams.players',
            'rounds.matches.teamA.players',
            'rounds.matches.teamB.players',
            'rounds.matches.sets.scores',
        ])->findOrFail($this->event_id);
        $this->renderKey++;
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

        .custom-shadow {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .bracket-connector {
            position: relative;
        }

        .bracket-connector::after {
            content: '';
            position: absolute;
            right: -1rem;
            top: 50%;
            width: 1rem;
            border-top: 2px solid #E5E7EB;
        }
    </style>

    <section class="flex min-h-screen">



        <main x-data="{ viewTab: 'matches'}" class="flex-1 overflow-y-auto">


            <div class="p-8 max-w-7xl mx-auto">
                <div class="bg-white rounded-xl border border-[#E5E7EB] p-6 custom-shadow mb-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-[28px] font-semibold text-[#111827]">{{ $event->name }}</h1>
                            <p class="text-[#6B7280] mt-1">{{ $event->tournament->name }}</p>
                            <div class="flex gap-2 mt-4">
                                <span class="px-3 py-1 bg-slate-100 text-[#111827] text-xs font-medium rounded-full">
                                    {{ ucfirst($event->default_discipline) }}</span>
                                <span
                                    class="px-3 py-1 bg-slate-100 text-[#111827] text-xs font-medium rounded-full">{{ ucfirst($event->type) }}</span>
                                <span
                                    class="px-3 py-1 bg-slate-100 text-[#111827] text-xs font-medium rounded-full">Best
                                    of {{ $event->best_of_sets }}</span>
                                <span @class([
                                    'px-3 py-1 text-xs font-semibold rounded-full flex items-center gap-1',
                                    'bg-green-100 text-green-700' => $event->status === 'ongoing',
                                    'bg-blue-100 text-blue-700' => $event->status === 'upcoming',
                                    'bg-gray-100 text-gray-600' => $event->status === 'completed',
                                ])>
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {{ strtoupper($event->status) }}
                                </span>
                            </div>
                        </div>
                        <div class=" flex flex-col gap-4">
                            <div class="flex gap-3">
                                @if ($this->canManage())

                                    @if ($this->canEditEvent())
                                        <flux:button variant="outline" wire:click="openEditEvent">
                                            Edit Event
                                        </flux:button>
                                    @endif

                                    @if ($this->canDeleteEvent())
                                        <flux:button variant="danger" wire:click="$set('showDeleteEventModal', true)">
                                            Delete Event
                                        </flux:button>
                                    @endif
                                    <flux:modal.trigger name="generate-matches">
                                        <flux:button variant="primary" color="blue">
                                            Generate Matches
                                        </flux:button>
                                    </flux:modal.trigger>
                                @endif

                            </div>

                            <flux:button x-cloak variant="primary"
                                @click="viewTab = viewTab === 'matches' ? 'teams' : 'matches'">
                                <span
                                    x-text="viewTab === 'matches' ? 'Manage Teams n Players' : 'Manage Matches'"></span>
                            </flux:button>



                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-4 mb-8">
                    <div class="bg-white p-4 rounded-xl border border-[#E5E7EB] custom-shadow">
                        <p class="text-xs text-[#6B7280] font-medium uppercase tracking-wider">Total Teams</p>
                        <p class="text-2xl font-semibold mt-1">{{ $this->stats['teams'] }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-[#E5E7EB] custom-shadow">
                        <p class="text-xs text-[#6B7280] font-medium uppercase tracking-wider">Total Matches</p>
                        <p class="text-2xl font-semibold mt-1">{{ $this->stats['matches'] }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-[#E5E7EB] custom-shadow">
                        <p class="text-xs text-[#6B7280] font-medium uppercase tracking-wider">Completed</p>
                        <p class="text-2xl font-semibold mt-1 text-[#16A34A]">{{ $this->stats['completed'] }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-[#E5E7EB] custom-shadow">
                        <p class="text-xs text-[#6B7280] font-medium uppercase tracking-wider">Live Matches</p>
                        <p class="text-2xl font-semibold mt-1 text-[#22C55E]">{{ $this->stats['live'] }}</p>
                    </div>
                </div>


                <div x-show="viewTab === 'matches'" wire:key="event-matches-{{ $renderKey }}"
                    x-data="{ viewMode: 'table', activeRoundId: {{ $event->rounds->first()->id ?? 'null' }} }">

                    <div class="flex justify-between items-end mb-6">
                        <div>
                            <h2 class="text-xl font-semibold text-[#111827]">Matches</h2>
                            <p class="text-sm text-[#6B7280]">Round-wise match overview and progression</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <div x-show="viewMode === 'table'" x-transition
                                class="flex bg-white border border-[#E5E7EB] p-1 rounded-lg">
                                @foreach ($event->rounds as $round)
                                    <button @click="activeRoundId = {{ $round->id }}"
                                        class="px-3 py-1.5 text-xs font-medium rounded-md transition"
                                        :class="activeRoundId === {{ $round->id }}
                                                                                                                                                                                                                                                                        ? 'bg-[#2563EB] text-white'
                                                                                                                                                                                                                                                                        : 'text-[#6B7280] hover:text-[#111827]'">
                                        {{ $round->short_name ?? Str::upper(Str::slug($round->name, '')) }}
                                    </button>
                                @endforeach
                            </div>


                            <button @click="viewMode = viewMode === 'bracket' ? 'table' : 'bracket'"
                                class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium border border-[#E5E7EB] bg-white rounded-lg hover:bg-gray-50">
                                <span
                                    x-text="viewMode === 'bracket' ? 'Switch to Table View' : 'Get Data'"></span>
                            </button>
                        </div>
                    </div>

                    <div x-cloak x-show="viewMode === 'bracket'" class="  max-h-[50vh]  pb-4">
                        <h2 class=" flex justify-center w-full text-gray-500 text-lg font-bold">Ummm , Still Working
                            On
                            it! 😅</h2>

                    </div>

                    <div x-show="viewMode === 'table'"
                        class="bg-white border border-[#E5E7EB] rounded-xl overflow-hidden custom-shadow">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-[#E5E7EB]">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-semibold text-[#6B7280] uppercase tracking-wider">
                                        Match #</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-[#6B7280] uppercase tracking-wider">
                                        Players</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-[#6B7280] uppercase tracking-wider">
                                        Round</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-[#6B7280] uppercase tracking-wider">
                                        Score</th>
                                    <th class="px-6 py-4 text-xs font-semibold text-[#6B7280] uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-[#6B7280] uppercase tracking-wider text-right">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($event->rounds as $round)
                                    @foreach ($round->matches as $match)
                                        <tr x-show="activeRoundId === {{ $round->id }}">

                                            <td class="px-6 py-4 text-sm font-medium">
                                                {{ $round->short_label }} – M{{ $match->match_no }}

                                            </td>

                                            <td class="px-6 py-4 text-sm">
                                                <div class="flex flex-col">
                                                    <span class="font-semibold">
                                                        {{ $match->teamA?->players->pluck('first_name')->join(', ') }}
                                                    </span>
                                                    <span class="text-[#6B7280]">
                                                        {{ $match->teamB?->players->pluck('first_name')->join(', ') }}
                                                    </span>
                                                </div>
                                            </td>

                                            <td class="px-6 py-4 text-sm text-[#6B7280]">
                                                {{ $round->name }}
                                            </td>

                                            <td class="px-6 py-4 text-sm font-medium">
                                                @foreach ($match->sets as $set)
                                                    {{ $set->scores->pluck('points')->join('-') }}
                                                    @if (!$loop->last), @endif
                                                @endforeach
                                            </td>

                                            <td class="px-6 py-4">
                                                <span
                                                    class="px-2 py-1 text-[10px] font-bold rounded
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            {{ $match->status === 'live' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                                    {{ strtoupper($match->status) }}
                                                </span>
                                            </td>

                                            <td class="px-6 py-4 text-right">
                                                @if ($this->isUmpire())

                                                    @if ($match->team_a_id == null && $match->team_b_id == null)
                                                        @if ($this->canManage())

                                                            <flux:button size="xs" wire:click="openAssignTeamsModal({{ $match->id }})">
                                                                Assign Teams
                                                            </flux:button>
                                                        @endif
                                                    @else
                                                                                                            @if ($this->canManage())

                                                        @if ($match->status == 'scheduled')

                                                            <flux:button variant="primary" color="red" size="xs">
                                                                Edit Match
                                                            </flux:button>
                                                        @endif
                                                                                                                    @endif

                                                        @if ($match->status == 'scheduled')

                                                            <flux:button variant="primary" color="red"
                                                                href="{{ route('matches.controlpanel', $match->id) }}" wire:navigate
                                                                size="xs">
                                                                Start Match
                                                            </flux:button>
                                                        @endif

                                                    @endif
                                                @endif
                                                @if ($match->status == 'completed')

                                                    <flux:button href="{{ route('matches.show', ['match' => $match->id]) }}"
                                                        wire:navigate size="xs">
                                                        Match Details
                                                    </flux:button>
                                                @endif

                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>

                        </table>
                    </div>


                </div>
                <div class="" x-show="viewTab === 'teams'">
                    <livewire:pages::participants.manage :event="$event" />
                </div>

            </div>
        </main>
        <flux:modal name="generate-matches" class="md:w-[420px]">
            <form wire:submit.prevent="generateMatches" class="space-y-6">

                <div>
                    <flux:heading size="lg">Generate Matches</flux:heading>
                    <flux:text class="mt-2">
                        Enter how many matches you want in the first round.
                    </flux:text>
                </div>

                <flux:input label="First Round Matches" type="number" min="1" wire:model.live="firstRoundMatches"
                    placeholder="e.g. 16" />

                {{-- LIVE PREVIEW --}}
                @if (!empty($roundPreview))
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 space-y-2">
                        <p class="text-xs font-semibold uppercase text-slate-600">
                            Rounds Preview
                        </p>

                        @foreach ($roundPreview as $round)
                            <div class="flex justify-between text-sm">
                                <span>{{ $round['name'] }}</span>
                                <span class="font-semibold">
                                    {{ $round['matches'] }} matches
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex pt-2">
                    <flux:spacer />

                    <flux:button type="submit" variant="primary" :disabled="empty($roundPreview)">
                        Confirm & Generate
                    </flux:button>
                </div>

            </form>
        </flux:modal>
        <flux:modal name="assign-teams" class="md:w-96">
            <form wire:submit.prevent="assignTeams" class="space-y-6">

                <div>
                    <flux:heading size="lg">
                        Assign Teams
                    </flux:heading>

                    <flux:text class="mt-2">
                        Select teams for this match or mark it as a BYE.
                    </flux:text>
                </div>

                {{-- TEAM A --}}

                <flux:select label="Team A" wire:model="teamAId">
                    <flux:select.option value="">Select team</flux:select.option>
                    @foreach ($this->assignableTeams as $team)
                                    <flux:select.option value="{{ $team->id }}">
                                        {{ $team->name
                        ?? $team->players->pluck('first_name')->join(' & ') }}
                                    </flux:select.option>
                    @endforeach
                </flux:select>


                <flux:field variant="inline">
                    <flux:checkbox wire:model.live="isBye" />

                    <flux:label>This match is a BYE</flux:label>

                    <flux:error name="terms" />
                </flux:field>


                {{-- TEAM B --}}
                <flux:select label="Team B" wire:model="teamBId" :disabled="$isBye" :invalid="$isBye">
                    <flux:select.option value="">Select team</flux:select.option>
                    @foreach ($this->assignableTeams as $team)
                                    <flux:select.option value="{{ $team->id }}">
                                        {{ $team->name
                        ?? $team->players->pluck('first_name')->join(' & ') }}
                                    </flux:select.option>
                    @endforeach
                </flux:select>


                <div class="flex pt-2">
                    <flux:spacer />
                    <flux:button type="submit" variant="primary">
                        Save
                    </flux:button>
                </div>

            </form>
        </flux:modal>
        <flux:modal wire:model.self="showEditEventModal" class="md:w-[420px]">
            <form wire:submit.prevent="updateEvent" class="space-y-6">

                <flux:heading size="lg">Edit Event</flux:heading>

                <flux:input label="Event Name" wire:model.defer="edit_name" />

                <flux:select label="Event Type" wire:model.defer="edit_type">
                    <option value="individual">Individual</option>
                    <option value="team">Team</option>
                </flux:select>

                <flux:select label="Default Discipline" wire:model.defer="edit_default_discipline">
                    <option value="singles">Singles</option>
                    <option value="doubles">Doubles</option>
                    <option value="mixed">Mixed</option>
                </flux:select>

                <flux:select label="Best of Sets" wire:model.defer="edit_best_of_sets">
                    <option value="1">1</option>
                    <option value="3">3</option>
                    <option value="5">5</option>
                </flux:select>

                <flux:select label="Status" wire:model.defer="edit_status">
                    <option value="draft">Draft</option>
                    <option value="live">Live</option>
                    <option value="completed">Completed</option>
                </flux:select>

                <div class="flex pt-2">
                    <flux:spacer />
                    <flux:button type="submit" variant="primary">
                        Save Changes
                    </flux:button>
                </div>

            </form>
        </flux:modal>

        <flux:modal wire:model.self="showDeleteEventModal" class="md:w-[420px]">
            <div class="space-y-6">

                <flux:heading size="lg" class="text-red-600">
                    Delete Event
                </flux:heading>

                <p class="text-sm text-gray-600">
                    This action cannot be undone.
                </p>

                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
                    Event: <strong>{{ $event->name }}</strong>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:button variant="outline" wire:click="$set('showDeleteEventModal', false)">
                        Cancel
                    </flux:button>

                    <flux:button variant="danger" wire:click="deleteEvent">
                        Yes, Delete
                    </flux:button>
                </div>

            </div>
        </flux:modal>




    </section>
</div>