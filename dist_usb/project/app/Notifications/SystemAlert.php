<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemAlert extends Notification
{
    use Queueable;

    public $type;
    public $icon;
    public $title;
    public $description;
    public $url;

    /**
     * Create a new notification instance.
     */
    public function __construct($type, $icon, $title, $description, $url)
    {
        $this->type = $type;
        $this->icon = $icon;
        $this->title = $title;
        $this->description = $description;
        $this->url = $url;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'icon' => $this->icon,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
        ];
    }
}
