<?php

namespace App\Console\Commands\Fixes;

use App\Models\DailyPricing;
use Illuminate\Console\Command;

class UpdateDailyPricingMarginCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:update-daily-pricing-margin {--pretend}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate all daily pricing\'s margins';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var DailyPricing[] $dailyPricing */
        DailyPricing::query()
            ->where('profit', '>', 0)
            ->whereLatestSubmission()
            ->chunk(100, function($pricings) {
                foreach ($pricings as $dp) {
                    $was = $dp->margin;
                    $dp->timestamps = false;
                    try {
                        $dp->recalculteProfitAndMargin();
                    } catch (\RuntimeException $ex) {
                        // nothing we can do if we can't recalc profit
                        continue;
                    }
                    $now = $dp->margin;
                    $this->comment("{$dp->product->item_number} profit {$dp->profit}, margin was $was, now $now");
                    if (!$this->option('pretend')) {
                        $dp->save();
                    }
                }
            });



    }
}
