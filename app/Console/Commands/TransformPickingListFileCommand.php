<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\Console\Output\OutputInterface;

class TransformPickingListFileCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import picking list file - used to create a shipment';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:shipping-file {--type=} {file} {sheet=0}';

    /** @var Worksheet */
    protected $sheet;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $reader = IOFactory::createReaderForFile($this->argument('file'));
        $reader->setReadEmptyCells(false);
        $document = $reader->load($this->argument('file'));

        if (is_numeric($this->argument('sheet'))) {
            $this->sheet = $document->getAllSheets()[$this->argument('sheet')];
        }

        if ($this->argument('sheet') == 'auto') {
            $this->sheet = $document->getSheetByName(strtoupper($this->option('type')));
        }

        $lines = [];
        switch($this->option('type')) {
            case 'invoice':
                $lines = $this->extractInvoiceLines();
                break;
            case 'packing':
                $lines = $this->extractPackingLines();
                break;
        }

        $this->output->write(json_encode([
            'lines' => $lines,
            'shipment' => $this->extractHeaders()
        ]));
    }

    protected function extractInvoiceLines()
    {
        $vvv = $this->verbosity >= OutputInterface::VERBOSITY_VERBOSE;
        $tableStart = $this->findPayloadStartingRow();
        $coords = $this->sheet->getHighestRowAndColumn();
        $maxRows = $coords['row'];

        $lines = [];
        $currentPo = null;
        for ($i = $tableStart+1; $i < $maxRows+10; $i++) {

            $columns = [
                'po_number',
                'factory_number',
                'article_number',
                'description',
                'qty',
                'unit_price',
                'sub_total'
            ];

            $row = [];
            foreach ($columns as $j => $name) {
                $row[$name] = trim($this->sheet->getCellByColumnAndRow($j+1, $i)->getFormattedValue());
            }

            $row['qty'] = str_replace(',', '', $row['qty']);

            if ($row['po_number'] && $row['po_number'] !== $currentPo) {
                $currentPo = $row['po_number'];
            }

            if (!$row['po_number'] && $currentPo) {
                $row['po_number'] = $currentPo;
            }

            $poOk = ($row['po_number'] && $currentPo) || (!$currentPo && !$row['po_number']);
            if ($poOk &&
                $row['article_number'] &&
                $row['qty'] &&
                $row['unit_price']
            ) {
                $row['unit_price'] = trim(str_replace('US$', '', $row['unit_price']));
                $row['sub_total'] = trim(str_replace('US$', '', $row['sub_total']));
                $row['unit_price'] = trim(str_replace('USD', '', $row['unit_price']));
                $row['sub_total'] = trim(str_replace('USD', '', $row['sub_total']));


                $validator = Validator::make($row, [
                    'unit_price' => 'required|numeric|min:0',
                    'qty' => 'required|numeric|min:0',
                ], [
                    'qty.numeric' => 'Column :attribute must be a number (found \':input\')'
                ]);

                if ($validator->fails()) {
                    throw new \Exception("Validation error on line $i : " . $validator->getMessageBag()->first());
                }

                $lines[] = $row;
            }
        }
        return $lines;
    }


    protected function extractPackingLines()
    {
        $vvv = $this->verbosity >= OutputInterface::VERBOSITY_VERBOSE;
        $tableStart = $this->findPayloadStartingRow();
        $coords = $this->sheet->getHighestRowAndColumn();
        $maxRows = $coords['row'];

        $lines = [];
        $currentPo = null;
        for ($i = $tableStart+1; $i < $maxRows+10; $i++) {

            $columns = [
                'po_number',
                'factory_item_number',
                'article_number',
                'description',
                'qty',
                'ctn',
                'pcs_per_ctn',
                'outer_dimension_w',
                'outer_dimension_l',
                'outer_dimension_h',
                'ctn_per_cbm',
                'total_cbm'
            ];

            $row = [];
            foreach ($columns as $j => $name) {
                $row[$name] = trim($this->sheet->getCellByColumnAndRow($j+1, $i)->getFormattedValue());
            }

            if ($row['po_number'] && $row['po_number'] !== $currentPo) {
                $currentPo = $row['po_number'];
            }

            if (!$row['po_number'] && $currentPo) {
                $row['po_number'] = $currentPo;
            }

            $poOk = ($row['po_number'] && $currentPo) || (!$currentPo && !$row['po_number']);

            if ($poOk &&
                $row['article_number'] &&
                $row['qty'] &&
                $row['ctn'] &&
                $row['pcs_per_ctn'] &&
                $row['outer_dimension_w'] &&
                $row['outer_dimension_l'] &&
                $row['outer_dimension_h'] &&
                $row['ctn_per_cbm'] &&
                $row['total_cbm']
            ) {
                $lines[] = $row;
            }
        }
        return $lines;
    }

    protected function extractHeaders()
    {

        $titles = [
            'invoice no' => 'invoice_number',
            'invoice no:' => 'invoice_number',
            'invoice no.' => 'invoice_number',
            'invoice no.:' => 'invoice_number',
            'p.o no.'   => 'sales_order_number',
            'p.o no.:'       => 'sales_order_number',
            'p.o no:'        => 'sales_order_number',
            'p.o no'         => 'sales_order_number',
            'etd:'          => 'estimated_date_of_departure',
            'eta:'          => 'estimated_date_of_arrival',
            'etd'           => 'estimated_date_of_departure',
            'eta'           => 'estimated_date_of_arrival',
            'vessel name'   => 'vessel_name',
            'vessel name:'  => 'vessel_name',
            'voyage no.:'   => 'voyage_number',
            'voyage no.'    => 'voyage_number',
            'voyage no'     => 'voyage_number',
            'voyage no:'    => 'voyage_number',
            'container no.:'    => 'container_reference',
            'container no.'     => 'container_reference',
            'container no:'     => 'container_reference',
            'container no'      => 'container_reference',
            'seal no:'      => 'seal_number'
        ];
        $pairs = [];
        for ($i = 1; $i < 10; $i++) {
            for ($j = 1; $j < 15; $j++) {
                $value = trim(strtolower($this->sheet->getCellByColumnAndRow($i, $j)->getValue()));
                if (!$value) {
                    continue;
                }
                if (isset($titles[$value])) {
                    // the first column has its data immediately adjacent
                    // (invoice #), but the next column of info has a gap
                    // Attempt to go right up to 10 cells before we find the value for $title
                    for ($attempts = 1; $attempts < 10; $attempts++) {
                        $nextCell = $this->sheet->getCellByColumnAndRow($i+$attempts, $j);
                        if ($nextCell->getFormattedValue()) {
                            break;
                        }
                    }


                    // if, after going 10 columns to the right we still haven't found anything, give up.
                    if (!$nextCell->getFormattedValue()) {
                        continue;
                    }

                    if (substr($titles[$value], 0, 14) == 'estimated_date') {
                        $date = date_create_from_format('d/M/Y', $nextCell->getFormattedValue());

                        if (!$date) {
                            $date = date_create_from_format('d/M/y', $nextCell->getFormattedValue());
                        }


                        if (!$date) {
                            $date = date_create_from_format('d/m/Y', $nextCell->getFormattedValue());
                        }

                        if (!$date) {
                            $date = date_create_from_format('d/m/y', $nextCell->getFormattedValue());
                        }

                        if (!$date) {
                            throw new \Exception("Failed to parse estimated date in cell {$nextCell->getCoordinate()} -- {$nextCell->getFormattedValue()}");
                        }

                        $pairs[$titles[$value]] = $date->setTime(0, 0)->format('Y-m-d');
                    } else {
                        $pairs[$titles[$value]] = $nextCell->getFormattedValue();
                    }
                }
            }
        }
        return $pairs;
    }

    public function findPayloadStartingRow()
    {
        $marker = 'products description';
        for ($i = 1; $i < 10; $i++) {
            for ($j = 5; $j < 20; $j++) {
                $value = trim(strtolower(
                    $this->sheet->getCellByColumnAndRow($i, $j)->getFormattedValue()
                ));
                if ($value == $marker) {
                    return $j;
                }
            }
        }
        throw new \RuntimeException("Failed to find marker '$marker'");
    }
}
