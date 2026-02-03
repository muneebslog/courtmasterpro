<?php

use Livewire\Component;
use App\Models\MatchGame;
use Livewire\Attributes\Layout;

new #[Layout('layouts::publicview')]
    class extends Component {
    public $matchId;

    public function mount($match)
    {
        $this->matchId = $match;
    }

    public function with()
    {
        $match = MatchGame::with(['teamA', 'teamB', 'sets.scores', 'round.event'])
            ->findOrFail($this->matchId);

        $allSets = $match->sets->sortBy('set_number');
        $latestSet = $allSets->last();

        // Determine current Visual Match (1-5) based on set number
        $currentVisualMatchNum = $latestSet ? ceil($latestSet->set_number / 3) : 1;

        // Filter for only sets in the current Visual Match
        $startSet = ($currentVisualMatchNum - 1) * 3 + 1;
        $endSet = $currentVisualMatchNum * 3;

        $visibleSets = $allSets->filter(function ($set) use ($startSet, $endSet) {
            return $set->set_number >= $startSet && $set->set_number <= $endSet;
        });

        return [
            'match' => $match,
            'visibleSets' => $visibleSets,
            'currentVisualMatchNum' => $currentVisualMatchNum,
            'winsA' => $match->visualMatchWinsForTeam($match->team_a_id),
            'winsB' => $match->visualMatchWinsForTeam($match->team_b_id),
        ];
    }
}; ?>


<div 
    style="margin:0;padding:0;box-sizing:border-box;background:#000;font-family:'Arial Black',Arial,sans-serif;overflow:hidden;">

    <div id="scoreboard-container" style="
        width:100vw;
        height:100vh;
        display:flex;
        flex-direction:column;
        padding:2vh 2vw;
        gap:2vh;
        background:#000;
    ">

        <!-- BACK BUTTON -->
        <button onclick="window.history.back()" style="
            position:fixed;
            top:10px;
            right:10px;
            padding:10px 15px;
            background:rgba(255,255,255,0.1);
            border:1px solid rgba(255,255,255,0.2);
            border-radius:8px;
            color:#fff;
            font-size:1.5vh;
            cursor:pointer;
            z-index:100;
        ">← BACK</button>

        <!-- FOOTER -->
        <div style="
            background:#fff;
            border:0.5vh solid #000;
            border-radius:1.5vh;
            padding:2vh 3vw;
            height:12vh;
            display:flex;
            align-items:center;
            justify-content:space-between;
        ">
            <div style="
                font-size:3vh;
                font-weight:900;
                color:#000;
                text-transform:uppercase;
                letter-spacing:0.2vw;
            ">
                {{ $match->round->event->name }}
                @if($match->round->event->default_discipline === 'mixed')
                    - MATCH {{ $currentVisualMatchNum }} OF 5
                @endif
            </div>

            <img src="{{ asset('img/sponser.jpeg') }}" alt="Sponsor" style="height:60px;">
        </div>

        <!-- SCOREBOARD -->
        <div style="
            flex:1;
            border:0.5vh solid #000;
            border-radius:1.5vh;
            display:flex;
            flex-direction:column;
            gap:2vh;
            background:#000;
            padding:2vh;
        ">

            <div style="flex:1;display:flex;flex-direction:column;gap:2vh;">

                <!-- TEAM A -->
                <div style="
                    flex:1;
                    background:linear-gradient(to right,#2a2a2a 0%,#1a1a1a 100%);
                    border:0.4vh solid #444;
                    border-radius:1.5vh;
                    display:flex;
                    align-items:center;
                    padding:0 3vw;
                ">
                    <div style="
                        width:10vh;
                        height:10vh;
                        border-radius:1vh;
                        margin-right:2vw;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        font-size:5vh;
                        background:linear-gradient(135deg,#2196f3 0%,#1565c0 100%);
                    ">🏸</div>

                    <div style="
                        color:#fff;
                        font-size:8vh;
                        font-weight:900;
                        letter-spacing:0.2vw;
                        text-transform:uppercase;
                        flex:1;
                    ">
                        {{ $match->teamA->display_name }}
                    </div>

                    <div style="display:flex;gap:1vw;align-items:center;">
                        <div style="
                            background:linear-gradient(135deg,#ffd700 0%,#ffa000 100%);
                            color:#000;
                            padding:1vh 1vw;
                            border-radius:1vh;
                            font-size:6vh;
                            font-weight:900;
                            min-width:6vw;
                            text-align:center;
                        ">
                            {{ $winsA }}
                        </div>

                        @foreach($visibleSets as $set)
                                            <div style="
                                                                        {{ $loop->last
                            ? 'background:linear-gradient(135deg,#d50000 0%,#8b0000 100%);color:#fff;font-size:15vh;min-width:9vw;'
                            : 'background:#333;color:#aaa;font-size:10vh;min-width:6vw;border:2px solid #444;' }}
                                                                        border-radius:1vh;
                                                                        font-weight:900;
                                                                        text-align:center;
                                                                        padding:0 10px;
                                                                    ">
                                                {{ $set->getPointsForTeam($match->team_a_id) }}
                                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- TEAM B -->
                <div style="
                    flex:1;
                    background:linear-gradient(to right,#2a2a2a 0%,#1a1a1a 100%);
                    border:0.4vh solid #444;
                    border-radius:1.5vh;
                    display:flex;
                    align-items:center;
                    padding:0 3vw;
                ">
                    <div style="
                        width:10vh;
                        height:10vh;
                        border-radius:1vh;
                        margin-right:2vw;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        font-size:5vh;
                        background:linear-gradient(135deg,#ff3d00 0%,#d50000 100%);
                    ">🏸</div>

                    <div style="
                        color:#fff;
                        font-size:8vh;
                        font-weight:900;
                        letter-spacing:0.2vw;
                        text-transform:uppercase;
                        flex:1;
                    ">
                        {{ $match->teamB->display_name }}
                    </div>

                    <div style="display:flex;gap:1vw;align-items:center;">
                        <div style="
                            background:linear-gradient(135deg,#ffd700 0%,#ffa000 100%);
                            color:#000;
                            padding:1vh 1vw;
                            border-radius:1vh;
                            font-size:6vh;
                            font-weight:900;
                            min-width:6vw;
                            text-align:center;
                        ">
                            {{ $winsB }}
                        </div>

                        @foreach($visibleSets as $set)
                                            <div style="
                                                                        {{ $loop->last
                            ? 'background:linear-gradient(135deg,#d50000 0%,#8b0000 100%);color:#fff;font-size:15vh;min-width:9vw;'
                            : 'background:#333;color:#aaa;font-size:10vh;min-width:6vw;border:2px solid #444;' }}
                                                                        border-radius:1vh;
                                                                        font-weight:900;
                                                                        text-align:center;
                                                                        padding:0 10px;
                                                                    ">
                                                {{ $set->getPointsForTeam($match->team_b_id) }}
                                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        <!-- FULLSCREEN -->
        <button onclick="document.documentElement.requestFullscreen()" style="
            position:fixed;
            bottom:10px;
            right:10px;
            width:45px;
            height:45px;
            border-radius:50%;
            background:#ff1744;
            border:2px solid #fff;
            color:#fff;
            font-size:20px;
            display:flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            opacity:0.5;
        ">⛶</button>
      


    </div>

  <script>
            (function () {
                function refreshScoreboard() {
                    fetch(window.location.href, {
                        cache: "no-store"
                    })
                        .then(r => r.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, "text/html");

                            const newContent = doc.querySelector('#scoreboard-container');
                            const current = document.querySelector('#scoreboard-container');

                            if (newContent && current) {
                                current.innerHTML = newContent.innerHTML;
                            }
                        })
                        .catch(() => {
                            // Android TV sometimes throws silent fetch errors — ignore
                        });
                }

                setInterval(refreshScoreboard, 1000);
            })();
        </script>
</div>