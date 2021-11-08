<?php

namespace App\Console\Commands;

use App\Domain\Excel\SalesOrderExporter;
use App\Models\SalesOrder;
use Illuminate\Console\Command;

class ExportSalesOrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:sales-order {sales_order_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export a sales order to stdout';

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
        /** @var SalesOrder $order */
        $order = SalesOrder::query()->findOrFail($this->argument('sales_order_id'));
        $template = new \SplFileInfo(storage_path('app/private/sales_order_template.xlsx'));
        $exporter = new SalesOrderExporter($template);
        try {
            $blob = $exporter->generate($order);
            echo $blob;
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
    }
}
