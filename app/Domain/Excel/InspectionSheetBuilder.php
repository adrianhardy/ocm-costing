<?php

namespace App\Domain\Excel;

use App\Models\Consignment;
use App\Models\SalesOrderLine;
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
    protected $currentWriterRow = 0;
    /** @var Worksheet */
    protected $sheet;
    protected $columns = [
        'Product Photo',
        'Art No.',
        'Products Description',
        'Packing',
        'BARCODE PRODUCT / INNER / OUTER CHECK IT IS CORRECT FOR PRODUCT',
        'PACKAGING MAILBOX / BAG / BUBBLE WRAP',
        'INSTRUCTIONS CHECK SUITABLE FOR PRODUCT',
        'PRODUCT TEST TURN ITEM ON OR ASSEMBLE',
        'APPEARANCE OK',
        'SIZE MEASURE ITEM CHECK IS CORRECT'
    ];
    public function build(Consignment $consignment)
    {
        $spreadsheet = new Spreadsheet();
        $this->sheet = $spreadsheet->getSheet(0);
        $this->drawTitle($consignment);
        $this->drawHeaders();
        foreach ($consignment->sales_order_lines as $line) {
            $this->writeLine($line);
        }
        $fp = fopen('php://memory', 'w');
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($fp);
        rewind($fp);
        return $fp;
    }

    protected function drawHorizontalRule()
    {
        for ($col = 1; $col <= count($this->columns); $col++) {
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
        $this->drawHorizontalRule();
        $this->currentWriterRow++;


        /** @var Media $media */
        foreach ($line->product->getMedia()->take(2) as $i => $media) {
            $drawing = new Drawing();
            $drawing->setPath($media->getPath('thumb'));
            $drawing->setCoordinates('A' . $this->currentWriterRow);
            $drawing->setOffsetX(5 + ($i * 135) );
            $drawing->setOffsetY(5);
            $drawing->setWidthAndHeight(130,130);
            $drawing->setWorksheet($this->sheet);
        }

        $sheet->setCellValueByColumnAndRow(2, $this->currentWriterRow, $line->article_number);
        // border over the top of the current row
        for ($i = 1; $i <= count($this->columns); $i++) {
            $sheet->getStyleByColumnAndRow($i, $this->currentWriterRow)
                ->getBorders()
                ->getTop()
                ->setBorderStyle(Border::BORDER_THICK);
        }

        $description = preg_split(
            "/[\r\n]+/",
            $line->product_description,
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
            $sheet->setCellValueByColumnAndRow(4, $this->currentWriterRow+$i, $text);
        }

        $rowCount = max(count($description), count($packing));
        $this->currentWriterRow += $rowCount-1;
        $this->drawHorizontalRule();


        // for every column ...
        for ($row = 0; $row < $rowCount; $row++) {

            $desc = $sheet
                ->getCellByColumnAndRow(3, $this->currentWriterRow - $row)
                ->getValue();

            $packing = $sheet
                ->getCellByColumnAndRow(4, $this->currentWriterRow - $row)
                ->getValue();

            $longest = max(strlen($desc), strlen($packing));
            if ($longest > 40) {
                $sheet
                    ->getRowDimension($this->currentWriterRow - $row)
                    ->setRowHeight(1.5 * ceil($longest % 40));
            }

            for ($col = 1; $col <= count($this->columns); $col++) {
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

        $this->currentWriterRow++;
    }

    protected function drawTitle(Consignment $consignment)
    {
        $this->sheet->setCellValue('A1', "CONTAINER: " . $consignment->container_number);
        $this->sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true]
        ]);
        $this->currentWriterRow++;
    }

    protected function drawHeaders()
    {
        $row = $this->currentWriterRow+1;
        foreach ($this->columns as $col => $name) {

            $width = 5*7.5;
            if ($col == 1 || $col > 3) {
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

        }
        $this->currentWriterRow++;
    }
}
