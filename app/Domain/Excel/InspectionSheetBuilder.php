<?php

namespace App\Domain\Excel;

use App\Domain\LandedCostCalculator;
use App\Models\Consignment;
use App\Models\SalesOrderLine;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Spatie\MediaLibrary\Models\Media;

class InspectionSheetBuilder
{
    protected $currentWriterRow = 5;
    /** @var Worksheet */
    protected $sheet;
    protected $inspectionColumns = [
        'Product Photo',
        'Item No.',
        'Products Description',
        'Barcode',
        'Packing',
        'BARCODE PRODUCT / INNER / OUTER CHECK IT IS CORRECT FOR PRODUCT',
        'PACKAGING MAILBOX / BAG / BUBBLE WRAP',
        'INSTRUCTIONS CHECK SUITABLE FOR PRODUCT',
        'PRODUCT TEST TURN ITEM ON OR ASSEMBLE',
        'APPEARANCE OK',
        'SIZE MEASURE ITEM CHECK IS CORRECT'
    ];
    protected $shippingColumns = [
        'Product Photo',
        'Item No.',
        'Products Description',
        'Barcode',
        'Packing',
        'Landed Cost GBP',
        'Qty Received'
    ];
    protected $mode = 'inspection';

    public function build(Consignment $consignment, $mode = 'inspection')
    {
        $this->mode = $mode;
        $spreadsheet = new Spreadsheet();
        $this->sheet = $spreadsheet->getSheet(0);
        $this->drawTitle($consignment, $this->mode);
        if ($this->mode === 'inspection') {
            $this->drawHeaders($this->inspectionColumns);
        } else if($this->mode === 'shipping') {
            $this->drawHeaders($this->shippingColumns);
        }
        foreach ($consignment->sales_order_lines as $line) {
            $this->writeLine($line);
        }
        $fp = fopen('php://memory', 'w');
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($fp);
        rewind($fp);
        return $fp;
    }

    protected function drawHorizontalRule($columns)
    {
        for ($col = 1; $col <= count($columns); $col++) {
            $this->sheet
                ->getStyleByColumnAndRow($col, $this->currentWriterRow)
                ->getBorders()
                ->getBottom()
                ->setBorderStyle(Border::BORDER_THICK);
        }
    }

    protected function writeLine(SalesOrderLine $line)
    {
        $sheet = $this->sheet;
        if($this->mode === 'inspection') {
            $this->drawHorizontalRule($this->inspectionColumns);
        } else {
            $this->drawHorizontalRule($this->shippingColumns);
        }
        $this->currentWriterRow++;


        /** @var Media $media */
        foreach ($line->product->getMedia()->take(2) as $i => $media) {
            if (!file_exists($media->getPath('thumb'))) {
                continue;
            }
            $drawing = new Drawing();
            $drawing->setPath($media->getPath('thumb'));
            $drawing->setCoordinates('A' . $this->currentWriterRow);
            $drawing->setOffsetX(5 + ($i * 135) );
            $drawing->setOffsetY(5);
            $drawing->setWidthAndHeight(50,50);
            $drawing->setWorksheet($this->sheet);
        }

        $sheet->setCellValueByColumnAndRow(1, $this->currentWriterRow, '');

        $sheet->setCellValueByColumnAndRow(2, $this->currentWriterRow, $line->article_number);
        $sheet->setCellValueByColumnAndRow(4, $this->currentWriterRow, $line->barcode);
        // border over the top of the current row

        if($this->mode === 'inspection') {
            for ($i = 1; $i <= count($this->inspectionColumns); $i++) {
                $sheet->getStyleByColumnAndRow($i, $this->currentWriterRow)
                    ->getBorders()
                    ->getTop()
                    ->setBorderStyle(Border::BORDER_THICK);
            }
        } else if($this->mode === 'shipping') {

            $sheet->getStyleByColumnAndRow(6, $this->currentWriterRow)
                ->applyFromArray([
                    'font' => ['bold' => true]
                ])
                ->getFont()
                ->getColor()
                ->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);

            for ($i = 1; $i <= count($this->shippingColumns); $i++) {
                $sheet->getStyleByColumnAndRow($i, $this->currentWriterRow)
                    ->getBorders()
                    ->getTop()
                    ->setBorderStyle(Border::BORDER_THICK);
            }
        }

        $description = preg_split(
            "/[\r\n]+/",
            $line->product->product_description?:$line->product_description,
            null,
            PREG_SPLIT_NO_EMPTY
        );
        foreach ($description as $i => $text) {
            $sheet->setCellValueByColumnAndRow(3, $this->currentWriterRow+$i, $text);
        }

        $packing = preg_split(
            "/[\r\n]+/",
            $line->packing_instructions,
            null,
            PREG_SPLIT_NO_EMPTY
        );

        foreach ($packing as $i => $text) {
            $sheet->setCellValueByColumnAndRow(5, $this->currentWriterRow+$i, $text);
        }

        if($this->mode === 'shipping') {
            $sheet->setCellValueByColumnAndRow(6, $this->currentWriterRow, $line->pivot->est_landed_cost_per_unit_gbp);

            $sheet->setCellValueByColumnAndRow(7, $this->currentWriterRow, $line->pivot->qty_shipped);
        }

        $rowCount = max(count($description), count($packing));
        $this->currentWriterRow += $rowCount-1;


        if($this->mode === 'inspection') {
            $this->drawHorizontalRule($this->inspectionColumns);
        } else if ($this->mode === 'shipping') {
            $this->drawHorizontalRule($this->shippingColumns);
        }

        // for every column ...
        for ($row = 0; $row < $rowCount; $row++) {

            $desc = $sheet
                ->getCellByColumnAndRow(4, $this->currentWriterRow - $row)
                ->getValue();

            $packing = $sheet
                ->getCellByColumnAndRow(5, $this->currentWriterRow - $row)
                ->getValue();

            $longest = max(strlen($desc), strlen($packing));
            if ($longest > 40) {
                $sheet
                    ->getRowDimension($this->currentWriterRow - $row)
                    ->setRowHeight(1.5 * ceil($longest % 40));
            }

            if($this->mode === 'inspection') {
                for ($col = 1; $col <= count($this->inspectionColumns); $col++) {
                    // start at the current row and work upwards, drawing
                    // vertical borders on either side of each cell ...
                    $sheet->getStyleByColumnAndRow($col, $this->currentWriterRow - $row)
                        ->applyFromArray([
                            'alignment' => [
                                'wrapText' => true,
                                'vertical' => Alignment::VERTICAL_TOP,
                            ],
                            'borders' => [
                                'left' => ['borderStyle' => Border::BORDER_THICK],
                                'right' => ['borderStyle' => Border::BORDER_THICK]
                            ]
                        ]);
                }
            } else if ($this->mode === 'shipping') {
                for ($col = 1; $col <= count($this->shippingColumns); $col++) {
                    // start at the current row and work upwards, drawing
                    // vertical borders on either side of each cell ...
                    $sheet->getStyleByColumnAndRow($col, $this->currentWriterRow - $row)
                        ->applyFromArray([
                            'alignment' => [
                                'wrapText' => true,
                                'vertical' => Alignment::VERTICAL_TOP,
                            ],
                            'borders' => [
                                'left' => ['borderStyle' => Border::BORDER_THICK],
                                'right' => ['borderStyle' => Border::BORDER_THICK]
                            ]
                        ]);
                }

            }
        }

        $this->currentWriterRow++;
    }

    protected function drawTitle(Consignment $consignment, $mode = 'inspection')
    {
        $this->sheet->setCellValue('A1', "REFERENCE: " . $consignment->reference);
        $this->sheet->setCellValue('A2', "ORDER NUMBER: " . $consignment->sales_order->reference);
        $this->sheet->setCellValue('A3', "SUPPLIER REF: " . $consignment->supplier_ref);
        $this->sheet->setCellValue('A4', "CONTAINER: " . $consignment->container_number);
        $this->sheet->setCellValue('B1', "MID REF: " . $consignment->shipping_agent_ref);
        $this->sheet->setCellValue('B2', "VESSEL: " . $consignment->vessel_name);
        $this->sheet->setCellValue('D1', "B/L NUMBER: " . $consignment->bl_number);
        $this->sheet->setCellValue('D2', "EST ARRIVAL: " . optional($consignment->estimated_arrival_at)->format('d/M/y'));
        $this->sheet->setCellValue('D3', "DATE GENERATED: " . Carbon::now()->format('d/M/y'));

        switch ($mode)  {
            case 'inspection':
                break;
            case 'shipping':

                $this->sheet
                    ->setCellValue('E1', 'FX RATE: ' . number_format(1/$consignment->sales_order->fx_rate, 2))
                    // This is calculated by doing invoice amount / total cuft at the point they update the shipment
                    ->setCellValue('E2', 'SHIPPING RATE: ' . number_format($consignment->est_cost_cuft_gbp,2));
                break;
        }

        foreach (['A1', 'A2', 'A3', 'A4', 'B1', 'B2', 'B3', 'C1', 'C2', 'C3', 'D1', 'D2', 'D3', 'E1', 'E2', 'E3'] as $cell) {
            $this->sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true]
            ]);
        }

    }

    protected function drawHeaders($columns)
    {
        $row = $this->currentWriterRow+1;
        foreach ($columns as $col => $name) {

            $width = 5*7.5;
            if ($col == 1 || $col == 2 || $col > 4) {
                $width = 5*3;
            }

            // Roughly 5 units per cm
            $this->sheet
                ->getColumnDimensionByColumn($col + 1)
                ->setWidth($width);

            $this->sheet->getRowDimension($row)->setRowHeight(5*15);
            $this->sheet->setCellValueByColumnAndRow($col+1, $row, $name);
            $this->sheet->getStyleByColumnAndRow($col+1, $row)->applyFromArray([
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ]
            ]);
            $this->sheet->getStyleByColumnAndRow($col+1, $row)
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THICK);

            if ($this->mode == 'shipping') {
                $this->sheet->getStyleByColumnAndRow(6, $row)
                    ->getFont()
                    ->getColor()
                    ->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
            }
        }
        $this->currentWriterRow++;
    }

    public function setMode($mode){
        $this->mode = $mode;
    }
}
