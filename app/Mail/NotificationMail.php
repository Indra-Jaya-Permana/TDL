<?php

namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $notification;

    /**
     * Buat instance baru dari mail.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Bangun isi email.
     */
    public function build()
    {
        return $this->subject($this->notification->title)
                    ->markdown('emails.notification')
                    ->with([
                        'title' => $this->notification->title,
                        'messageBody' => $this->notification->message,
                        'type' => $this->notification->type,
                        'created_at' => $this->notification->created_at,
                    ]);
    }
}
