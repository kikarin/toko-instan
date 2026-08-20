<x-mail::message>
# Langganan Premium berakhir

Paket Premium sudah habis. Toko kamu kembali ke **Free**: escrow + biaya withdraw Rp5.000.

Upgrade lagi kapan saja dari halaman langganan.

<x-mail::button :url="$renewUrl">
Upgrade ke Premium
</x-mail::button>

Salam,<br>
{{ config('app.name') }}
</x-mail::message>
