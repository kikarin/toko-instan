<x-mail::message>
# Verifikasi Email

Terima kasih telah mendaftar di {{ config('app.name') }}. Klik tombol di bawah untuk memverifikasi alamat email Anda.

<x-mail::button :url="$verifyUrl">
Verifikasi Email
</x-mail::button>

Tautan ini berlaku selama 60 menit. Jika Anda tidak merasa mendaftar, abaikan email ini.

Salam,<br>
{{ config('app.name') }}
</x-mail::message>
