<?php

namespace App\Listeners;
use Illuminate\Support\Facades\Log;
use App\Events\AppointmentCreated;

class AppointmentEventSubscriber
{
    /**
     * Handle Appointment Created Event
     */
    public function handleAppointmentCreated(AppointmentCreated $event)
    {
        // Test Log — proves subscriber is working
        Log::info('Appointment Event Subscriber Triggered', [
            'appointment_id' => $event->appointment->id,
            'status' => $event->appointment->status
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events)
    {
        $events->listen(
            AppointmentCreated::class,
            [AppointmentEventSubscriber::class, 'handleAppointmentCreated']
        );
    }
}
