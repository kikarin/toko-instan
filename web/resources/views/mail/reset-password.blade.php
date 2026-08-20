<x-mail::message>
# Reset Kata Sandi

Kami menerima permintaan untuk mereset kata sandi akun Anda. Klik tombol di bawah untuk membuat kata sandi baru.

<x-mail::button :url="$resetUrl">
Reset Kata Sandi
</x-mail::button>

Tautan ini berlaku selama 60 menit. Jika Anda tidak merasa meminta reset ini, abaikan email ini dan kata sandi Anda tidak akan berubah.

Salam,<br>
{{ config('app.name') }}
</x-mail::message>
