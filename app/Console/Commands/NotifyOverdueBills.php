<?php

namespace App\Console\Commands;

use App\Enums\BillStatus;
use App\Models\Bill;
use App\Notifications\BillOverdue;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

#[Signature('bills:notify-overdue')]
#[Description('Notify company users about bills that just became overdue')]
class NotifyOverdueBills extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Bill::query()
            ->where('status', BillStatus::Pending)
            ->whereDate('due_date', '<', today())
            ->whereNull('overdue_notified_at')
            ->with('company.users')
            ->each(function (Bill $bill): void {
                Notification::send($bill->company->users, new BillOverdue($bill));

                $bill->update(['overdue_notified_at' => now()]);
            });
    }
}
