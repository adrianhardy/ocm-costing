<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class ImportUnitDimensionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:dimensions {csv} {--skip-first-row} {--pretend}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the csv file, importing the unit dimensions';

    protected $columns = [
        'article_number',
        'length_cm', 'width_cm', 'height_cm',
        'unit_length_cm', 'unit_width_cm', 'unit_height_cm',
        'case_weight_kg', 'unit_weight_kg',
        'units_per_case', 'inner_qty'
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->hasArgument('csv')) {
            $this->error("You must supply a CSV file path");
            return;
        }

        $csv = new \SplFileObject(realpath($this->argument('csv')), 'r');

        if ($this->option('skip-first-row')) {
            $csv->fgetcsv();
        }

//
//        $super = User::query()->role('super')->first();
//        Auth::login($super);

        while (!$csv->eof()) {
            $row = $csv->fgetcsv();

            if (strlen(trim(implode('', $row))) == 0) {
                continue;
            }

            $row = array_combine($this->columns, array_map('trim', $row));

            $products = Product::query()
                ->where('article_number', $row['article_number'])
                ->get();

            if ($products->count() > 1) {
                $this->comment("{$row['article_number']} has more than one entry in the database...");
                continue;
            }

            if ($products->isEmpty()) {
                $this->comment("{$row['article_number']} has NO matches in the database");
                continue;
            }

            $product = $products->first();

            $product->fill([
                'length_m' => $row['length_cm'] / 100,
                'width_m' => $row['width_cm'] / 100,
                'height_m' => $row['height_cm'] / 100,
                'unit_length_m' => $row['unit_length_cm'] / 100,
                'unit_width_m' => $row['unit_width_cm'] / 100,
                'unit_height_m' => $row['unit_height_cm'] / 100,
                'unit_weight_kg' => $row['unit_weight_kg'],
                'case_weight_kg' => $row['case_weight_kg'],
                'units_per_case' => $row['units_per_case'],
                'inner_qty' => $row['inner_qty']
            ]);

            $this->comment("Updating {$row['article_number']}...");
            if (!$this->option('pretend')) {
                $product->save();
            }
        }
    }
}
