<?php

use Livewire\Component;
use App\Models\Tournament;
use Livewire\Attributes\On;

new class extends Component {
    public $sidebarTournaments;

    public function mount()
    {
        $this->sidebarTournaments = Tournament::with('events')
            ->orderBy('created_at', 'desc')
            ->get();
    }

       #[On('tournament-created')] 
       #[On('event-created')] 
    public function refreshTournaments()
    {
        $this->sidebarTournaments = Tournament::with('events')
            ->orderBy('created_at', 'desc')
            ->get();
    }
};
?>

<flux:sidebar.nav>



    @forelse ($sidebarTournaments as $tournament)
        <flux:sidebar.group expandable heading="{{ $tournament->name }}" class="ml-2">
            @forelse ($tournament->events as $event)
                <flux:sidebar.item href="{{ route('events.show', $event) }}" :current="request()->routeIs('events.show')
                                            && request()->route('event')?->id === $event->id">
                    {{ $event->name }}
                </flux:sidebar.item>
            @empty
                <div class="px-3 py-2 text-xs text-slate-500">
                    No events
                </div>
            @endforelse
        </flux:sidebar.group>
    @empty
        <div class="px-3 py-2 text-xs text-slate-500">
            No tournaments yet
        </div>
    @endforelse


</flux:sidebar.nav>