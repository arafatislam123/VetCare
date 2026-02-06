<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Appointment $appointment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Appointment Cancelled')
            ->line('An appointment has been cancelled.')
            ->line('Appointment ID: ' . $this->appointment->id)
            ->action('View Appointments', url('/appointments'))
            ->line('If you have any questions, please contact support.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->appointment->load(['pet', 'veterinarian.user', 'petOwner', 'timeSlot']);

        return [
            'type' => 'appointment_cancelled',
            'appointment_id' => $this->appointment->id,
            'pet_name' => $this->appointment->pet->name,
            'veterinarian_name' => $this->appointment->veterinarian->user->name,
            'pet_owner_name' => $this->appointment->petOwner->name,
            'time_slot' => [
                'start_time' => $this->appointment->timeSlot->start_time->toDateTimeString(),
                'end_time' => $this->appointment->timeSlot->end_time->toDateTimeString(),
            ],
            'status' => $this->appointment->status,
            'message' => 'An appointment has been cancelled.',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
