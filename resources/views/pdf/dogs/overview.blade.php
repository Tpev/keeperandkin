@php
    use Illuminate\Support\Str;

    $KK_NAVY     = $palette['NAVY'] ?? '#03314C';
    $KK_BLUE     = $palette['BLUE'] ?? '#076BA8';
    $KK_BLUE_ALT = $palette['BLUE_ALT'] ?? '#DAEEFF';
    $KK_DIVIDER  = $palette['DIVIDER'] ?? '#E2E8F0';
    $KK_DANGER   = $palette['DANGER'] ?? '#DC2626';
    $OK          = $palette['OK'] ?? '#94A3B8';
    $SCALE_RED    = $palette['DANGER'] ?? '#DC2626';
    $SCALE_ORANGE = $palette['ORANGE'] ?? '#F97316';
    $SCALE_YELLOW = $palette['YELLOW'] ?? '#FFCC00';
    $SCALE_GREEN  = $palette['GREEN'] ?? '#16A34A';

    $name       = $dog->name ?? 'Unnamed';
    $breed      = $dog->breed ?? 'Mixed';
    $sex        = $dog->sex ? Str::ucfirst($dog->sex) : '—';
    $age        = $dog->age ? $dog->age.' yrs' : '—';
    $intakeDate = $dog->created_at ? $dog->created_at->format('M d, Y') : now()->format('M d, Y');
    $approxDob  = $dog->approx_dob ? \Illuminate\Support\Carbon::parse($dog->approx_dob)->format('M d, Y') : '—';
    $fixedHuman = is_null($dog->fixed) ? 'Unknown' : ($dog->fixed ? 'Yes' : 'No');

    $latest = $latestEval ?? null;
    $cats   = (array)($latest?->category_scores ?? []);
    $pick = function(array $keys) use ($cats) { foreach ($keys as $k) if (array_key_exists($k, $cats)) return (int) $cats[$k]; return null; };
    $cc = $pick(['Comfort & Confidence','Confidence','comfort_confidence']);
    $so = $pick(['Sociability','Social','sociability']);
    $tr = $pick(['Trainability','trainability']);

    $colorFor = function($v) use ($SCALE_RED,$SCALE_ORANGE,$SCALE_YELLOW,$SCALE_GREEN,$OK) {
        if (!is_numeric($v)) return $OK;
        $v = (int)$v;
        if ($v <= 25) return $SCALE_RED;
        if ($v <= 50) return $SCALE_ORANGE;
        if ($v <= 75) return $SCALE_YELLOW;
        return $SCALE_GREEN;
    };
    $barWidth  = fn($v) => is_numeric($v) ? max(0, min(100,(int)$v)) : 0;
    $valOrDash = fn($v) => is_numeric($v) ? $v : '—';

    $redFlags = is_array($latest?->red_flags ?? null) ? $latest->red_flags : [];
    $notes    = $dog->description ? Str::limit(trim(strip_tags($dog->description)), 220) : null;

    $fieldsRaw = array_values(array_filter([
        ['label' => 'Location',             'value' => $dog->location],
        ['label' => 'Approx. Date of Birth','value' => $approxDob !== '—' ? $approxDob : null],
        ['label' => 'Altered',              'value' => $fixedHuman],
        ['label' => 'Color',                'value' => $dog->color],
        ['label' => 'Size',                 'value' => $dog->size],
        ['label' => 'Microchip',            'value' => $dog->microchip],
        ['label' => 'Heartworm',            'value' => $dog->heartworm],
        ['label' => 'FIV/L',                'value' => $dog->fiv_l],
        ['label' => 'FLV',                  'value' => $dog->flv],
        ['label' => 'Housetrained?',        'value' => $dog->housetrained],
        ['label' => 'Good with Dogs?',      'value' => $dog->good_with_dogs],
        ['label' => 'Good with Cats?',      'value' => $dog->good_with_cats],
        ['label' => 'Good with Children?',  'value' => $dog->good_with_children],
        ['label' => 'Intake',               'value' => $intakeDate],
    ], fn($f) => filled($f['value']) && $f['value'] !== '—'));

    $fields = array_slice($fieldsRaw, 0, 12);
    $rows = [];
    for ($i=0; $i<count($fields); $i+=2) { $rows[] = array_slice($fields, $i, 2); }

    $cell = function(string $label, $value, int $colspan = 1) {
        $v = e($value);
        return <<<HTML
            <td colspan="{$colspan}" style="border:1px solid #E2E8F0; padding:3px 6px; vertical-align:top;">
                <div style="font-size:7.8pt; text-transform:uppercase; letter-spacing:.03em; color:#334155; margin-bottom:1px;">{$label}</div>
                <div style="font-size:9pt;">{$v}</div>
            </td>
        HTML;
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $name }} — Overview</title>
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: {{ $KK_NAVY }}; font-size: 9pt; line-height: 1.18; }
        h1 { margin: 0 0 1px; font-size: 17pt; }
        .muted { color:#475569; }
        .tag { display:inline-block; font-size:8.8pt; padding:1px 5px; border:1px solid {{ $KK_DIVIDER }}; border-radius:3px; background:#F8FAFC; color:#374151; }
        .tag-red { background: {{ $KK_DANGER }}; color:#fff; border-color: {{ $KK_DANGER }}; }
        .card { border: 1px solid {{ $KK_DIVIDER }}; border-radius: 5px; page-break-inside: avoid; }
        .ch { background: {{ $KK_BLUE_ALT }}; padding: 4px 6px; border-bottom: 1px solid {{ $KK_DIVIDER }}; font-size: 10.5pt; font-weight:700; }
        .cb { padding: 5px 6px; }
        .bar { height:5px; width:100%; border:1px solid {{ $KK_DIVIDER }}; }
        table { border-collapse: collapse; width: 100%; }
        .no-break { page-break-inside: avoid; }

        .photo-wrap {
            width: 210px; height: 210px;
            border:1px solid {{ $KK_DIVIDER }};
            border-radius:5px;
            overflow:hidden;
            margin: 0 auto;
        }
        img.photo {
            width:100%; height:100%;
            object-fit: cover;
            object-position: center;
            display:block;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 8mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #64748B;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <table style="margin-bottom:4px;">
        <tr>
            <td style="width:68%; vertical-align:bottom; text-align:left;">
                <h1>{{ $name }}</h1>
                <div class="muted" style="font-size:10pt;">
                    {{ $breed }} · {{ $sex }} · {{ $age }}
                </div>
            </td>
            <td style="width:32%; text-align:right; vertical-align:middle;">
                <img src="{{ $qrFileUri }}" alt="QR" style="width:100px; height:100px; display:inline-block;">
                <div style="font-size:8pt; color:#475569; margin-top:1px;">Scan for profile</div>
            </td>
        </tr>
    </table>

    <!-- Two-column layout -->
    <table class="no-break">
        <tr>
            <!-- LEFT -->
            <td style="width:36%; vertical-align:top; padding-right:6px;">
                <table class="card no-break" style="margin-bottom:5px;">
                    <tr><td class="cb" style="padding:4px; text-align:center;">
                        <div class="photo-wrap">
                            @if(!empty($photoFileUri))
                                <img class="photo" src="{{ $photoFileUri }}" alt="{{ $name }}">
                            @else
                                <div style="width:100%;height:100%;background:#e5e7eb;color:#6b7280;display:flex;align-items:center;justify-content:center;">
                                    No photo
                                </div>
                            @endif
                        </div>
                    </td></tr>
                </table>

                <table class="card no-break" style="margin-bottom:5px;">
                    <tr><td class="ch">Scorecard</td></tr>
                    <tr><td class="cb" style="padding-top:4px;">
                        <table style="width:100%;">
                            @foreach([['Comfort & Confidence',$cc],['Sociability',$so],['Trainability',$tr]] as [$lbl,$val])
                                <tr>
                                    <td style="padding:3px 0;">
                                        <table style="width:100%;">
                                            <tr>
                                                <td style="font-weight:600; font-size:9.6pt;">{{ $lbl }}</td>
                                                <td style="text-align:right; font-size:9.6pt;"><strong>{{ $valOrDash($val) }}</strong>/100</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <div class="bar">
                                                        <div style="height:100%; width:{{ $barWidth($val) }}%; background: {{ $colorFor($val) }};"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </td></tr>
                </table>

                <table class="card no-break">
                    <tr><td class="ch">Alerts & Flags</td></tr>
                    <tr><td class="cb">
                        @if(empty($redFlags))
                            <span class="tag">No red flags</span>
                        @else
                            @foreach($redFlags as $rf)
                                <span class="tag tag-red" style="margin-right:4px; margin-bottom:3px; display:inline-block;">
                                    {{ \Illuminate\Support\Str::headline($rf) }}
                                </span>
                            @endforeach
                        @endif
                    </td></tr>
                </table>
            </td>

            <!-- RIGHT -->
            <td style="width:64%; vertical-align:top;">
                <table class="card no-break" style="margin-bottom:5px;">
                    <tr><td class="ch">Profile</td></tr>
                    <tr><td class="cb" style="padding:0;">
                        <table style="width:100%;">
                            @foreach($rows as $rIndex => $row)
                                @php $count = count($row); @endphp
                                <tr>
                                    @foreach($row as $i => $f)
                                        @php $isLastCell = ($i === $count - 1); $colspan = ($isLastCell && $count < 2) ? (2 - ($count - 1)) : 1; @endphp
                                        {!! $cell($f['label'], $f['value'], $colspan) !!}
                                    @endforeach
                                </tr>
                            @endforeach
                        </table>
                    </td></tr>
                </table>

                <table class="card no-break">
                    <tr><td class="ch">Notes</td></tr>
                    <tr><td class="cb" style="height: 110px; overflow: hidden;">
                        @if($notes) {{ $notes }} @else <span class="muted">—</span> @endif
                    </td></tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        KeeperandKin.com — Automatically generated on {{ now()->format('F j, Y \\a\\t H:i') }}
    </div>
</body>
</html>
