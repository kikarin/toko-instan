<x-mail::message>
# Kode OTP Login

Gunakan kode berikut untuk masuk ke akun Anda:

# {{ $code }}

Kode berlaku selama 10 menit. Jangan bagikan kode ini kepada siapa pun.

Jika Anda tidak meminta kode ini, abaikan email ini.

Salam,<br>
{{ config('app.name') }}
</x-mail::message>
