<?php

namespace App\Notifications\SuperAdmin\Order;

use App\Models\SuperAdmin\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderSuccessSa extends Notification
{
  use Queueable;

  private Order $order;

  /**
   * Create a new notification instance.
   *
   * @param Order $order
   */
  public function __construct(Order $order)
  {
    $this->order = $order;
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
    return (new MailMessage)
      ->subject('Order Successful')
      ->greeting('Hello Super Admin,')
      ->line('A new order has been successfully processed.')
      ->line('**Order Details:**')
      ->line('**Order ID:** ' . $this->order->id)
      ->line('**User:** ' . $this->order->user->getFullName())
      ->line('**Plan:** ' . $this->order->plan->name)
      ->line('**Type:** ' . ucfirst(str_replace('_', ' ', $this->order->type)))
      ->line('**Total Amount:** $' . number_format($this->order->total_amount, 2))
      ->line('**Status:** ' . ucfirst($this->order->status))
      ->action('View Order', route('orders.show', $this->order->id))
      ->line('Thank you for your attention!');
  }

  /**
   * Get the array representation of the notification.
   *
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    return [
      'order_id' => $this->order->id,
      'user_id' => $this->order->user->id,
      'plan_id' => $this->order->plan->id,
      'total_amount' => $this->order->total_amount,
      'status' => $this->order->status,
    ];
  }
}
