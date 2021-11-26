<?php


namespace App\Domain\Excel;


use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Spatie\MediaLibrary\Models\Media;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class SalesOrderExporter
{
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

    private $format = 'Xlsx';

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
        'packing_instructions' => '%PACKING_INSTRUCTIONS%',
        'unit_price' => '%UNIT_PRICE%',
        'cartons' => '%CARTONS%',
        'units_per_case' => '%UNITS_PER_CARTON%',
        'inner_qty' => '%INNER_QUANTITY%',
        'units_requested' => '%QUANTITY%',
        'sub_total' => '%SUB_TOTAL%',
        'cuft_per_case' => '%CUFT_PER_CTN%',
        'total_cuft' => '%TOTAL_CUFT%'
    ];

    public function __construct(\SplFileInfo $template)
    {
        $this->template = $template;
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

        $this->start = $this->findItemStartRow($source->getSheet(0));
        $this->mapping = $this->calculateColumnMapping($source->getSheet(0));

        $this->clone->getSheet(0)->removeRow($this->start);

        foreach  ($salesOrder->lines as $current => $line)
        {
            $this->clone->getSheet(0)->insertNewRowBefore($this->start + ($current*10), 10);
            $this->insertSalesOrderLine($line, $this->start + ($current*10));
        }

        foreach ($this->clone->getSheet(0)->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $this->replaceGlobalPlaceholders($cell, $salesOrder);
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
            'inner_qty'
        ]);
        $row = collect($row)->merge([
            'product_name' => $line->name,
            'sub_total' => $line->total,
            'cartons' => ceil($line->units_requested / $line->units_per_case),
            'cuft_per_case' => $line->case_cubic_ft,
            'total_cuft' => $line->total_cubic_m * setting('cubic_metres_to_feet')
        ]);

        // for each of the values we've found above, find the coordinates
        // (stored in $this->mapping) and write out the value to the cell
        foreach ($row as $name => $value) {
            if (!$this->mapping[$name]) {
                // if we can't find the mapping information, skip it
                continue;
            }

            $this->clone->getSheet(0)->setCellValue(
                $this->mapping[$name].($offset + 1),
                $row[$name]
            );
        }

        // because product description sits under product name,
        // we'll do this one manually
        $this->clone->getSheet(0)->setCellValue(
            $this->mapping['product_name'].($offset + 2),
            $line->product_description
        );
        // set the row height
        $this->clone->getSheet(0)
            ->getRowDimension($offset+2)
            ->setRowHeight(100);

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
                    return $line;
                }
            }
        }
        throw new \RuntimeException("Failed to find %ITEM_NUMBER% in sheet");
    }

    /**
     * @param Media $media
     * @param Cell $cell
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function writeImageToCell(Media $media, Cell $cell)
    {
        $blob = stream_get_contents($media->stream());
        $gdImage = imagecreatefromstring($blob);
        $drawing = new MemoryDrawing();
        $drawing->setCoordinates($cell->getCoordinate());
        $drawing->setImageResource($gdImage);
        $drawing->setRenderingFunction(
            MemoryDrawing::RENDERING_JPEG
        );
        $drawing->setMimeType(MemoryDrawing::MIMETYPE_DEFAULT);
        $drawing->setWidthAndHeight(150,150);
        $drawing->setWorksheet($this->clone->getActiveSheet());
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
        switch (strtoupper($cell->getValue())) {
            case '%SUPPLIER_NAME%':
                $cell->setValue($salesOrder->supplier->name);
                break;
            case '%SUPPLIER_TELEPHONE%':
                $cell->setValue("Tel: " . $salesOrder->supplier->contact_phone);
                break;
            case '%SUPPLIER_ADDRESS_AS_LINE%':
                $cell->setValue($salesOrder->supplier->delivery_address);
                break;
            case '%DOCUMENT_NUMBER%':
                $cell->setValue($salesOrder->sales_order_number);
                break;
            case '%DOCUMENT_TYPE%':
                $cell->setValue($salesOrder->stage_description);
                break;
            case '%SC_REFERENCE%':
                $cell->setValue($salesOrder->reference);
                break;
            case '%DATE_FINALISED%':
                $cell->setValue($salesOrder->finalised_at->format('d M Y'));
                break;
        }
    }
}
