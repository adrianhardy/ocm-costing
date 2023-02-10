<?php


namespace App\Domain\Excel;


use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Spatie\MediaLibrary\Models\Media;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class SalesOrderExporter
{

    protected $sheetRowsPerLine = 1;

    protected $log;

    /**
     * @var \SplFileInfo
     */
    private $template;

    /**
     * @var Spreadsheet
     */
    private $clone;

    /**
     * @var array
     */
    private $mapping;

    private $format = 'Xls';

    /**
     * Line number where the sheet fun starts
     * @var integer
     */
    private $start;

    private $columns = [
        'item_number' => '%ITEM_NUMBER%',
        'article_number' => '%ARTICLE_NUMBER%',
        'product_image' => '%PRODUCT_IMAGE%',
        'product_name' => '%PRODUCT_NAME%',
        'product_details' => '%PRODUCT_DETAILS%',
        'packing_instructions' => '%PACKING_INSTRUCTIONS%',
        'unit_price' => '%UNIT_PRICE%',
        'unit_weight_kg' => '%UNIT_WEIGHT_KG%',
        'case_weight_kg' => '%CARTON_WEIGHT_KG%',
        'unit_length_cm' => '%UNIT_LENGTH_CM%',
        'unit_width_cm' => '%UNIT_WIDTH_CM%',
        'unit_height_cm' => '%UNIT_HEIGHT_CM%',
        'length_cm' => '%CARTON_LENGTH_CM%',
        'width_cm' => '%CARTON_WIDTH_CM%',
        'height_cm' => '%CARTON_HEIGHT_CM%',

        'cartons' => '%CARTONS%',
        'units_per_case' => '%UNITS_PER_CARTON%',
        'line_cubic_m' => '%LINE_CUBIC_M%',
        'cubic_m_per_case' => '%CUBIC_M_PER_CTN%',
        'inner_qty' => '%INNER_QUANTITY%',
        'units_requested' => '%QUANTITY%',
        'sub_total' => '%SUB_TOTAL%',
        'cuft_per_case' => '%CUFT_PER_CTN%',
        'total_cuft' => '%TOTAL_CUFT%',
        'sales_order_reference' => '%SALES_ORDER_NUMBER%',
        'barcode' => '%BARCODE%'
    ];

    public function __construct(\SplFileInfo $template)
    {
        $this->template = $template;
        $this->log = Log::channel('export');
    }

    public function setFormatCsv()
    {
        $this->format = 'Csv';
        return $this;
    }

    public function setFormatXlsx()
    {
        $this->format = 'Xlsx';
        return $this;
    }

    /**
     * @param SalesOrder $salesOrder
     * @return false|resource
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function generate(SalesOrder $salesOrder)
    {
        $reader = IOFactory::createReaderForFile($this->template->getRealPath());
        $source = $reader->load($this->template->getRealPath());
        $this->clone = $reader->load($this->template->getRealPath());
        $writer = IOFactory::createWriter($this->clone, $this->format);

        $this->log->debug(
            "Highest Row and Column: " . print_r($source->getSheet(0)->getHighestRowAndColumn(), true)
        );

        // Sometimes, setting print areas generates the exception
        // "Cell coordinate can not be zero-length string" whenever
        // you try to insert or remove a row. It relates to
        // PhpOffice\PhpSpreadsheet\ReferenceHelper::insertNewBefore
        // where, right at the bottom, it does something to do with the
        // printarea.
        if ($source->getActiveSheet()->getPageSetup()->isPrintAreaSet()) {
            $source->getActiveSheet()->getPageSetup()->clearPrintArea();
        }

        $this->start = $this->findItemStartRow($source->getSheet(0));

        $this->mapping = $this->calculateColumnMapping($source->getSheet(0));
        // remove the template row
        $this->clone->getSheet(0)->removeRow($this->start);

        $maxLine = 0;
        for ($current = 1; $current < $salesOrder->lines->count(); $current++) {
            try {
                $this->clone->getSheet(0)->insertNewRowBefore($this->start + $current, $this->sheetRowsPerLine);
            } catch (\PhpOffice\PhpSpreadsheet\Exception $ex) {
                $this->log->debug(
                    "Caught exception when trying to process 'insertNewRowBefore': start '$this->start', current '$current'"
                );
                $this->log->error($ex->getMessage());
                throw $ex;
            }
            $maxLine = $this->start+$current;
        }

        foreach ($salesOrder->lines as $current => $line) {
            $this->insertSalesOrderLine($line, $this->start + $current);
        }

        $maxLine++; // So when we're doing our sums we're going to the last
        // line, not the zeroth indexed last line
        foreach ($this->clone->getSheet(0)->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $this->replaceGlobalPlaceholders($cell, $salesOrder);
                $this->replaceGlobalPlaceholderFormulae($cell, $maxLine);
            }
        }

        $temp = fopen('php://temp', 'w');
        $writer->save($temp);
        $size = ftell($temp);
        rewind($temp);
        return fread($temp, $size);
    }

    /**
     * Given a Sales Order Line, write out its contents to $offset
     * @param SalesOrderLine $line
     * @param $offset
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function insertSalesOrderLine(SalesOrderLine $line, $offset)
    {

        /** @var Collection $row */
        $row = $line->only([
            'item_number',
            'article_number',
            'packing_instructions',
            'unit_price',
            'units_per_case',
            'inner_qty',
            'unit_length_cm',
            'unit_height_cm',
            'unit_width_cm',
            'length_cm',
            'height_cm',
            'width_cm'
        ]);
        $row = collect($row)->merge([
            'case_weight_kg' => $line->case_weight_kg,
            'unit_weight_kg' => $line->unit_weight_kg,
            'product_name' => $line->product->name,
            'product_details' => $line->product_description,
            'sub_total' => $line->total,
            'units_requested' => $line->units_requested,
            'cartons' => ceil($line->units_requested / $line->units_per_case),
            'cuft_per_case' => $line->case_cubic_ft,
            'cubic_m_per_case' => $line->case_cubic_m,
            'total_cuft' => $line->total_cubic_m * setting('cubic_metres_to_feet'),
            'line_cubic_m'=> $line->total_cubic_m,
            'barcode' => $line->barcode,
        ]);

        // for each of the values we've found above, find the coordinates
        // (stored in $this->mapping) and write out the value to the cell
        foreach ($row as $name => $value) {
            if (! (isset($this->mapping[$name]) && $this->mapping[$name]) ) {
                // if we can't find the mapping information, skip it
                continue;
            }

            $this->clone->getSheet(0)->setCellValue(
                $this->mapping[$name].($offset + 1),
                $row[$name]
            );

            if (in_array($name, ['sub_total', 'unit_price'])) {
                $this->clone->getSheet(0)->getCell(
                    $this->mapping[$name].($offset + 1)
                )->getStyle()
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
            }
        }

        // set the row height
        $this->clone->getSheet(0)
            ->getRowDimension($offset+1)
            ->setRowHeight(150);

        // write out the product media to the image column
        if ($line->product->hasMedia()) {
            $coordinates = $this->mapping['product_image'].($offset+1);
            $image = $line->product->getFirstMedia();
            if ($line->selected_media) {
                $image = $line->selected_media;
            }
            $this->writeImageToCell(
                $image,
                $this->clone->getSheet(0)->getCell($coordinates)
            );
        }

    }

    /**
     * Calculate the line number where the template contains
     * %ITEM_NUMBER%
     *
     * @param Worksheet $sheet
     * @return int
     */
    protected function findItemStartRow(Worksheet $sheet)
    {
        $line = 0;
        foreach ($sheet->getRowIterator() as $row) {
            $line++;
            foreach ($row->getCellIterator() as $cell) {
                if ($cell->getValue() == '%ITEM_NUMBER%') {
                    $this->log->debug("Found ITEM_NUMBER on line $line");
                    return $line;
                }
            }
        }
        $this->log->debug("Failed to find ITEM_NUMBER even by the time we got to line $line");
        throw new \RuntimeException("Failed to find %ITEM_NUMBER% in sheet");
    }

    /**
     * @param Media $media
     * @param Cell $cell
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function writeImageToCell(Media $media, Cell $cell)
    {
        $coords = $cell->getCoordinate();
        try {
            $drawing = new Drawing();
            $drawing->setPath($media->getPath('thumb'));
            $drawing->setWidthAndHeight(130,130);
            $drawing->setOffsetY(5);
            $drawing->setOffsetX(5);
            $drawing->setWorksheet($this->clone->getSheet(0));
            $drawing->setCoordinates($coords);
        } catch (\Exception $ex) {
            $this->log->debug("Uncaught exception: {$ex->getMessage()}");
        }
    }

    /**
     * Produce an array of our key names to column numbers
     *
     * @param Worksheet $sheet
     * @return array
     */
    protected function calculateColumnMapping(Worksheet $sheet)
    {
        // create an empty mapping from our key to null
        $mapping = array_combine(array_keys($this->columns), array_fill(0, count($this->columns), null));

        // for each row, for each cell, start scanning for keywords
        // and map from our key to column name/number ....
        foreach ($sheet->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $flip = array_flip($this->columns);
                $value = $cell->getValue();
                if (in_array($value, array_values($this->columns))) {
                    if (isset($flip[$value])) {
                        $mapping[$flip[$value]] = $cell->getColumn();
                    }
                }
            }
        }
        return $mapping;
    }

    /**
     * @param \PhpOffice\PhpSpreadsheet\Cell\Cell|null $cell
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $target
     * @param SalesOrder $salesOrder
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function replaceGlobalPlaceholders(
        \PhpOffice\PhpSpreadsheet\Cell\Cell $cell,
        SalesOrder $salesOrder
    ) {
        $pairs = array_combine( [
            '%SUPPLIER_NAME%',
            '%FACTORY_NAME%',
            '%SUPPLIER_TELEPHONE%',
            '%SUPPLIER_ADDRESS_AS_LINE%',
            '%DOCUMENT_NUMBER%',
            '%DOCUMENT_TYPE%',
            '%SC_REFERENCE%',
            '%SALES_ORDER_NUMBER%',
            '%DATE_FINALISED%',
            '%NOTES%'
        ], [
            $salesOrder->supplier->name,
            $salesOrder->factory->name,
            $salesOrder->supplier->contact_phone,
            $salesOrder->supplier->delivery_address,
            $salesOrder->sales_order_number,
            $salesOrder->stage_description,
            $salesOrder->reference,
            $salesOrder->reference,
            $salesOrder->finalised_at->format('d M Y'),
            $salesOrder->sales_order_notes
        ]);
        if ($cell->getValue() == '%NOTES%') {
            $cell->getStyle()->getAlignment()->setWrapText(true);
        }
        $cell->setValue(strtr($cell->getValue(), $pairs));
    }

    /**
     * @param Cell $cell
     * @param integer $line the biggest line number our formulae shoudl work off
     * @return void
     */
    protected function replaceGlobalPlaceholderFormulae(Cell $cell, $maxLine)
    {

        switch($cell->getValue()) {
            case '%GRAND_TOTAL%':
                $quantityColumn = $this->mapping['units_requested'];
                $unitPriceColumn = $this->mapping['unit_price'];
                $subTotalColumn = $this->mapping['sub_total'];
                //$cell->setValue("=SUM({$quantityColumn}{$this->start}:{$quantityColumn}{$maxLine})*SUM({$unitPriceColumn}{$this->start}:{$unitPriceColumn}$maxLine)");
                $cell->setValue("=SUM({$subTotalColumn}{$this->start}:{$subTotalColumn}{$maxLine})");
                $cell->getStyle()
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

                break;
            case '%GRAND_TOTAL_UNITS%':
            case '%GRAND_TOTAL_PIECES%':
                $column = $this->mapping['units_requested'];
                $cell->setValue("=SUM({$column}{$this->start}:{$column}{$maxLine})");
                break;
            case '%GRAND_TOTAL_CARTONS%':
                $column = $this->mapping['cartons'];
                $cell->setValue("=SUM({$column}{$this->start}:{$column}{$maxLine})");
                break;
            case '%GRAND_TOTAL_CBM%':
                // because the spreadsheet template doesn't contain a column for
                // cubic m per line, we work it out from the total cuft and divide
                // by the magic number 35.3147
                $column = $this->mapping['total_cuft'];
                $cell->setValue("=SUM({$column}{$this->start}:{$column}{$maxLine})/35.3147");
                break;
            case '%GRAND_TOTAL_CUFT%':
                $column = $this->mapping['total_cuft'];
                $cell->setValue("=SUM({$column}{$this->start}:{$column}{$maxLine})");
                break;

        }
    }
}
