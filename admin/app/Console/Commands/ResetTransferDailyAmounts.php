<?php

namespace App\Console\Commands;

use App\Models\TransferAccount;
use Illuminate\Console\Command;

class ResetTransferDailyAmounts extends Command
{
    protected $signature = 'transfer:reset-daily';
    protected $description = 'Remet à zéro le cumul journalier de tous les comptes de transfert';

    public function handle(): int
    {
        $n = TransferAccount::query()->update(['current_daily_amount' => 0]);
        $this->info("{$n} compte(s) réinitialisé(s).");
        return self::SUCCESS;
    }
}
