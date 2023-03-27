<?php

namespace App\Console;

use App\Console\Commands\CmdWorker;
use App\Console\Commands\GmailTest;
use App\Console\Commands\ImporterTest;
use App\Console\Commands\InitBuckets;
use App\Console\Commands\Jobs;
use App\Console\Commands\QueueWorker;
use Illuminate\Console\Scheduling\Schedule;
use Infrastructure\Persistence\Database\Fixtures;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        InitBuckets::class,

        // Test tools
        ImporterTest::class,
        GmailTest::class,

        // Workers
        CmdWorker::class,
        QueueWorker::class,

        // Sequences
        Jobs\ProcessActiveSequences::class,
        Jobs\SequencesReplyCheck::class,

        // Fixtures
        Fixtures\AddMissingCustomSalesOutcome::class,
        Fixtures\AddMissingSlugsCompanies::class,
        Fixtures\AddMissingSlugsContacts::class,
        Fixtures\FixStorageStructure::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
