<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

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
        $this->sheet = $document->getAllSheets()[$this->argument('sheet')];

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
        $tableStart = $this->findPayloadStartingRow();
        $coords = $this->sheet->getHighestRowAndColumn();
        $maxRows = $coords['row'];

        $lines = [];
        $currentPo = null;
        for ($i = $tableStart+1; $i < $maxRows+10; $i++) {

            $columns = [
                'po_number',
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

            if ($row['po_number'] && $row['po_number'] !== $currentPo) {
                $currentPo = $row['po_number'];
            }

            if (!$row['po_number'] && $currentPo) {
                $row['po_number'] = $currentPo;
            }

            if ($row['po_number'] &&
                $row['article_number'] &&
                $row['qty'] &&
                $row['unit_price']
            ) {
                $row['unit_price'] = str_replace('US$', '', $row['unit_price']);
                $row['sub_total'] = str_replace('US$', '', $row['unit_price']);
                $lines[] = $row;
            }
        }
        return $lines;
    }


    protected function extractPackingLines()
    {
        $tableStart = $this->findPayloadStartingRow();
        $coords = $this->sheet->getHighestRowAndColumn();
        $maxRows = $coords['row'];

        $lines = [];
        $currentPo = null;
        for ($i = $tableStart+1; $i < $maxRows+10; $i++) {

            $columns = [
                'po_number',
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

            if ($row['po_number'] &&
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
        for ($i = 1; $i < 7; $i++) {
            for ($j = 1; $j < 15; $j++) {
                $value = trim(strtolower($this->sheet->getCellByColumnAndRow($i, $j)->getValue()));
                if (!$value) {
                    continue;
                }
                if (isset($titles[$value])) {
                    $nextCell = $this->sheet->getCellByColumnAndRow($i+1, $j);
                    // the first column has its data immediately adjacent
                    // (invoice #), but the next column of info has a gap
                    // (container size)
                    if (!$nextCell->getFormattedValue()) {
                        $nextCell = $this->sheet->getCellByColumnAndRow($i+2, $j);
                    }

                    if (!$nextCell->getFormattedValue()) {
                        continue;
                    }

                    if (substr($titles[$value], 0, 14) == 'estimated_date') {
                        $date = date_create_from_format('d/M/y', $nextCell->getFormattedValue());
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
