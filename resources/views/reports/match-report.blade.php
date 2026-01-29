<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <title>Match Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
        }

        h1,
        h2,
        h3 {
            margin: 0;
            padding: 0;
        }

        .section {
            margin-bottom: 20px;
        }

        .box {
            border: 1px solid #ccc;
            padding: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f3f3f3;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .small {
            font-size: 10px;
            color: #555;
        }
    </style>
</head>

<body>
    <div class="section box">
        <h1 class="text-center">
            Match Report
        </h1>

        <p class="text-center small">
            Generated at: {{ $generatedAt }}
        </p>
    </div>
    <div class="section box">
        <h3>Match Details</h3>

        <table>
            <tr>
                <th>Tournament</th>
                <td>{{ $match->round->event->tournament->name }}</td>
            </tr>
            <tr>
                <th>Event</th>
                <td>{{ $match->round->event->name }}</td>
            </tr>
            <tr>
                <th>Round</th>
                <td>{{ $match->round->name }}</td>
            </tr>
            <tr>
                <th>Match No</th>
                <td>#{{ $match->id }}</td>
            </tr>
            <tr>
                <th>Court</th>
                <td>{{ $match->court_no ?? '—' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ ucfirst($match->status) }}</td>
            </tr>
        </table>
    </div>
    <div class="section box">
        <h3>Teams & Officials</h3>

        <table>
            <tr>
                <th>Team A</th>
                <td>
                    {{ $match->teamA->display_name }}<br>
                    <span class="small">
                        {{ $match->teamA->players->pluck('first_name')->join(', ') }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Team B</th>
                <td>
                    {{ $match->teamB->display_name }}<br>
                    <span class="small">
                        {{ $match->teamB->players->pluck('first_name')->join(', ') }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Umpire</th>
                <td>{{ $match->umpire ?? '—' }}</td>
            </tr>
            <tr>
                <th>Service Judge</th>
                <td>{{ $match->referee ?? '—' }}</td>
            </tr>
        </table>
    </div>
    <div class="section box">
        <h3>Match Result</h3>

        <p>
            Winner:
            <strong>
                {{ $match->winner_team_id === $match->team_a_id
    ? $match->teamA->display_name
    : $match->teamB->display_name }}
            </strong>
        </p>

        <p>
            @if($match->round->event->default_discipline === 'mixed')
                Final Matches Won:
            @else
                Final Score:
            @endif
            <strong>{{ $teamAWins }} – {{ $teamBWins }}</strong>
        </p>
        @if($match->round->event->default_discipline === 'mixed')
            {{-- Display as 5 matches for mixed/team events --}}
            @php
                $groupedSets = $completedSets->groupBy(function ($set) {
                    return ceil($set->set_number / 3);
                });
            @endphp

            @foreach($groupedSets as $matchNum => $setsInMatch)
                <h4 style="margin-top: 12px; margin-bottom: 6px;">Match {{ $matchNum }}</h4>
                <table>
                    <tr>
                        <th>Set</th>
                        <th>{{ $match->teamA->display_name }}</th>
                        <th>{{ $match->teamB->display_name }}</th>
                        <th>Winner</th>
                    </tr>
                    @foreach($setsInMatch as $set)
                        @php
                            $setInMatch = $match->getSetWithinVisualMatch($set->set_number);
                        @endphp
                        <tr>
                            <td>Set {{ $setInMatch }}</td>
                            <td>{{ $set->scores->firstWhere('team_id', $match->team_a_id)?->points ?? 0 }}</td>
                            <td>{{ $set->scores->firstWhere('team_id', $match->team_b_id)?->points ?? 0 }}</td>
                            <td>
                                {{ $set->winner_team_id === $match->team_a_id ? 'Team A' : 'Team B' }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endforeach
        @else
            {{-- Original singles/doubles display --}}
            <table>
                <tr>
                    <th>Set</th>
                    <th>{{ $match->teamA->display_name }}</th>
                    <th>{{ $match->teamB->display_name }}</th>
                    <th>Winner</th>
                </tr>

                @foreach ($completedSets as $set)
                    <tr>
                        <td>Set {{ $set->set_number }}</td>
                        <td>{{ $set->scores->firstWhere('team_id', $match->team_a_id)?->points ?? 0 }}</td>
                        <td>{{ $set->scores->firstWhere('team_id', $match->team_b_id)?->points ?? 0 }}</td>
                        <td>
                            {{ $set->winner_team_id === $match->team_a_id ? 'Team A' : 'Team B' }}
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>
    <div class="section box">
        <h3>Match Timeline</h3>

        <table>
            <tr>
                <th>Time</th>
                <th>Event</th>
            </tr>

            @foreach ($timelineEvents as $event)
                <tr>
                    <td>{{ $event->occurred_at->format('H:i') }}</td>
                    <td>
                        {{ ucfirst(str_replace('_', ' ', $event->type)) }}
                        @if($event->player)
                            — {{ $event->player->first_name }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
    @if ($incidents->isNotEmpty())
        <div class="section box">
            <h3>Incidents</h3>

            <table>
                <tr>
                    <th>Type</th>
                    <th>Details</th>
                </tr>

                @foreach ($incidents as $incident)
                    <tr>
                        <td>{{ strtoupper(str_replace('_', ' ', $incident->type)) }}</td>
                        <td>
                            {{ $incident->player?->first_name ?? 'Team Level' }}
                            (Set {{ $incident->set?->set_number }})
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif
    <div class="section box">
        <h3>Match Metadata</h3>

        <table>
            <tr>
                <th>Duration</th>
                <td>{{ $duration }}</td>
            </tr>
            <tr>
                <th>Scheduled Time</th>
                <td>{{ optional($match->scheduled_at)->format('H:i') ?? '—' }}</td>
            </tr>
            <tr>
                <th>Start Time</th>
                <td>{{ optional($match->started_at)->format('H:i') ?? '—' }}</td>
            </tr>
            <tr>
                <th>End Time</th>
                <td>{{ optional($match->ended_at)->format('H:i') ?? '—' }}</td>
            </tr>
            <tr>
                <th>Shuttles Used</th>
                <td>{{ $match->shuttlecock_used_count }}</td>
            </tr>
        </table>
    </div>

    <br><br>

    <table width="100%">
        <tr>
            <td class="text-center">
                ____________________<br>
                Umpire Signature
            </td>
            <td class="text-center">
                ____________________<br>
                Service Judge Signature
            </td>
        </tr>
    </table>

</body>

</html>