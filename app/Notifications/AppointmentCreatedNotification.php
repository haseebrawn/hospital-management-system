<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    public function via($notifiable): array
    {
        return ['database', 'mail', 'broadcast']; //  database + email + Pusher
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Appointment Scheduled')
            ->line('A new appointment has been scheduled.')
            ->line('Patient: ' . $this->appointment->patient->name)
            ->line('Doctor: ' . $this->appointment->doctor->name)
            ->line('Schedule: ' . $this->appointment->scheduled_at)
            ->action('View Appointment', url('/'))
            ->line('Thank you for using our system!');
    }

    public function toArray($notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'patient_name' => $this->appointment->patient->name,
            'doctor_name' => $this->appointment->doctor->name,
            'scheduled_at' => $this->appointment->scheduled_at,
        ];
    }
    //  Real-Time Broadcast using Pusher
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'appointment_id' => $this->appointment->id,
            'patient_name' => optional($this->appointment->patient)->name ?? 'Unknown',
            'doctor_name' => optional($this->appointment->doctor)->name ?? 'Unassigned',
            'status' => $this->appointment->status,
            'created_at' => now()->toDateTimeString(),
            'message' => 'New appointment created successfully.'
        ]);
    }
}
