<?php

namespace App\Console\Commands;

use App\Models\DogTransfer;
use Illuminate\Console\Command;

class ExpireDogTransfers extends Command
{
    protected $signature = 'transfers:expire';
    protected $description = 'Mark expired dog transfers as expired';

    public function handle(): int
    {
        $count = DogTransfer::where('status','pending')
            ->where('expires_at','<=', now())
            ->update(['status' => 'expired']);

        $this->info("Expired {$count} transfers.");
        return self::SUCCESS;
    }
}
