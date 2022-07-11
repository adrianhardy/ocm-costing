<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class ImportProductASINCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:asin {csv} {--skip-first-row} {--pretend}' ;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import asin and additional sku for a product';

    protected $columns = [
        'article_number',
        'primary_sku', 'primary_asin',
        'additional_skus', 'additional_asins'
    ];

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
        if (!$this->hasArgument('csv')) {
            $this->error("You must supply a CSV file path");
            return;
        }

        $csv = new \SplFileObject(realpath($this->argument('csv')), 'r');

        if ($this->option('skip-first-row')) {
            $csv->fgetcsv();
        }

        while(!$csv->eof()){
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
                'primary_sku' => $row['primary_sku'],
                'primary_asin' => $row['primary_asin'],
            ]);

            $product->additional_skus = explode(',', $row['additional_skus']);
            $product->additional_asins = explode(',', $row['additional_asins']);
            $this->comment("Updating {$row['article_number']}...");
            if (!$this->option('pretend')) {
                $product->save();
            }
        }
    }
}
