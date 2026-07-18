<?php

namespace App\Notifications;

use App\Models\Bill;
use Illuminate\Notifications\Notification;

class BillReceiptUploaded extends Notification
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
                'Novo comprovante enviado para o boleto #%d (%s), aguardando aprovação.',
                $this->bill->id,
                $this->bill->lease->property->title,
            ),
        ];
    }
}
