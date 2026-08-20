<x-mail::message>
# Terima kasih, {{ $order->customer_name }}!

Pesanan **{{ $order->order_number }}** di **{{ $order->store?->name ?? 'Toko Instan' }}** telah berhasil dibayar. Berikut rincian pesanan Anda:

## Item Pesanan

| Produk | Jumlah | Harga |
| --- | --- | --- |
@foreach ($order->items as $item)
| {{ $item->name }} | {{ $item->qty }} | Rp {{ number_format((float) $item->total, 0, ',', '.') }} |
@endforeach

**Total Pembayaran: Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}**

@component('mail::button', ['url' => $trackingUrl])
Lacak Pesanan
@endcomponent

Pesanan Anda sedang diproses dan akan segera dikirim. Anda akan menerima email lain beserta nomor resi begitu pesanan dikirim oleh penjual.

Salam,<br>
{{ $order->store?->name ?? config('app.name') }}
</x-mail::message>