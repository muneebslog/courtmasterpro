<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Event;
use App\Models\Round;

new #[Layout('layouts::publicview')]
    class extends Component {

    public Event $event;
    public $selectedRoundId;
    public function mount(Event $event)
    {
        $this->event = $event;
        $firstRound = $this->event->rounds()->orderBy('order_no', 'asc')->first();
        $this->selectedRoundId = $firstRound?->id;
    }

    public function selectRound($id)
    {
        $this->selectedRoundId = $id;
    }

    public function getMatchesProperty()
    {
        if (!$this->selectedRoundId) return [];
        
        return Round::find($this->selectedRoundId)
            ->matches()
            ->with(['teamA', 'teamB', 'sets.scores']) 
            ->get();
    }
};
?>

<div>
    <section style="margin: 0; background-color: #0B0F14; color: #F9FAFB; min-height: 100vh; font-family: Inter, system-ui, sans-serif;">
        
        <header style="width: 100%; height: 64px; background-color: #0B0F14; border-bottom: 1px solid #111827; display: flex; align-items: center; justify-content: center; position: sticky; top: 0; z-index: 20;">
            <div style="width: 100%; max-width: 1000px; padding: 0 16px; display: flex; justify-content: space-between; align-items: center;">
                <h1 style="font-size: 18px; font-weight: 600; margin: 0;">{{ $event->name }}</h1>
                <a href="javascript:window.history.back()" style="text-decoration: none; color: #9CA3AF; font-size: 13px;">&larr; Back</a>
            </div>
        </header>

        {{-- Round Selector --}}
        <nav style="background-color: #0F1621; border-bottom: 1px solid #111827; overflow-x: auto; white-space: nowrap;">
            <div style="max-width: 1000px; margin: 0 auto; display: flex;">
                @foreach ($event->rounds()->orderBy('order_no', 'asc')->get() as $round)
                    <button wire:click="selectRound({{ $round->id }})"
                        style="padding: 14px 20px; border: none; background: none; color: {{ $selectedRoundId == $round->id ? '#3B82F6' : '#9CA3AF' }}; font-size: 12px; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid {{ $selectedRoundId == $round->id ? '#3B82F6' : 'transparent' }};">
                        {{ $round->name }}
                    </button>
                @endforeach
            </div>
        </nav>

        <main style="width: 100%; max-width: 1000px; margin: 0 auto; padding: 16px;">
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse ($this->matches as $match)
                    <a href="{{ url("viewer/matches/{$match->id}/scoreboard") }}" wire:navigate
                       style="display: block; text-decoration: none; background-color: #0F1621; border: 1px solid #1F2937; border-radius: 8px; padding: 16px;">
                        
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="flex: 1;">
                                {{-- Team A Row --}}
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <span style="font-size: 16px; color: {{ $match->winner_team_id == $match->team_a_id ? '#F9FAFB' : '#D1D5DB' }}; font-weight: {{ $match->winner_team_id == $match->team_a_id ? '700' : '500' }};">
                                        {{ $match->teamA->display_name ?? 'TBD' }}{{ $match->winner_team_id !== null && $match->winner_team_id == $match->team_a_id ? '👑' : '' }}
                                    </span>
                                    
                                    <div style="display: flex; gap: 6px;">
                                        @foreach($match->sets as $set)
                                            @php
                                                $ptsA = $set->scores->where('team_id', $match->team_a_id)->first()?->points ?? 0;
                                                $ptsB = $set->scores->where('team_id', $match->team_b_id)->first()?->points ?? 0;
                                                $isWinner = $ptsA > $ptsB;
                                            @endphp
                                            <span style="
                                                width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 4px; 
                                                background-color: {{ $isWinner ? '#3B82F6' : '#111827' }}; 
                                                color: {{ $isWinner ? '#FFFFFF' : '#9CA3AF' }}; 
                                                font-size: 14px; font-weight: 800; border: 1px solid {{ $isWinner ? '#3B82F6' : '#1F2937' }};">
                                                {{ $ptsA }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Team B Row --}}
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 16px; color: {{ $match->winner_team_id == $match->team_b_id ? '#F9FAFB' : '#D1D5DB' }}; font-weight: {{ $match->winner_team_id == $match->team_b_id ? '700' : '500' }};">
                                        {{ $match->teamB->display_name ?? 'TBD' }}{{ $match->winner_team_id !== null && $match->winner_team_id == $match->team_b_id ? '👑' : '' }}
                                    </span>

                                    <div style="display: flex; gap: 6px;">
                                        @foreach($match->sets as $set)
                                            @php
                                                $ptsA = $set->scores->where('team_id', $match->team_a_id)->first()?->points ?? 0;
                                                $ptsB = $set->scores->where('team_id', $match->team_b_id)->first()?->points ?? 0;
                                                $isWinner = $ptsB > $ptsA;
                                            @endphp
                                            <span style="
                                                width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 4px; 
                                                background-color: {{ $isWinner ? '#3B82F6' : '#111827' }}; 
                                                color: {{ $isWinner ? '#FFFFFF' : '#9CA3AF' }}; 
                                                font-size: 14px; font-weight: 800; border: 1px solid {{ $isWinner ? '#3B82F6' : '#1F2937' }};">
                                                {{ $ptsB }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Vertical Status Strip --}}
                            <div style="margin-left: 20px; padding: 4px 0 4px 16px; border-left: 1px solid #1F2937; display: flex; flex-direction: column; align-items: center; min-width: 60px;">
                                <span style="font-size: 10px; font-weight: 900; color: {{ $match->status == 'live' ? '#22C55E' : '#6B7280' }}; text-transform: uppercase;">
                                    {{ $match->status }}
                                </span>
                                @if($match->court_no)
                                    <span style="font-size: 10px; color: #4B5563; margin-top: 4px;">CRT {{ $match->court_no }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div style="color: #6B7280; text-align: center; padding: 60px 0; font-size: 14px;">No matches scheduled.</div>
                @endforelse
            </div>
        </main>
    </section>
</div>