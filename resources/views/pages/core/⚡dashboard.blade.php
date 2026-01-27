<?php

use Livewire\Component;
use App\Models\Tournament;
use App\Models\Event;
use App\Models\Player;
use App\Models\MatchGame;
use App\Models\Project;
use Livewire\Volt\Volt;
use Livewire\Attributes\Validate;

new class extends Component {
    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('nullable|string')]
    public $location = '';

    #[Validate('required|date')]
    public $start_date = '';

    #[Validate('required|date|after_or_equal:start_date')]
    public $end_date = '';

    #[Validate('required|exists:projects,id')]
    public $project_id = '';

    public $newTournamentModal = false;

    public function with(): array
    {
        $project = Project::first(); // Get the first project for now
        $this->project_id = $project ? $project->id : null;


        return [
            'totalTournaments' => Tournament::count(),
            'totalEvents' => Event::count(),
            'liveMatches' => MatchGame::where('status', 'live')->count(),
            'totalPlayers' => Player::count(),
            'tournaments' => Tournament::with('project')
                ->withCount('events')
                ->latest()
                ->take(6)
                ->get(),
            'project' => $project,
        ];
    }

    public function createTournament()
    {
        // dd($this->name, $this->location, $this->start_date, $this->end_date, $this->project_id);
        $this->validate();

        Tournament::create([
            'name' => $this->name,
            'location' => $this->location,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'project_id' => $this->project_id,
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        $this->resetprops();
        // $this->dispatch('close-modal', name: 'create-tournament');
        $this->newTournamentModal = false;

        session()->flash('message', 'Tournament created successfully!');
    }

    public function resetprops()
    {
        $this->reset(['name', 'location', 'start_date', 'end_date']);
    }
};
?>

<div class="">


    <section class="bg-gray-50 p-8">

        <div class="max-w-7xl mx-auto space-y-10">

            <!-- Greeting Section -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-8 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">
                            @php
                                $hour = now()->format('H');
                                $greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
                            @endphp
                            {{ $greeting }}, {{ auth()->user()->name }}! 👋
                        </h1>
                        <p class="text-blue-100 text-lg">Welcome back to your tournament management dashboard</p>
                    </div>
                    <div class="hidden md:block">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                            <p class="text-sm text-blue-100">Today's Date</p>
                            <p class="text-xl font-semibold">{{ now()->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Tournaments</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-2xl font-semibold text-gray-900">{{ $totalTournaments }}</span>
                        <div class="text-blue-500 bg-blue-50 p-2 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="浸8 13h8m-8-4h8m-8 8h8M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Events</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-2xl font-semibold text-gray-900">{{ $totalEvents }}</span>
                        <div class="text-gray-400 bg-gray-50 p-2 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Live Matches</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-2xl font-semibold text-gray-900">{{ $liveMatches }}</span>
                        <div class="flex items-center justify-center">
                            <span class="relative flex h-3 w-3 mr-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Players</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-2xl font-semibold text-gray-900">{{ number_format($totalPlayers) }}</span>
                        <div class="text-gray-400 bg-gray-50 p-2 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <section>
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Tournaments</h2>
                    <p class="text-sm text-gray-500">Manage your active and upcoming badminton events.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <flux:modal.trigger name="create-tournament">
                        <button
                            class="border-2 border-dashed border-gray-200 rounded-xl p-6 flex flex-col items-center justify-center text-gray-500 hover:border-blue-400 hover:text-blue-500 transition-colors group">
                            <svg class="w-8 h-8 mb-2 group-hover:scale-110 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="font-medium">Add New Tournament</span>
                        </button>
                    </flux:modal.trigger>

                    @forelse ($tournaments as $tournament)
                        <div
                            class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-6 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-4">
                                    @if ($tournament->status === 'live')
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Live</span>
                                    @elseif ($tournament->status === 'completed')
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Completed</span>
                                    @else
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ ucfirst($tournament->status) }}</span>
                                    @endif
                                    <span
                                        class="text-xs text-gray-400 font-medium">{{ $tournament->start_date->format('M d') }}</span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $tournament->name }}</h3>
                                <p class="text-sm text-gray-500 mb-4">{{ $tournament->location ?? 'TBA' }}</p>
                                <div class="grid grid-cols-3 gap-2 border-t border-gray-50 pt-4">
                                    <div class="text-center">
                                        <p class="text-[10px] uppercase text-gray-400 font-bold">Events</p>
                                        <p class="text-sm font-semibold text-gray-700">{{ $tournament->events_count ?? 0 }}
                                        </p>
                                    </div>
                                    <div class="text-center border-x border-gray-50">
                                        <p class="text-[10px] uppercase text-gray-400 font-bold">Matches</p>
                                        <p class="text-sm font-semibold text-gray-700">0</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-[10px] uppercase text-gray-400 font-bold">Players</p>
                                        <p class="text-sm font-semibold text-gray-700">0</p>
                                    </div>
                                </div>
                            </div>
                            <flux:button 
                            href="{{ route('tournaments.show', $tournament->id) }}" wire:navigate
                                class="mt-6 w-full py-2 px-4 {{ $tournament->status === 'live' ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200' }} rounded-lg text-sm font-medium transition-colors">
                                {{ $tournament->status === 'live' ? 'View Details' : 'Manage' }}
                            </flux:button>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-12 text-gray-500">
                            <p>No tournaments yet. Create your first tournament!</p>
                        </div>
                    @endforelse
                </div>
            </section>



        </div>

    </section>

    <flux:modal wire:model.self="newTournamentModal" wire:cancel="resetprops" wire:close="resetprops"
        name="create-tournament" class="md:w-[500px]">
        <form wire:submit="createTournament" class="space-y-6">
            <div>
                <flux:heading size="lg">Create New Tournament</flux:heading>
                <flux:subheading class="mt-2">Set up a new badminton tournament.</flux:subheading>
            </div>

            @if (session()->has('message'))
                <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-sm">
                    {{ session('message') }}
                </div>
            @endif

            <flux:input wire:model="name" label="Tournament Name" placeholder="e.g., National Open 2024" required />
            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror

            <flux:input wire:model="location" label="Location" placeholder="e.g., Wembley Arena, London" />
            @error('location') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:input wire:model="start_date" label="Start Date" type="date" required />
                    @error('start_date') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <flux:input wire:model="end_date" label="End Date" type="date" required />
                    @error('end_date') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>



            <div class="flex gap-3">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" :disabled="!$project">Create Tournament</flux:button>
            </div>
        </form>
    </flux:modal>
</div>