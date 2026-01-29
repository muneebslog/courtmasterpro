<?php
use Livewire\Attributes\Layout;
use App\Models\Tournament;
use Livewire\Component;

new #[Layout('layouts::publicview')]
    class extends Component {
    public $tournaments;

    public function mount()
    {
        $this->tournaments = Tournament::query()
            ->orderBy('start_date', 'desc')
            ->get();
    }
};
?>
<div class="">
    <header
        style="width: 100%; height: 64px; border-bottom: 1px solid #1F2937; background-color: #0B0F14; display: flex; align-items: center; justify-content: center; position: sticky; top: 0; z-index: 10;">
        <div
            style="width: 100%; max-width: 900px; padding: 0 20px; display: flex; justify-content: space-between; align-items: center;">
            <h1 style="font-size: 22px; font-weight: 600; margin: 0;">Tournaments</h1>
            <a href="javascript:window.history.back()" style="color: #D1D5DB; font-size: 14px; display: flex; align-items: center;">&larr; Back</a>
        </div>
    </header>

    <main style="max-width: 900px; margin: 0 auto; padding: 32px 20px;">

        <div style="display: flex; flex-direction: column; gap: 16px;">
            @forelse ($tournaments as $tournament)

                <a wire:navigate href="{{ url('viewer/tournaments/' . $tournament->id . '/events') }}"
                    style="background-color: #0F1621; border: 1px solid #1F2937; border-radius: 8px; padding: 20px; display: flex; justify-content: space-between; align-items: center; transition: background-color 0.2s;"
                    onmouseover="this.style.backgroundColor='#111827'" onmouseout="this.style.backgroundColor='#0F1621'">
                    <div>
                        <div style="font-size: 18px; font-weight: 600; color: #F9FAFB; margin-bottom: 4px;">{{ $tournament->name }}</div>
                        <div style="font-size: 14px; color: #D1D5DB;"> {{ $tournament->location }}</div>
                        <div style="font-size: 12px; color: #9CA3AF; margin-top: 4px;">{{ $tournament->start_date->format('M d') }} – {{ $tournament->end_date->format('M d, Y') }}</div>
                    </div>
                    <div
                        style="border: 1px solid #22C55E; color: #22C55E; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                        Live
                    </div>
                </a>
            @empty
                <div style="color: #9CA3AF; text-align: center; padding: 40px 0;">
                    No tournaments available at the moment.
                </div>
            @endforelse



        </div>
    </main>

</div>