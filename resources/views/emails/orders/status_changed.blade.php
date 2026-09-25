<x-mail::message>
# مرحباً {{ $order->user->name }}،

تم تحديث حالة طلبك رقم **#{{ $order->id }}** بنجاح إلى:
<x-mail::panel>
**{{ strtoupper($order->status->value) }}**
</x-mail::panel>

شكراً لتسوقك معنا،<br>
{{ config('app.name') }}
</x-mail::message>
