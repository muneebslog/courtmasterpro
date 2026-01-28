<?php

use Livewire\Component;
use Flux\Flux;
use App\Models\Team;
use App\Models\Player;

new class extends Component {
    public $event;

    public ?string $teamName = null;

    public array $players = [
        ['first_name' => '', 'last_name' => ''],
    ];

    public function addPlayer()
    {
        $this->players[] = [
            'first_name' => '',
            'last_name' => '',
        ];
    }

    public function removePlayer(int $index)
    {
        if (count($this->players) > 1) {
            unset($this->players[$index]);
            $this->players = array_values($this->players);
        }
    }

    public function createTeam()
    {
        $this->validate([
            'teamName' => 'nullable|string|max:255',
            'players.*.first_name' => 'required|string|max:100',
            'players.*.last_name' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () {

            $team = Team::create([
                'event_id' => $this->event->id,
                'name' => $this->teamName,
            ]);

            foreach ($this->players as $playerData) {
                $player = Player::create($playerData);
                $team->players()->attach($player->id);
            }
        });

        // reset state
        $this->reset('teamName', 'players');

        $this->players = [
            ['first_name' => '', 'last_name' => ''],
        ];

        // close modal
        Flux::modal('create-team')->close();

        // tell parent to refresh if needed
        $this->dispatch('team-created');
    }


};
?>

<div>
    <flux:modal name="create-team" class="md:w-[520px]">
        <form wire:submit.prevent="createTeam" class="space-y-6">

            <div>
                <flux:heading size="lg">
                    Create Team
                </flux:heading>
                <flux:text class="mt-2">
                    Add a team for <strong>{{ $event->name }}</strong>
                </flux:text>
            </div>

            {{-- TEAM NAME --}}
            <flux:input label="Team Name (optional)" placeholder="e.g. Falcons" wire:model.defer="teamName" />

            {{-- PLAYERS --}}
            <div class="space-y-3">
                <p class="text-sm font-semibold text-slate-700">
                    Players
                </p>

                @foreach ($players as $index => $player)
                    <div class="flex gap-2 items-end">

                        <flux:input placeholder="First name" wire:model.defer="players.{{ $index }}.first_name"
                            class="flex-1" />

                        <flux:input placeholder="Last name" wire:model.defer="players.{{ $index }}.last_name"
                            class="flex-1" />

                        @if (count($players) > 1)
                            <flux:button type="button" size="xs" variant="ghost" wire:click="removePlayer({{ $index }})">
                                ✕
                            </flux:button>
                        @endif

                    </div>
                @endforeach

                <flux:button type="button" size="sm" wire:click="addPlayer">
                    + Add Player
                </flux:button>
            </div>

            {{-- ACTIONS --}}
            <div class="flex pt-2">
                <flux:spacer />
                <flux:button type="submit" variant="primary">
                    Create Team
                </flux:button>
            </div>

        </form>
    </flux:modal>