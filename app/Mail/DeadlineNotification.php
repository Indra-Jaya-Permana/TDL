<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DeadlineNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $taskTitle;
    public $taskUrl;
    public $dueDate;
    public $daysLeft;

    public function __construct($name, $taskTitle, $taskUrl, $dueDate, $daysLeft = 0)
    {
        $this->name = $name;
        $this->taskTitle = $taskTitle;
        $this->taskUrl = $taskUrl;
        $this->dueDate = $dueDate;
        $this->daysLeft = $daysLeft;
    }

    public function build()
    {
        $subject = $this->daysLeft > 0 
            ? "⏰ Deadline H-{$this->daysLeft} - {$this->taskTitle}"
            : "🚨 Deadline Hari Ini - {$this->taskTitle}";

        return $this->subject($subject)
            ->markdown('emails.deadline-notification');
    }
}