@component('mail::message')
# You’ve been invited to take ownership of {{ $transfer->dog->name }}

**From:** {{ $transfer->fromTeam->name }}  
**Dog:** {{ $transfer->dog->name }}  
**Includes:** {{ $transfer->count_evaluations }} evaluations, {{ $transfer->count_files }} files, {{ $transfer->count_notes }} notes

@component('mail::button', ['url' => $url])
Accept Transfer
@endcomponent

This link expires on **{{ $expires }}**.

If the button doesn’t work, use this code inside the app: **{{ $code }}**

Thanks,  
{{ config('app.name') }}
@endcomponent
