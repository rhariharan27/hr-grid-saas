<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\User;

class ExpiryNotification extends Notification
{
  use Queueable;

  protected $user;
  protected $type;
  protected $days;

  public function __construct(User $user, $type, $days)
  {
    $this->user = $user;
    $this->type = $type;
    $this->days = $days;
  }

  /**
   * Change the delivery channel to 'database'.
   */
  public function via(object $notifiable): array
  {
    return ['database']; // Changed from ['mail']
  }

  /**
   * Define the data that gets stored in the database.
   */
  public function toArray(object $notifiable): array
  {
    $documentType = ucfirst($this->type);

    return [
      'message' => "The $documentType for {$this->user->getFullName()} is expiring in {$this->days} days.",
      'url' => route('employees.show', $this->user->id), // Link to the employee's page
      'icon' => $this->type === 'passport' ? 'bx-passport' : 'bxs-plane-alt' // An icon for the UI
    ];
  }
}
