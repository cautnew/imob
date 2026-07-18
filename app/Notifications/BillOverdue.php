<?php

namespace App\Notifications;

use App\Models\Bill;
use Illuminate\Notifications\Notification;

class BillOverdue extends Notification
{
    public function __construct(public Bill $bill) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'bill_id' => $this->bill->id,
            'message' => sprintf(
                'Boleto #%d (%s) venceu em %s.',
                $this->bill->id,
                $this->bill->lease->property->title,
                $this->bill->due_date->format('d/m/Y'),
            ),
        ];
    }
}
