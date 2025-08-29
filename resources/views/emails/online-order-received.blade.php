<x-mail::message>
# New Online Order Received

Hello {{ $notifiable->name }},

A new online order has been received. Here are the details:
<x-mail::panel>
- **Order Ref:** {{ $order->reference }}
- **Customer Name:** {{ $order->customer?->name ?? '-' }}
- **Customer Email:** {{ $order->customer?->email ?? '-' }}
- **Total Amount:** Ksh. {{ number_format($order->total_amount, 2) }}
- **Order Status:** {{ $order->order_status }}
</x-mail::panel>

<x-mail::button :url="route('backoffice.pos', ['reference' => $order->reference])">
Check Order Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
