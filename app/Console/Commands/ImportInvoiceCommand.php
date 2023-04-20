<?php

namespace App\Console\Commands;

use App\Models\Import;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Services\InvoiceService;
use App\Services\PricingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ImportInvoiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:invoice {import} {--skip-first-row} {--pretend}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an invoice and its lines from an imported CSV';

    protected $columns = [
        'item_number',
        'barcode',
        'unit_price',
        'quantity',
        'pricing_priority'
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
    public function handle(InvoiceService $invoiceService, PricingService $pricingService)
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
        $reference = $import->config['reference'];

        $this->setupLogs($import);

        if($this->hasOption('skip-first-row')) {
            $this->comment('skipping first row');
            $csv->fgetcsv();
        }

        DB::beginTransaction();

        $invoice = new Invoice();
        $invoice->sales_order()->associate($acknowledgement);
        $invoice->reference = $reference;
        $invoice->creator()->associate(auth()->user());

        $invoice->save();

        $this->log->info("Creating Invoice with reference '$reference'");

        $canCreate = true;

        while(!$csv->eof()) {
            $row = $csv->fgetcsv();

            if (strlen(trim(implode('', $row))) == 0) {
                continue;
            }

            $row = array_combine($this->columns, array_map('trim', $row));

            $validator = Validator::make($row, [
                'item_number' => 'required',
                'barcode' => 'required',
                'unit_price' => 'required|numeric',
                'quantity' => 'required|numeric',
                'pricing_priority' => 'sometimes|in:low,medium,high'
            ]);

            if($validator->fails()) {
                foreach($validator->errors()->all() as $error) {
                    $this->log->error("{$row['item_number']} - $error");
                    $this->error("{$row['item_number']} - $error");
                }
                continue;
            }

            $salesOrderLine = SalesOrderLine::query()
                ->where('sales_order_id', $acknowledgement->id)
                ->whereHas('product', function ($query) use ($row) {
                    return $query
                        ->where('item_number', $row['item_number'])
                        ->orWhere('barcode', $row['barcode']);
                })
                ->first();

            if(!$salesOrderLine) {
                $this->log->error("Could not find line for # {$row['item_number']}");
                $this->error("Could not find line for # {$row['item_number']}");
                $canCreate = false;
                continue;
            }

            $invoiceLine = new InvoiceLine();
            $invoiceLine->invoice()->associate($invoice);
            $invoiceLine->sales_order_line()->associate($salesOrderLine);

            $invoiceLine->fill([
                'unit_price' => $row['unit_price'],
                'qty' => $row['quantity'],
                'pricing_priority' => $row['pricing_priority'] !== ''?$row['pricing_priority']:'low'
            ]);

            $invoiceLine->save();
            $this->log->info("Created Invoice Line for Item # {$row['item_number']}");
        }

        if(!$canCreate) {
            $this->log->error("One or more errors were found, invoice will not be created");
        } else {
            $this->log->info('No errors found in lines, checking if approval required...');

            if($invoiceService->checkIfInvoiceApprovalRequired($invoice)) {
                $this->log->warning('APPROVAL REQUIRED');
                $invoice->is_approval_required = true;
            } else {
                $this->log->info('AUTOMATICALLY APPROVED, SENDING LINES TO PRICING');
                foreach($invoice->invoice_lines as $line) {
                    $pricingService->sendToPricing($line->sales_order_line->product, $line->unit_price, $line->pricing_priority);
                }
            }

            $invoice->touch();
            if(!$this->option('pretend')) {
                DB::commit();
            }
        }
    }

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
