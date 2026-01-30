<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tournament;

new #[Layout('layouts::publicview')]
    class extends Component {

    public Tournament $tournament;
    public $events;

    public function mount(Tournament $tournament)
    {
        $this->tournament = $tournament;
        $this->events = $tournament->events()->orderBy('name')->get();
    }
    //
};
?>

<div>
    {{-- Always remember that you are absolutely unique. Just like everyone else. - Margaret Mead --}}


    <section
        style="margin: 0; font-family: Inter, system-ui, -apple-system, sans-serif; background-color: #0B0F14; color: #F9FAFB; -webkit-font-smoothing: antialiased;">

        <header
            style="width: 100%; height: 64px; background-color: #0B0F14; border-bottom: 1px solid #111827; display: flex; align-items: center; justify-content: center; position: sticky; top: 0; z-index: 10;">
            <div
                style="width: 100%; max-width: 1000px; padding: 0 16px; display: flex; justify-content: space-between; align-items: center;">
                <h1 style="font-size: 22px; font-weight: 600; margin: 0; color: #F9FAFB;">Events</h1>
                <a href="javascript:window.history.back()" style="text-decoration: none; color: #D1D5DB; font-size: 14px; font-weight: 500;">&larr;
                    Back</a>
            </div>
        </header>

        <main style="width: 100%; max-width: 1000px; margin: 0 auto; box-sizing: border-box; padding: 24px 16px;">

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">

                 @forelse ($events as $event)
                <div style="background-color: #0F1621; border: 1px solid #111827; border-radius: 8px; padding: 20px; cursor: pointer; transition: background-color 0.2s;"
                    onmouseover="this.style.backgroundColor='#111827'"
                    onmouseout="this.style.backgroundColor='#0F1621'">
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 16px;">
                        <h2 style="font-size: 17px; font-weight: 600; margin: 0; color: #F9FAFB; line-height: 1.4;">
                            {{ $event->name }}</h2>
                        <span
                            style="background-color: #22C55E; color: #0B0F14; font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 4px; text-transform: uppercase; white-space: nowrap;">{{ $event->status }}</span>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        <span
                            style="background-color: #111827; border: 1px solid #374151; color: #D1D5DB; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">Singles</span>
                        <span
                            style="background-color: #111827; border: 1px solid #374151; color: #D1D5DB; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">Individual</span>
                        <span
                            style="background-color: #111827; border: 1px solid #374151; color: #D1D5DB; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">Bo3</span>
                    </div>
                </div>
                @empty
                <div style="color: #9CA3AF; text-align: center; padding: 40px 0;">
                    No events available for this tournament at the moment.
                </div>
                @endforelse

                {{-- <div style="background-color: #0F1621; border: 1px solid #111827; border-radius: 8px; padding: 20px; cursor: pointer; transition: background-color 0.2s;"
                    onmouseover="this.style.backgroundColor='#111827'"
                    onmouseout="this.style.backgroundColor='#0F1621'">
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 16px;">
                        <h2 style="font-size: 17px; font-weight: 600; margin: 0; color: #F9FAFB; line-height: 1.4;">
                            Women's Singles U19</h2>
                        <span
                            style="background-color: #6B7280; color: #F9FAFB; font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 4px; text-transform: uppercase; white-space: nowrap;">Upcoming</span>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        <span
                            style="background-color: #111827; border: 1px solid #374151; color: #D1D5DB; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">Singles</span>
                        <span
                            style="background-color: #111827; border: 1px solid #374151; color: #D1D5DB; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">Individual</span>
                        <span
                            style="background-color: #111827; border: 1px solid #374151; color: #D1D5DB; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">Bo3</span>
                    </div>
                </div>

                <div style="background-color: #0F1621; border: 1px solid #111827; border-radius: 8px; padding: 20px; cursor: pointer; transition: background-color 0.2s;"
                    onmouseover="this.style.backgroundColor='#111827'"
                    onmouseout="this.style.backgroundColor='#0F1621'">
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 16px;">
                        <h2 style="font-size: 17px; font-weight: 600; margin: 0; color: #F9FAFB; line-height: 1.4;">
                            Men's Doubles Open</h2>
                        <span
                            style="background-color: #3B82F6; color: #F9FAFB; font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 4px; text-transform: uppercase; white-space: nowrap;">Completed</span>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        <span
                            style="background-color: #111827; border: 1px solid #374151; color: #D1D5DB; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">Doubles</span>
                        <span
                            style="background-color: #111827; border: 1px solid #374151; color: #D1D5DB; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">Individual</span>
                        <span
                            style="background-color: #111827; border: 1px solid #374151; color: #D1D5DB; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">Bo5</span>
                    </div>
                </div>

                <div style="background-color: #0F1621; border: 1px solid #111827; border-radius: 8px; padding: 20px; cursor: pointer; transition: background-color 0.2s;"
                    onmouseover="this.style.backgroundColor='#111827'"
                    onmouseout="this.style.backgroundColor='#0F1621'">
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 16px;">
                        <h2 style="font-size: 17px; font-weight: 600; margin: 0; color: #F9FAFB; line-height: 1.4;">
                            Mixed Doubles U19</h2>
                        <span
                            style="background-color: #6B7280; color: #F9FAFB; font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 4px; text-transform: uppercase; white-space: nowrap;">Upcoming</span>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        <span
                            style="background-color: #111827; border: 1px solid #374151; color: #D1D5DB; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">Mixed</span>
                        <span
                            style="background-color: #111827; border: 1px solid #374151; color: #D1D5DB; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">Individual</span>
                        <span
                            style="background-color: #111827; border: 1px solid #374151; color: #D1D5DB; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">Bo3</span>
                    </div>
                </div>

                <div style="background-color: #0F1621; border: 1px solid #111827; border-radius: 8px; padding: 20px; cursor: pointer; transition: background-color 0.2s;"
                    onmouseover="this.style.backgroundColor='#111827'"
                    onmouseout="this.style.backgroundColor='#0F1621'">
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 16px;">
                        <h2 style="font-size: 17px; font-weight: 600; margin: 0; color: #F9FAFB; line-height: 1.4;">Team
                            Championship</h2>
                        <span
                            style="background-color: #22C55E; color: #0B0F14; font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 4px; text-transform: uppercase; white-space: nowrap;">Live</span>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        <span
                            style="background-color: #111827; border: 1px solid #374151; color: #D1D5DB; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">Mixed</span>
                        <span
                            style="background-color: #111827; border: 1px solid #374151; color: #D1D5DB; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">Team</span>
                        <span
                            style="background-color: #111827; border: 1px solid #374151; color: #D1D5DB; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">Bo5</span>
                    </div>
                </div> --}}

            </div>
        </main>



    </section>

</div>