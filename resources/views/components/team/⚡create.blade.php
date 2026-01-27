<?php

use Livewire\Component;
use Flux\Flux;

new class extends Component {
    public bool $showCreateTeamModal = false;

    public ?string $teamName = null;

    public array $players = [
        ['first_name' => '', 'last_name' => ''],
    ];


    public function addPlayerRow()
    {
        $this->players[] = ['first_name' => '', 'last_name' => ''];
    }

    public function removePlayerRow($index)
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

            $team = $this->event->teams()->create([
                'name' => $this->teamName,
            ]);

            foreach ($this->players as $playerData) {
                $player = \App\Models\Player::create($playerData);
                $team->players()->attach($player->id);
            }
        });

        // reset state
        $this->reset('teamName', 'players');

        $this->players = [
            ['first_name' => '', 'last_name' => ''],
        ];

        Flux::modal('create-team')->close();

    }

};
?>

<div>
    <flux:modal.trigger name="create-team">
        <flux:button size="sm" variant="primary">
            +
        </flux:button>
    </flux:modal.trigger>
    <flux:modal name="create-team" class="md:w-[520px]">
        <form wire:submit.prevent="createTeam" class="space-y-6">

            <div>
                <flux:heading size="lg">Create Team</flux:heading>
                <flux:text class="mt-2">
                    Add a team and its players for this event.
                </flux:text>
            </div>

            {{-- TEAM NAME --}}
            <flux:input label="Team Name (optional)" wire:model.defer="teamName" placeholder="e.g. Falcons" />

            {{-- PLAYERS --}}
            <div class="space-y-3">
                <p class="text-sm font-semibold text-slate-700">
                    Players
                </p>

                @foreach ($players as $index => $player)
                    <div class="flex gap-2 items-end">

                        <flux:input wire:model.defer="players.{{ $index }}.first_name" placeholder="First name"
                            class="flex-1" />

                        <flux:input wire:model.defer="players.{{ $index }}.last_name" placeholder="Last name"
                            class="flex-1" />

                        @if (count($players) > 1)
                            <flux:button type="button" variant="ghost" size="xs" wire:click="removePlayerRow({{ $index }})">
                                ✕
                            </flux:button>
                        @endif
                    </div>
                @endforeach

                <flux:button type="button" variant="primary" size="sm" wire:click="addPlayerRow">
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
</div>