<?php

namespace App\Console\Commands;

use App\Models\Prize;
use Illuminate\Console\Command;

class UpdateDailyStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-daily-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Fetch records where expiration date has passed and status is not expired
        $expiredItems = Prize::where('expiration', '<', now())
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        $this->info("Updated {$expiredItems} items to expired.");
    }
}
