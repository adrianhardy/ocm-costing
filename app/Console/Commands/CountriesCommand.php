<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Currency;
use Illuminate\Console\Command;

class CountriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:countries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $json = file_get_contents(
            'https://raw.githubusercontent.com/lukes/ISO-3166-Countries-with-Regional-Codes/master/slim-2/slim-2.json'
        );
        $eur = Currency::query()->where('code', 'EUR')->first();
        $gbp = Currency::query()->where('code', 'GBP')->first();
        $usd = Currency::query()->where('code', 'USD')->first();
        $cad = Currency::query()->where('code', 'CAD')->first();
        $mxn = Currency::query()->where('code', 'MXN')->first();

        $countries = json_decode($json, true);
        foreach($countries as $country) {
            $cn = Country::query()
                ->where('iso_2_code', $country['alpha-2'])
                ->first();

            $vat = 0;
            $eu = 'AT,BE,BG,HR,CY,CZ,DK,EE,FI,FR,DE,GR,HU,IE,IT,LV,LT,LU,MT,NL,PL,PT,RO,SK,SI,ES,SE,GB';
            if (in_array($country['alpha-2'], explode(',', $eu))) {
                $vat = 20;
            }

            if (!$cn) {
                $cn = new \App\Models\Country([
                    'name' => $country['name'],
                    'import_duty' => 0,
                    'export_duty' => 0,
                    'vat_rate' => $vat,
                    'iso_2_code' => $country['alpha-2']
                ]);
            }

            if ($vat >= 20 && $cn->iso_2_code != 'GB') {
                $cn->currency()->associate($eur);
            }

            if ($cn->iso_2_code == 'GB') {
                $cn->currency()->associate($gbp);
            }

            if ($cn->iso_2_code == 'US') {
                $cn->currency()->associate($usd);
            }

            if ($cn->iso_2_code == 'MX') {
                $cn->currency()->associate($mxn);
            }

            if ($cn->iso_2_code == 'CA') {
                $cn->currency()->associate($cad);
            }


            $cn->save();

        }
        $this->comment("Completed");
    }
}
