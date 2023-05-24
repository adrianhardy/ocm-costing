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
    protected $signature = 'import:products:uk {import} {--skip-first-row} {--pretend}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Products for the UK side of the system';

    protected $columns = [
        'primary_sku',
        'item_number',
        'description',
        'unit_price_gbp'
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

            // make sure there's at MOST n columns in this CSV ...
            $row = array_slice($row, 0, count($this->columns));
            $row = array_combine($this->columns, array_map('trim', $row));

            $validator = Validator::make($row, [
                'primary_sku'       => 'required|min:0|max:64',
                'item_number'       => 'required|min:0|max:64',
                'description'       => 'required|min:1|max:200',
                'unit_price_gbp'    => 'required|numeric'
            ]);

            if($validator->fails()) {
                foreach($validator->errors()->all() as $error) {
                    $this->log->error("{$row['primary_sku']} / {$row['item_number']} - $error");
                }
                continue;
            }

            $product = Product::query()
                ->where('primary_sku', $row['primary_sku'])
                ->first();

            if ($product) {
                $this->log->info("{$row['item_number']} has an entry in the database, updating...");
            } else {
                $this->log->info("{$row['item_number']} does not have an entry in the database, creating...");
                $product = new Product();
            }


            $product->fill([
                'item_number'    => $row['item_number'],
                'article_number' => $row['item_number'],
                'name'           => $row['description'],
                'primary_sku'    => $row['primary_sku'],
                'unit_price_gbp' => $row['unit_price_gbp']
            ]);

            $product->supplier()->associate($supplier);

            if(!$this->hasArgument('pretend')) {
                $product->save();
                event(new ProductInformationChanged($product));
            }

            if ($product->wasRecentlyCreated) {
                $product->touch(); // trigger daily pricing build up
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
