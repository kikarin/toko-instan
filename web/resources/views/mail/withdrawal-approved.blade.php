@component('mail::message')
# Penarikan disetujui

Penarikan sebesar **{{ $amount }}** sudah disetujui. Saldo akan ditransfer sesuai proses admin.

@component('mail::button', ['url' => $url])
Buka dompet
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
