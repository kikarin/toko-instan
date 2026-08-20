<x-mail::message>
# Pesanan diterima, {{ $order->customer_name }}!

Pesanan **{{ $order->order_number }}** di **{{ $order->store?->name ?? 'Toko Instan' }}** sudah kami terima.

@if ($order->status === 'pending')
Silakan selesaikan pembayaran agar pesanan segera diproses.
@else
Pesanan Anda sedang kami proses.
@endif

@component('mail::button', ['url' => $trackingUrl])
Lacak Pesanan
@endcomponent

Simpan email ini — link di atas bisa dipakai untuk mengecek status pesanan tanpa login.

Salam,<br>
{{ $order->store?->name ?? config('app.name') }}
</x-mail::message>
