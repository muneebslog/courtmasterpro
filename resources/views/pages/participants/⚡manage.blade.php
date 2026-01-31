<?php

use Livewire\Component;
use App\Models\Event;
use App\Models\Team;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Flux\Flux;

new class extends Component {

    public Event $event;

    // shared modal state
    public ?int $editingTeamId = null;
    public ?string $teamName = null;

    public array $players = [];

    /* ------------------------------
        Computed helpers
    ------------------------------ */

    public function getTeamsProperty()
    {
        return $this->event
            ->teams()
            ->with('players')
            ->get();
    }

    public function getIsIndividualProperty(): bool
    {
        return $this->event->type === 'individual';
    }

    public function getIsSinglesProperty(): bool
    {
        return $this->event->default_discipline === 'singles';
    }

    public function getIsDoublesProperty(): bool
    {
        return $this->event->default_discipline === 'doubles';
    }

    public function getIsTeamMixedProperty(): bool
    {
        return $this->event->type === 'team'
            && $this->event->default_discipline === 'mixed';
    }

    /* ------------------------------
        Open Modals
    ------------------------------ */

    public function openCreateTeam()
    {
        $this->editingTeamId = null;
        $this->teamName = null;

        // players count rules
        if ($this->isSingles) {
            $this->players = [['first_name' => '', 'last_name' => '']];
        } elseif ($this->isDoubles) {
            $this->players = [
                ['first_name' => '', 'last_name' => ''],
                ['first_name' => '', 'last_name' => ''],
            ];
        } else {
            $this->players = [['first_name' => '', 'last_name' => '']];
        }

        Flux::modal('manage-team')->show();
    }

    public function openEditTeam(int $teamId)
    {
        $team = Team::with('players')->findOrFail($teamId);

        $this->editingTeamId = $team->id;
        $this->teamName = $team->name;

        $this->players = $team->players
            ->map(fn ($p) => [
                'first_name' => $p->first_name,
                'last_name' => $p->last_name,
                'tag' => $p->country,
                'id' => $p->id,
            ])
            ->toArray();

        Flux::modal('manage-team')->show();
    }

    /* ------------------------------
        Player row helpers
    ------------------------------ */

    public function addPlayer()
    {
        $this->players[] = ['first_name' => '', 'tag' => ''];
    }

    public function removePlayer(int $index)
    {
        if (count($this->players) > 1) {
            unset($this->players[$index]);
            $this->players = array_values($this->players);
        }
    }

    /* ------------------------------
        Save Team
    ------------------------------ */

    public function saveTeam()
    {
        $rules = [
            'players.*.first_name' => 'required|string|max:100',
            'players.*.last_name' => 'nullable|string|max:100',
        ];

        if ($this->isTeamMixed) {
            $rules['teamName'] = 'required|string|max:255';
        }

        $this->validate($rules);

        DB::transaction(function () {

            $team = $this->editingTeamId
                ? Team::findOrFail($this->editingTeamId)
                : Team::create([
                    'event_id' => $this->event->id,
                    'name' => $this->isTeamMixed ? $this->teamName : null,
                ]);

            // clear old players on edit
            if ($this->editingTeamId) {
                $team->players()->detach();
            }

            foreach ($this->players as $playerData) {
                $player = Player::create([
                    'first_name' => $playerData['first_name'],
                    'last_name' => $playerData['last_name'] ?? null,
                    'country' => $playerData['tag'] ?? null,
                ]);

                $team->players()->attach($player->id);
            }
        });

        Flux::modal('manage-team')->close();

        $this->event->load('teams.players');
    }

};
?>

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold">Participants</h2>
            <p class="text-sm text-slate-600">Manage teams and players</p>
        </div>

        <flux:button variant="primary" wire:click="openCreateTeam">
            + Create Team
        </flux:button>
    </div>

    {{-- TABLE --}}
    <div class="bg-white border rounded-xl overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-xs uppercase">#</th>
                    <th class="px-6 py-3 text-xs uppercase">Team</th>
                    <th class="px-6 py-3 text-xs uppercase">Players</th>
                    <th class="px-6 py-3 text-xs uppercase">Tag</th>
                    <th class="px-6 py-3 text-xs uppercase">Assigned</th>
                    <th class="px-6 py-3 text-xs uppercase text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse ($this->teams as $i => $team)
                    <tr>
                         <td class="px-6 py-4 font-medium">
                            {{ $i + 1 }}
                        </td>
                        {{-- TEAM LABEL --}}
                        <td class="px-6 py-4 font-medium">
                            {{ $team->name ?? 'Not a Team' }}
                        </td>

                        {{-- PLAYERS --}}
                        <td class="px-6 py-4 text-sm text-slate-600">
                            @if($event->type === 'individual')
                                {{ $team->players->first()->first_name.' '.$team->players->first()->last_name }}
                            @else
                            {{ $team->players->pluck('first_name')->join(', ') }}
                            @endif
                        </td>
                        {{-- PLAYERS --}}
                        <td class="px-6 py-4 text-sm text-slate-600">
                            @if($event->type === 'individual')
                                {{ $team->players->first()->country}}
                            @else
                            {{ $team->players->pluck('first_name')->join(', ') }}
                            @endif
                        </td>

                        {{-- ASSIGNED --}}
                        <td class="px-6 py-4">
                            <span class="text-xs font-semibold px-2 py-1 rounded
                                {{ $team->is_assigned
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-100 text-gray-600' }}">
                                {{ $team->is_assigned ? 'YES' : 'NO' }}
                            </span>
                        </td>

                        {{-- ACTIONS --}}
                        <td class="px-6 py-4 text-right">
                            <flux:button
                                size="xs"
                                
                                wire:click="openEditTeam({{ $team->id }})"
                            >
                                Edit
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                            No teams yet
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- CREATE / EDIT MODAL --}}
    <flux:modal name="manage-team" class="md:w-[520px]">
        <form wire:submit.prevent="saveTeam" class="space-y-6">

            <flux:heading size="lg">
                {{ $editingTeamId ? 'Edit Team' : 'Create Team' }}
            </flux:heading>

            {{-- TEAM NAME (only for team + mixed) --}}
            @if ($this->isTeamMixed)
                <flux:input
                    label="Team Name"
                    wire:model.defer="teamName"
                />
            @endif

            {{-- PLAYERS --}}
            <div class="space-y-3">
                <p class="text-sm font-semibold">Players</p>

                @foreach ($players as $index => $player)
                    <div class="flex gap-2 items-end">

                        <flux:input
                            placeholder="First name"
                            wire:model.defer="players.{{ $index }}.first_name"
                            class="flex-1"
                        />

                        <flux:input
                            placeholder="Last name"
                            wire:model.defer="players.{{ $index }}.last_name"
                            class="flex-1"
                        />

                        <flux:input
                            placeholder="Tag"
                            wire:model.defer="players.{{ $index }}.tag"
                            class=""
                        />



                        @if (!$this->isSingles && !$this->isDoubles && count($players) > 1)
                            <flux:button
                                type="button"
                                size="xs"
                                variant="ghost"
                                wire:click="removePlayer({{ $index }})"
                            >
                                ✕
                            </flux:button>
                        @endif

                    </div>
                @endforeach

                @if ($this->isTeamMixed)
                    <flux:button
                        type="button"
                        size="sm"
                        
                        wire:click="addPlayer"
                    >
                        + Add Player
                    </flux:button>
                @endif
            </div>

            <div class="flex pt-2">
                <flux:spacer />
                <flux:button type="submit" variant="primary">
                    Save
                </flux:button>
            </div>

        </form>
    </flux:modal>

</div>
