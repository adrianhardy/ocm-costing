<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Illuminate\Console\Command;

class SetupCurrenciesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:currencies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create currencies';

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
        $currencies = [
            'RMB' => ['Renminbi', 0.11],
            'CAD' => ['CAD Dollar', 0.59],
            'USD' => ['US Dollar', 0.73],
            'EUR' => ['Euro', 0.85],
            'MXN' => ['Mexican Peso', 0.03]
        ];

        foreach ($currencies as $code => $defn) {
            $currency = Currency::where('code', $code)->first();
            if (!$currency) {
                $currency = new Currency([
                    'code' => $code
                ]);
            }
            $currency->fill([
                'name' => $defn[0],
                'default_fx_to_gbp' => $defn[1]
            ]);
            $currency->save();
        }
    }
}
