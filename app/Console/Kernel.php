<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // fix stocktakes:locations:updateo on RAFDEMO
        $schedule->command('stock:expunge-new')->everyThirtyMinutes();
        $schedule->command('telescope:prune')->daily();
        $schedule->command('shipments:complete')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('stocktakes:locations:update')
            ->dailyAt('02:00')
            ->withoutOverlapping();


        $live = app()->environment('production');

        // request a bunch of reports from amazon
        foreach (['gb', 'es', 'de', 'fr', 'it', 'us', 'nl', 'se', 'pl'] as $marketplace) {

            if (!$live) {
                continue;
            }

            foreach (['00:00', '06:30', '12:00', '17:00'] as $time) {
                $schedule
                    ->command('mws:levels:fetch --queue --marketplace=' . $marketplace)
                    ->dailyAt($time)
                    ->withoutOverlapping();
            }

            $schedule
                ->command('mws:invoices:upload --marketplace=' . $marketplace)
                ->everyThirtyMinutes()
                ->weekdays()
                ->withoutOverlapping();

            $schedule
                ->command('mws:removals:fetch --queue --marketplace=' . $marketplace)
                ->daily()
                ->withoutOverlapping();
        }

        if ($live) {
            // download the above reports once they've generated
            $schedule->command('mws:reports:check')->everyFifteenMinutes()->withoutOverlapping();
            $schedule->command('mws:levels:import')->hourly()->withoutOverlapping();
            $schedule->command('mws:removals:import')->hourly()->withoutOverlapping();

            // create invoices and then upload them
            $schedule->command('amazon:import:orders')->everyFifteenMinutes()->withoutOverlapping();
            $schedule->command('amazon:invoices:create')->everyTenMinutes()->withoutOverlapping();
            $schedule->command('mws:feed:check')->everyThirtyMinutes()->withoutOverlapping();
        }

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
