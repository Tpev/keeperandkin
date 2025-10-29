{{-- resources/views/dogs/evaluations/show.blade.php --}}
@php
    $KK_NAVY    = '#03314C';
    $KK_BLUE    = '#076BA8';
    $KK_BLUE_ALT= '#DAEEFF';
    $KK_DIVIDER = '#E2E8F0';
    $SCALE_OK   = '#94A3B8'; $SCALE_RED='#DC2626'; $SCALE_ORANGE='#F97316'; $SCALE_YELLOW='#FFCC00'; $SCALE_GREEN='#16A34A';

    $colorFor = function($v) use ($SCALE_OK,$SCALE_RED,$SCALE_ORANGE,$SCALE_YELLOW,$SCALE_GREEN){
        if (!is_numeric($v)) return $SCALE_OK;
        $v = (int) $v;
        if ($v <= 25) return $SCALE_RED;
        if ($v <= 50) return $SCALE_ORANGE;
        if ($v <= 75) return $SCALE_YELLOW;
        return $SCALE_GREEN;
    };
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $dog->name }} — Evaluation {{ $evaluation->created_at?->format('M d, Y') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root{ --navy: {{ $KK_NAVY }}; --blue: {{ $KK_BLUE }}; --blueAlt: {{ $KK_BLUE_ALT }}; --divider: {{ $KK_DIVIDER }}; }
        *{ box-sizing: border-box; }
        body{ margin:0; font-family: Raleway, system-ui, -apple-system, Segoe UI, Roboto, sans-serif; color: var(--navy); background:#fff; }
        h1,h2,h3{ font-family: "Playfair Display", serif; margin:0; }
        .wrap{ max-width: 1000px; margin: 24px auto 64px; padding: 0 16px; }
        .header{ display:flex; align-items:center; justify-content:space-between; gap:16px; border-bottom:1px solid var(--divider); padding-bottom:12px; }
        .actions a, .actions button{ display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border:1px solid var(--blue); color: var(--blue); background:#fff; text-decoration:none; font-weight:600; cursor:pointer; }
        .muted{ color:#475569; }
        .grid2{ display:grid; grid-template-columns: 1fr 1fr; gap:16px; }
        .card{ border:1px solid var(--divider); background:#fff; }
        .card .hd{ background: var(--blueAlt); padding:10px 12px; border-bottom:1px solid var(--divider); font-weight:700; }
        .card .bd{ padding:12px; }
        .score{ margin-bottom:10px; }
        .bar{ height:10px; width:100%; background: var(--divider); border:1px solid var(--divider); }
        .bar > span{ display:block; height:100%; }
        table{ width:100%; border-collapse: collapse; }
        th, td{ text-align:left; padding:10px 8px; border-top:1px solid var(--divider); vertical-align: top; }
        thead th{ background: var(--blueAlt); border-top:none; }
        .small{ font-size:12px; }
        .foot{ margin-top:32px; text-align:center; color:#64748b; font-size:12px; }
        @media (max-width: 720px){ .grid2{ grid-template-columns: 1fr; } }
        @media print{ .actions{ display:none } body{ background:#fff } }
    </style>
</head>
<body>
<div class="wrap">

    <div class="header">
        <div>
            <h1 style="font-size:28px; line-height:1.2;">{{ $dog->name }} — Evaluation Details</h1>
            <div class="muted small">
                Date: {{ $evaluation->created_at?->format('M d, Y') }} · Evaluator: {{ $evaluation->evaluator_name ?? '—' }}
            </div>
        </div>
        <div class="actions">
            <a href="{{ route('dogs.show', $dog) }}">← Back to Dog</a>
            <button onclick="window.print()">Print</button>
        </div>
    </div>

    <div class="grid2" style="margin-top:16px;">
        <div class="card">
            <div class="hd">Summary Scores</div>
            <div class="bd">
                @foreach($displayScores as $label => $value)
                    @php
                        $width = is_numeric($value) ? max(0, min(100, (int)$value)) : 0;
                        $clr   = $colorFor($value);
                        $txt   = is_numeric($value) ? ($value.' / 100') : '—';
                    @endphp
                    <div class="score">
                        <div class="small" style="margin-bottom:6px; color:#334155; font-weight:600;">{{ $label }}</div>
                        <div class="bar"><span style="width: {{ $width }}%; background: {{ $clr }};"></span></div>
                        <div class="small" style="text-align:right; color: {{ $clr }}; margin-top:4px; font-weight:700;">{{ $txt }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="hd">Meta</div>
            <div class="bd">
                <div class="small" style="margin-bottom:6px;"><strong>Dog:</strong> {{ $dog->name }} (ID #{{ $dog->id }})</div>
                <div class="small" style="margin-bottom:6px;"><strong>Breed:</strong> {{ $dog->breed ?? '—' }}</div>
                <div class="small" style="margin-bottom:6px;"><strong>Sex / Age:</strong> {{ $dog->sex ?? '—' }} / {{ $dog->age ?? '—' }}</div>
                <div class="small"><strong>Evaluation ID:</strong> {{ $evaluation->id }}</div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:16px;">
        <div class="hd">Questions & Answers</div>
        <div class="bd" style="padding:0;">
            @if (empty($answers))
                <div class="small" style="padding:12px 12px 16px; color:#334155;">No answers recorded for this evaluation.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th style="width:45%;">Question</th>
                            <th style="width:25%;">Answer</th>
                            <th style="width:30%;">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($answers as $row)
                            <tr>
                                <td>{{ $row['question'] ?? '' }}</td>
                                <td>{{ $row['answer'] ?? '' }}</td>
                                <td>{{ $row['notes'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="foot">
        Keeper & Kin — Evaluation generated {{ now()->format('M d, Y, H:i') }}
    </div>
</div>
</body>
</html>
