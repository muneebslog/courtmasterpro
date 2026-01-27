<?php

namespace App\Livewire\Pages\Tournaments;

use App\Models\Tournament;
use Livewire\Component;

class Show extends Component
{
    public Tournament $tournament;

    public function mount($id)
    {
        $this->tournament = Tournament::with('events')->findOrFail($id);
    }

    public function render()
    {
        return view('pages.tournaments.show');
    }
}
