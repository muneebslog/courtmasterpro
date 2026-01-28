<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Match Summary Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111;
        }

        h1 {
            font-size: 16px;
            margin-bottom: 6px;
        }

        .muted {
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: left;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .no-border td {
            border: none;
            padding: 4px 0;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .section {
            margin-top: 10px;
        }
    </style>
</head>

<body>

{{-- HEADER --}}
<h1>
    {{ $match->round->event->tournament->name }}
</h1>

<p class="muted">
    {{ $match->round->event->name }}
    — {{ $match->round->name }}
    | Match #{{ $match->id }}
    @if($match->court_no) | Court {{ $match->court_no }} @endif
    | {{ ucfirst($match->status) }}
</p>

<hr>

{{-- TEAMS & OFFICIALS --}}
<table class="no-border">
    <tr>
        <td width="50%">
            <strong>Team A:</strong>
            {{ $match->teamA->display_name }}
            <br>
            <span class="muted">
                {{ $match->teamA->players->pluck('first_name')->join(', ') }}
            </span>
        </td>
        <td width="50%">
            <strong>Team B:</strong>
            {{ $match->teamB->display_name }}
            <br>
            <span class="muted">
                {{ $match->teamB->players->pluck('first_name')->join(', ') }}
            </span>
        </td>
    </tr>
    <tr>
        <td>
            <strong>Umpire:</strong> {{ $match->umpire ?? '—' }}
        </td>
        <td>
            <strong>Referee:</strong> {{ $match->referee ?? '—' }}
        </td>
    </tr>
</table>

{{-- RESULT --}}
<div class="section">
    <strong>Winner:</strong>
    {{ $match->winner_team_id === $match->team_a_id
        ? $match->teamA->display_name
        : $match->teamB->display_name }}

    &nbsp;&nbsp;|&nbsp;&nbsp;

    <strong>Final Result:</strong>
    {{ $teamAWins }} – {{ $teamBWins }}
</div>

{{-- SET SCORES --}}
<div class="section">
    <table>
        <tr>
            <th width="15%">Set</th>
            <th width="25%">{{ $match->teamA->display_name }}</th>
            <th width="25%">{{ $match->teamB->display_name }}</th>
            <th width="15%">Winner</th>
        </tr>

        @foreach ($completedSets as $set)
        <tr>
            <td class="center">Set {{ $set->set_number }}</td>
            <td class="center">
                {{ $set->scores->firstWhere('team_id', $match->team_a_id)?->points ?? 0 }}
            </td>
            <td class="center">
                {{ $set->scores->firstWhere('team_id', $match->team_b_id)?->points ?? 0 }}
            </td>
            <td class="center">
                {{ $set->winner_team_id === $match->team_a_id ? 'A' : 'B' }}
            </td>
        </tr>
        @endforeach
    </table>
</div>

{{-- INCIDENTS --}}
<div class="section">
    <strong>Incidents:</strong>
    @if ($incidents->isEmpty())
        None
    @else
        <table>
            <tr>
                <th>Type</th>
                <th>Player / Team</th>
                <th>Set</th>
            </tr>
            @foreach ($incidents as $incident)
            <tr>
                <td>{{ strtoupper(str_replace('_',' ', $incident->type)) }}</td>
                <td>{{ $incident->player?->first_name ?? 'Team Level' }}</td>
                <td>{{ $incident->set?->set_number ?? '—' }}</td>
            </tr>
            @endforeach
        </table>
    @endif
</div>

{{-- METADATA --}}
<div class="section muted">
    Duration: {{ $duration }}
    • Scheduled: {{ optional($match->scheduled_at)->format('H:i') ?? '—' }}
    • Start: {{ optional($match->started_at)->format('H:i') ?? '—' }}
    • End: {{ optional($match->ended_at)->format('H:i') ?? '—' }}
    • Shuttles: {{ $match->shuttlecock_used_count }}
</div>

<br><br>

<table width="100%" class="no-border">
    <tr>
        <td class="center">
            ____________________<br>
            Umpire
        </td>
        <td class="center">
            ____________________<br>
            Referee
        </td>
        <td class="right muted">
            Generated: {{ $generatedAt }}
        </td>
    </tr>
</table>

</body>
</html>
