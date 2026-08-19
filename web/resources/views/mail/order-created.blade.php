@component('mail::message')
# Pesanan baru

Ada pesanan **{{ $orderNumber }}** dari {{ $customer }}.

Total: **{{ $total }}**

@component('mail::button', ['url' => $url])
Lihat pesanan
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
