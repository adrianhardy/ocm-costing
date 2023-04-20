<?php

namespace App\Console\Commands;

use App\Events\ProductInformationChanged;
use App\Models\Import;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ImportUkProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:uk-products {import} {--skip-first-row} {--pretend}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Products for the UK side of the system';

    protected $columns = [
        'item_number',
        'article_number',
        'name',
        'barcode',
        'product_description',
        'primary_asin',
        'primary_sku',
        'unit_price_gbp',
        'uk_postage',
        'eu_postage',
        'worldwide_postage',
        'units_per_case',
        'inner_qty',
        'weight',
        'length',
        'width',
        'height',
        'unit_weight',
        'unit_length',
        'unit_width',
        'unit_height',
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
        $supplier = Supplier::query()->findOrFail($import->config['supplier_id']);


        $this->setupLogs($import);

        if ($this->option('skip-first-row')) {
            $this->log->info('Skipping first row...');
            $csv->fgetcsv();
        }

        while(!$csv->eof()) {
            $row = $csv->fgetcsv();

            if (strlen(trim(implode('', $row))) == 0) {
                continue;
            }

            $row = array_combine($this->columns, array_map('trim', $row));

            $validator = Validator::make($row, [
                'item_number'      => 'sometimes|nullable|min:0|max:64',
                'article_number'   => 'sometimes|nullable|min:0|max:64',
                'name'  => 'required|min:1|max:200',
                'barcode' => 'sometimes|nullable|max:32',
                'product_description' => 'sometimes|nullable|max:500',
                'unit_price_gbp' => 'sometimes|numeric',
                'uk_postage' => 'sometimes|in:small_letter,large_letter_package,package,dpd_small,dpd_large,parcel_force',
                'eu_postage' => 'sometimes|in:lightweight_airmail,standard_airmail,signed_for,parcel_force',
                'worldwide_postage' => 'sometimes|in:lightweight_airmail,standard_airmail,signed_for,parcel_force',
                'units_per_case' => 'sometimes|numeric',
                'inner_qty' => 'sometimes|numeric',
                'weight' => 'sometimes|numeric',
                'length' => 'sometimes|numeric',
                'width' => 'sometimes|numeric',
                'height' => 'sometimes|numeric',
                'unit_weight' => 'sometimes|numeric',
                'unit_length' => 'sometimes|numeric',
                'unit_width' => 'sometimes|numeric',
                'unit_height' => 'sometimes|numeric',
            ]);

            if($validator->fails()) {
                foreach($validator->errors()->all() as $error) {
                    $this->log->error("{$row['article_number']} / {$row['barcode']} - $error");
                }
                continue;
            }


            $product = Product::query()
                ->where('article_number', $row['article_number'])
                ->first();

            if ($product) {
                $this->log->info("{$row['article_number']} has an entry in the database, updating...");
            } else {
                $this->log->info("{$row['article_number']} does not have an entry in the database, creating...");
                $product = new Product();
            }

            $product->fill([
                'item_number' => $row['item_number'],
                'article_number' => $row['article_number'],
                'name' => $row['name'],
                'barcode' => $row['barcode'],
                'product_description' => $row['product_description'],
                'primary_asin' => $row['primary_asin'],
                'primary_sku' => $row['primary_sku'],
                'unit_price_gbp' => $row['unit_price_gbp'],
                'uk_postage_method' => $row['uk_postage'],
                'eu_postage_method' => $row['eu_postage'],
                'row_postage_method' => $row['worldwide_postage'],
                'units_per_case' => $row['units_per_case'],
                'inner_qty' => $row['inner_qty'],
                'height_m' => $row['height']?$row['height'] / 100:null,
                'length_m' => $row['length']?$row['length'] / 100:null,
                'width_m' => $row['width']?$row['width'] / 100:null,
                'case_weight_kg' => $row['weight'],
                'unit_weight_kg' => $row['unit_weight'],
                'unit_length_m' => $row['unit_length']?$row['unit_length'] / 100:null,
                'unit_width_m' => $row['unit_width']?$row['unit_width'] / 100:null,
                'unit_height_m' => $row['unit_height']?$row['unit_height'] / 100:null,
            ]);

            $product->supplier()->associate($supplier);


            if(!$this->hasArgument('pretend')) {
                $product->save();
                event(new ProductInformationChanged($product));
            }
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
