<x-mail::message>
# Langganan Premium hampir berakhir

Paket Premium toko kamu berakhir pada **{{ $endsAt }}**. Perpanjang sekarang supaya settlement langsung dan withdraw tanpa biaya tetap aktif.

<x-mail::button :url="$renewUrl">
Perpanjang Premium
</x-mail::button>

Salam,<br>
{{ config('app.name') }}
</x-mail::message>
