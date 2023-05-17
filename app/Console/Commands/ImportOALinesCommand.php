<?php

namespace App\Console\Commands;

use App\Models\Import;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ImportOALinesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:oa-lines {import} {--skip-first-row} {--pretend}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import OA Lines from an uploaded CSV';


    protected $columns = [
        'item_number',
        'product_description',
        'barcode',
        'inner_qty',
        'case_weight_kg',
        'height_m',
        'length_m',
        'width_m',
        'units_per_case',
        'unit_weight_kg',
        'unit_height_m',
        'unit_length_m',
        'unit_width_m',
        'primary_sku',
        'oa_eta',
        'unit_price',
        'units_requested',
    ];

    protected $numericColumns =[
        'inner_qty',
        'case_weight_kg',
        'height_m',
        'length_m',
        'width_m',
        'units_per_case',
        'unit_weight_kg',
        'unit_height_m',
        'unit_length_m',
        'unit_width_m',
    ];


    /**
     * @var Logger
     */
    private $log;


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
        if (!$this->hasArgument('import')) {
            $this->error("import");
            return;
        }
        /** @var Import $import */
        $import = Import::query()->findOrFail($this->argument('import'));
        $csvPath = Storage::disk('imports')->path($import->path);
        $csv = new \SplFileObject($csvPath, 'r');
        /** @var SalesOrder $acknowledgement */
        $acknowledgement = SalesOrder::query()->findOrFail($import->config['oa_id']);

        $this->setupLogs($import);

        if($this->hasOption('skip-first-row')) {
            $csv->fgetcsv();
        }



        while(!$csv->eof()) {
            $row = $csv->fgetcsv();

            if (strlen(trim(implode('', $row))) == 0) {
                continue;
            }

            $row = array_combine($this->columns, array_map('trim', $row));


            // We want them to be able to leave these fields empty,
            // so we make sure that they aren't empty strings so they
            // won't be picked up by validation, but also won't throw
            // errors when we try to put a string into a number field in SQL
            foreach($this->numericColumns as $column) {
                if($row[$column] === '') {
                    $row[$column] = 0;
                }
            }


            $validator = Validator::make($row, [
                'item_number' => 'required',
                'product_description' => 'sometimes',
                'barcode' => 'sometimes|max:16',
                'inner_qty' => 'sometimes|numeric',
                'case_weight_kg' => 'sometimes|numeric',
                'height_m' => 'sometimes|numeric',
                'length_m' => 'sometimes|numeric',
                'width_m' => 'sometimes|numeric',
                'units_per_case' => 'sometimes|numeric',
                'unit_weight_kg' => 'sometimes|numeric',
                'unit_height_m' => 'sometimes|numeric',
                'unit_length_m' => 'sometimes|numeric',
                'unit_width_m' => 'sometimes|numeric',
                'primary_sku' => 'sometimes',
                'oa_eta' => 'sometimes|date',
                'unit_price' => 'required|numeric',
                'units_requested' => 'required|numeric',
            ]);

            if($validator->fails()) {

                foreach($validator->errors()->all() as $error) {
                    $this->log->error("{$row['item_number']} - $error");
                    $this->error("{$row['item_number']} - $error");
                }

                continue;
            };

            $line = new SalesOrderLine();
            $line->sales_order()->associate($acknowledgement);

            $line->fill([
                'item_number' => $row['item_number'],
                'product_description' => $row['product_description'],
                'name' => $row['product_description'],
                'barcode' => $row['barcode'],
                'oa_primary_sku' => $row['primary_sku'],
                'unit_price' => $row['unit_price'],
                'units_requested' => $row['units_requested'],
            ]);

            if($row['oa_eta'] !== '') {
                $line->oa_eta = Carbon::parse($row['oa_eta']);
            }

            // some fields are nullable, so we don't want them getting
            // set when they aren't present on the import, or we'll get
            // SQL errors
            foreach($this->numericColumns as $column) {
                if($row[$column] !== '') {
                    $line->{$column} = $row[$column];
                }
            }


            $this->comment('finding_product');


            $product = Product::query()->where('item_number', $row['item_number'])->first();

            if($product) {
                // something to be noted, in the event that the information
                // provided on the import (mostly stuff pertaining to dimensions)
                // it will be overwritten by what we have saved in for the PRODUCT,
                // by the OBSERVER.

                $this->log->info("Found product for # {$row['item_number']}, associating ");
                $line->product()->associate($product);
            } else {
                $this->log->info("Count not find product for item # {$row['item_number']}");
            }

            $line->total = $line->units_requested * $line->unit_price;
            $line->total_gbp = $line->total * $acknowledgement->fx_rate;
            $line->unit_price_gbp = $line->unit_price * $acknowledgement->fx_rate;


            if(!$this->option('pretend')) {
                $line->save();
            }
            $acknowledgement->touch();
        }
    }

    /**
     * @param Import $import
     */
    protected function setupLogs(Import $import): void
    {
        $logPath = Storage::disk('imports')->path($import->log_path);
        $stream = new StreamHandler(fopen($logPath, 'a'));
        $stream->setFormatter(new JsonFormatter());
        $log = new Logger('import', [$stream]);
        if ($this->output->isVeryVerbose()) {
            $log->pushHandler(new StreamHandler(fopen('php://stdout', 'w')));
        }
        $this->log = $log;
    }


}
