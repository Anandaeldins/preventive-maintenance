<?php

namespace App\Console\Commands;

use App\Models\PmSchedule;
use Illuminate\Console\Command;

class AutoRejectSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-reject-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto reject jadwal PM terlewat yang belum dikerjakan';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updated = PmSchedule::rejectOverdueWithoutInspeksi();

        $this->info("Auto reject selesai. Total jadwal ditolak: {$updated}");

        return self::SUCCESS;
    }
}

