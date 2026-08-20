<x-mail::message>
# Halo, {{ $order->customer_name }}!

Pesanan **{{ $order->order_number }}** di **{{ $order->store?->name ?? 'Toko Instan' }}** telah dikirim.

@if ($order->tracking_number)
No. Resi: **{{ $order->tracking_number }}**

@if ($order->tracking_courier === 'J&T Express')
Lacak di: https://www.jet.co.id/track/trace?no={{ $order->tracking_number }}
@elseif ($order->tracking_courier === 'SiCepat BEST')
Lacak di: https://www.sicepat.com/track?waybill={{ $order->tracking_number }}
@else
Lacak di: https://www.jne.co.id/en/tracking/trace?awb={{ $order->tracking_number }}
@endif
@else
Pesanan Anda sudah dikirim dan sedang dalam perjalanan.
@endif

@component('mail::button', ['url' => $trackingUrl])
Lacak Pesanan
@endcomponent

Terima kasih telah berbelanja di {{ $order->store?->name ?? 'Toko Instan' }}!

Salam,<br>
{{ $order->store?->name ?? config('app.name') }}
</x-mail::message>