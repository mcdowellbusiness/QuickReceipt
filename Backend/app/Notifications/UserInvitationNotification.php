<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInvitationNotification extends Notification
{
    use Queueable;

    protected $invitation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $roleText = $this->invitation->role === 'admin' ? 'Team Lead' : 'Team Member';
        $actionUrl = env('FRONTEND_URL', 'http://localhost:3000') . '/auth/register?token=' . $this->invitation->token;

        return (new MailMessage)
            ->subject('You\'ve been invited to join ' . $this->invitation->team->name . ' on QuickReceipt')
            ->greeting('Hello ' . $this->invitation->name . '!')
            ->line('You have been invited to join **' . $this->invitation->team->name . '** as a ' . $roleText . ' on QuickReceipt.')
            ->line('QuickReceipt helps teams manage budgets, track expenses, and process receipts efficiently.')
            ->line('**Team Details:**')
            ->line('• Team: ' . $this->invitation->team->name)
            ->line('• Role: ' . $roleText)
            ->line('• Organization: ' . $this->invitation->team->org->name)
            ->when($this->invitation->team->description, function ($mail) {
                return $mail->line('• Description: ' . $this->invitation->team->description);
            })
            ->line('To get started, click the button below to create your account and accept the invitation.')
            ->action('Accept Invitation & Create Account', $actionUrl)
            ->line('This invitation will expire on ' . $this->invitation->expires_at->format('M j, Y \a\t g:i A') . '.')
            ->line('If you did not expect this invitation, you can safely ignore this email.')
            ->line('Welcome to the team!')
            ->salutation('Best regards, The QuickReceipt Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'team_id' => $this->invitation->team->id,
            'team_name' => $this->invitation->team->name,
            'role' => $this->invitation->role,
            'invitation_type' => 'team_invitation'
        ];
    }
}
