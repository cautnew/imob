<?php

namespace App\Notifications;

use App\Models\Bill;
use Illuminate\Notifications\Notification;

class BillReceiptRejected extends Notification
{
    public function __construct(public Bill $bill, public ?string $reason) {}

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
            'message' => trim(sprintf(
                'Seu comprovante para o boleto #%d (%s) foi rejeitado. %s',
                $this->bill->id,
                $this->bill->lease->property->title,
                $this->reason ? "Motivo: {$this->reason}" : '',
            )),
        ];
    }
}
