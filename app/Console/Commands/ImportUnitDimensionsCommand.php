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
        'article_number', 'description', 'length_cm', 'width_cm', 'height_cm',
        'measurement_unit', 'weight', 'weight_unit', 'postage_method'
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

        $super = User::query()->role('super')->first();
        Auth::login($super);

        while (!$csv->eof()) {
            $row = $csv->fgetcsv();
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
                'length_cm' => $row['length_cm'],
                'width_cm' => $row['width_cm'],
                'height_cm' => $row['height_cm'],
                'weight_g' => $row['weight'],
                'uk_postage_method' => $row['postage_method'],
                'eu_postage_method' => $row['postage_method'],
                'row_postage_method' => $row['postage_method'],
            ]);
            $this->comment("Updating {$row['article_number']}...");
            if (!$this->option('pretend')) {
                $product->save();
            }
        }
    }
}
