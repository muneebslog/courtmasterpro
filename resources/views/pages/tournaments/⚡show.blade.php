<?php

use App\Models\Tournament;
use Livewire\Component;
use Flux\Flux;
use App\Models\Event;


new class extends Component {
    public Tournament $tournament;

    // Form state
    public string $name = '';
    public string $type = 'individual'; // individual | team
    public string $default_discipline = 'singles'; // singles | doubles | mixed
    public int $best_of_sets = 3;
    public string $status = 'upcoming';

    public bool $showEditTournamentModal = false;

    public string $edit_name = '';
    public string $edit_location = '';
    public string $edit_status = 'draft';
    public string $edit_start_date = '';
    public string $edit_end_date = '';

    public bool $showDeleteTournamentModal = false;


    public function canManageTournament(): bool
    {
        $user = auth()->user();

        return $user->isProjectOwner() || $user->hasRole('admin');
    }




    public function mount(Tournament $tournament)
    {
        $this->tournament = $tournament->load([
            'events.teams.players',
            'events.rounds.matches',
        ]);

        // dd($this->tournament);
    }

    public function openEditTournament()
    {
        abort_unless($this->canManageTournament(), 403);
        $this->edit_name = $this->tournament->name;
        $this->edit_location = $this->tournament->location;
        $this->edit_status = $this->tournament->status;

        // ✅ preload dates (HTML date input expects Y-m-d)
        $this->edit_start_date = $this->tournament->start_date?->format('Y-m-d') ?? '';
        $this->edit_end_date = $this->tournament->end_date?->format('Y-m-d') ?? '';

        $this->showEditTournamentModal = true;
    }

    public function updateTournament()
    {
        abort_unless($this->canManageTournament(), 403);
        $this->validate([
            'edit_name' => ['required', 'string', 'max:255'],
            'edit_location' => ['nullable', 'string', 'max:255'],
            'edit_status' => ['required', 'in:draft,live,completed'],
            'edit_start_date' => ['required', 'date'],
            'edit_end_date' => ['required', 'date', 'after_or_equal:edit_start_date'],
        ]);


        $this->tournament->update([
            'name' => $this->edit_name,
            'location' => $this->edit_location,
            'status' => $this->edit_status,
            'start_date' => $this->edit_start_date,
            'end_date' => $this->edit_end_date,
        ]);

        $this->tournament->refresh();

        $this->showEditTournamentModal = false;
    }

    public function canDeleteTournament(): bool
    {
        return $this->tournament->status === 'draft'
            && $this->tournament->events->count() === 0;
    }

    public function deleteTournament()
    {
        abort_unless($this->canManageTournament(), 403);
        if (!$this->canDeleteTournament()) {
            return;
        }

        $this->tournament->delete();

        return redirect()->route('dashboard');
    }



    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:individual,team'],
            'default_discipline' => ['required', 'in:singles,doubles,mixed'],
            'best_of_sets' => ['required', 'in:1,3,5'],
            'status' => ['required', 'in:upcoming,live,finished'],
        ];
    }

    public function createEvent()
    {
        abort_unless($this->canManageTournament(), 403);
        $this->validate();

        Event::create([
            'tournament_id' => $this->tournament->id,
            'name' => $this->name,
            'type' => $this->type,
            'default_discipline' => $this->default_discipline,
            'best_of_sets' => $this->best_of_sets,
            'status' => $this->status,
        ]);

        // Reset form
        $this->reset('name', 'type', 'default_discipline', 'best_of_sets', 'status');

        // Close modal
        $this->dispatch('event-created');
        Flux::modal('create-event')->close();

        // Refresh tournament data
        $this->tournament->refresh();
    }

    public function getStatsProperty()
    {
        return [
            'events' => $this->tournament->events->count(),
            'matches' => $this->tournament->events
                ->flatMap(fn($e) => $e->rounds)
                ->flatMap(fn($r) => $r->matches)
                ->count(),
            'players' => $this->tournament->events
                ->flatMap(fn($e) => $e->teams)
                ->flatMap(fn($t) => $t->players)
                ->unique('id')
                ->count(),
            'live_matches' => $this->tournament->events
                ->flatMap(fn($e) => $e->rounds)
                ->flatMap(fn($r) => $r->matches)
                ->where('status', 'live')
                ->count(),
        ];
    }

    public function publishTournament()
    {
        abort_unless($this->canManageTournament(), 403);
        if ($this->tournament->status === 'draft') {
            $this->tournament->update(['status' => 'live']);
        } elseif ($this->tournament->status === 'live') {
            $this->tournament->update(['status' => 'completed']);
        }

        $this->tournament->refresh();
    }

};
?>


<div>


    <section class="bg-gray-50 p-8">

        <div class="max-w-7xl mx-auto space-y-8">

            <div class="bg-white rounded-[12px] border border-gray-100 shadow-sm p-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-3xl font-bold text-gray-900">
                                {{ $tournament->name }}
                            </h1>

                            <span @class([
                                'px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider',
                                'bg-green-100 text-green-700' => $tournament->status === 'live',
                                'bg-blue-100 text-blue-700' => $tournament->status === 'draft',
                                'bg-gray-100 text-gray-600' => $tournament->status === 'completed',
                            ])>
                                {{ ucfirst($tournament->status) }}
                            </span>

                        </div>
                        <div class="flex flex-wrap items-center gap-6 text-sm text-gray-500 font-medium">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $tournament->location }}

                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                {{ $tournament->start_date->format('M d, Y') }}
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                {{ $tournament->end_date->format('M d, Y') }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center flex-col gap-3">
                        <div class="flex items-center gap-3">
                            @if ($this->canManageTournament())
                                @if ($this->canDeleteTournament())
                                    <flux:button variant="danger" wire:click="$set('showDeleteTournamentModal', true)">
                                        Delete Tournament
                                    </flux:button>
                                @endif

                                <flux:button variant="outline" wire:click="openEditTournament">
                                    Edit Tournament
                                </flux:button>
                            @endif

                        </div>

                        @if ($this->canManageTournament())

                            @if ($tournament->status !== 'completed')
                                <flux:button variant="primary" wire:click="publishTournament">
                                    @if ($tournament->status === 'draft')
                                        Publish Tournament
                                    @else
                                        Mark as Completed
                                    @endif
                                </flux:button>
                            @endif
                        @endif



                    </div>
                </div>
            </div>




            <section>
                <div class="flex items-center w-full justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Events</h2>
                        <p class="text-sm text-gray-500">Categories created for this tournament</p>
                    </div>
                    <div class="">
                        @if ($this->canManageTournament())

                            <flux:modal.trigger name="create-event">
                                <flux:button wire:navigate icon="plus"
                                    class="flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700">
                                    Create Event
                                </flux:button>
                            </flux:modal.trigger>
                        @endif

                    </div>


                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse ($tournament->events as $event)
                        <div
                            class="bg-white rounded-[12px] border border-gray-100 shadow-sm flex flex-col hover:border-blue-200 transition-colors">
                            <div class="p-6 flex-1">

                                {{-- Tags --}}
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span
                                        class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-bold uppercase rounded">
                                        {{ $event->type }}
                                    </span>

                                    <span
                                        class="px-2 py-0.5 bg-gray-100 text-gray-600 text-[10px] font-bold uppercase rounded">
                                        {{ $event->default_discipline }}
                                    </span>
                                </div>

                                {{-- Name --}}
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                    {{ $event->name }}
                                </h3>

                                {{-- Stats --}}
                                <div class="flex items-center gap-6 border-t border-gray-50 pt-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase">Teams</p>
                                        <p class="text-sm font-semibold text-gray-700">
                                            {{ $event->teams->count() }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase">Matches</p>
                                        <p class="text-sm font-semibold text-gray-700">
                                            {{ $event->rounds->flatMap->matches->count() }}
                                        </p>
                                    </div>

                                    <div class="ml-auto">
                                        <span @class([
                                            'px-2 py-0.5 text-[10px] font-bold uppercase rounded',
                                            'bg-green-100 text-green-700' => $event->status === 'ongoing',
                                            'bg-blue-100 text-blue-700' => $event->status === 'upcoming',
                                            'bg-gray-100 text-gray-500' => $event->status === 'completed',
                                        ])>
                                            {{ ucfirst($event->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Action --}}
                            <div class="px-6 pb-6">
                                <a href="{{ route('events.show', $event) }}" wire:navigate
                                    class="block w-full text-center py-2 bg-gray-50 hover:bg-blue-600 hover:text-white text-gray-700 rounded-lg text-sm font-medium border border-gray-100 transition-all">
                                    View Event
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No events created yet.</p>
                    @endforelse
                </div>

                <flux:modal name="create-event" class="md:w-[420px]">
                    <form wire:submit.prevent="createEvent" class="space-y-6">

                        <div>
                            <flux:heading size="lg">Create Event</flux:heading>
                            <flux:text class="mt-2">
                                Create an event category for this tournament.
                            </flux:text>
                        </div>

                        {{-- Event Name --}}
                        <flux:input label="Event Name" placeholder="Men's Singles U16" wire:model.defer="name" />

                        {{-- Event Type --}}
                        <flux:select label="Event Type" wire:model.defer="type">
                            <option value="individual">Individual</option>
                            <option value="team">Team Event</option>
                        </flux:select>

                        {{-- Default Discipline --}}
                        <flux:select label="Default Discipline" wire:model.defer="default_discipline">
                            <option value="singles">Singles</option>
                            <option value="doubles">Doubles</option>
                            <option value="mixed">Mixed</option>
                        </flux:select>

                        {{-- Best of Sets --}}
                        <flux:select label="Best of Sets" wire:model.defer="best_of_sets">
                            <option value="1">1 (Knockout)</option>
                            <option value="3">3 (Standard)</option>
                            <option value="5">5 (Finals)</option>
                        </flux:select>

                        {{-- Status --}}
                        <flux:select label="Status" wire:model.defer="status">
                            <option value="upcoming">upcoming</option>
                            <option value="live">Live</option>
                            <option value="finished">Finished</option>
                        </flux:select>

                        <div class="flex pt-2">
                            <flux:spacer />
                            <flux:button type="submit" variant="primary">
                                Create Event
                            </flux:button>
                        </div>

                    </form>
                </flux:modal>

                <flux:modal wire:model.self="showEditTournamentModal" class="md:w-[420px]">
                    <form wire:submit.prevent="updateTournament" class="space-y-6">

                        <div>
                            <flux:heading size="lg">Edit Tournament</flux:heading>
                            <flux:text class="mt-2">
                                Update tournament details.
                            </flux:text>
                        </div>

                        <flux:input label="Tournament Name" wire:model.defer="edit_name" />

                        <flux:input label="Location" wire:model.defer="edit_location" />

                        <flux:input type="date" label="Start Date" wire:model.defer="edit_start_date" />

                        <flux:input type="date" label="End Date" wire:model.defer="edit_end_date" />


                        <flux:select label="Status" wire:model.defer="edit_status">
                            <option value="draft">draft</option>
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

                <flux:modal name="delete-profile" wire:model.self="showDeleteTournamentModal" class="min-w-[22rem]">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">Delete Tournament</flux:heading>
                            <flux:text class="mt-2">
                                You're about to delete this tournament: <strong>{{ $tournament->name }}</strong><br>
                                This action cannot be reversed.
                            </flux:text>
                        </div>
                        <div class="flex gap-2">
                            <flux:spacer />
                            <flux:modal.close>
                                <flux:button variant="ghost">Cancel</flux:button>
                            </flux:modal.close>
                            <flux:button type="submit" wire:click="deleteTournament" variant="danger">Delete tournament
                            </flux:button>
                        </div>
                    </div>
                </flux:modal>




            </section>



        </div>

    </section>
</div>