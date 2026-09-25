<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Mail\OrderStatusChangedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderStatusUpdated $event): void
    {
        // إرسال الإيميل لبريد العميل المرتبط بالطلب
        if ($event->order->user && $event->order->user->email) {
            Mail::to($event->order->user->email)->send(
                new OrderStatusChangedMail($event->order)
            );
        }
    }
}
