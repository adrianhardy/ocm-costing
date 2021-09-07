<?php


namespace App\Domain\Excel;


use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;

class SalesOrderExporter
{
    /**
     * @var \SplFileInfo
     */
    private $template;

    public function __construct(\SplFileInfo $template)
    {
        $this->template = $template;
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
        $clone = $reader->load($this->template->getRealPath());
        $writer = IOFactory::createWriter($clone, 'Xlsx');
        
        $target = $clone->getSheet(0);
        
        foreach ($source->getSheet(0)->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {

                if ($cell->getColumn() == 'A' && $cell->getValue() == '%LINE_START%') {
                    $target->getCell($cell->getCoordinate())->setValue('');
                    $writingLines = true;
                }
                if ($cell->getColumn() == 'A' && $cell->getValue() == '%LINE_END%') {
                    $target->getCell($cell->getCoordinate())->setValue('');
                    $writingLines = false;
                }

                $targetCell = $target->getCell($cell->getCoordinate());
                $this->replacePlaceholders($targetCell, $salesOrder);
            }
        }

        $temp = fopen('php://temp', 'w');
        $writer->save($temp);
        rewind($temp);
        return $temp;
    }

    /**
     * @param \PhpOffice\PhpSpreadsheet\Cell\Cell|null $cell
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $target
     * @param SalesOrderLine $salesOrder
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function replaceLinePlaceholders(
        \PhpOffice\PhpSpreadsheet\Cell\Cell $cell,
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $target,
        SalesOrderLine $line
    )
    {
        switch (strtoupper($cell->getValue())) {
            case '%ITEM_NUMBER%':
                $cell->setValue($line->item_number);
                break;
        }
    }

    /**
     * @param \PhpOffice\PhpSpreadsheet\Cell\Cell|null $cell
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $target
     * @param SalesOrder $salesOrder
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function replacePlaceholders(
        \PhpOffice\PhpSpreadsheet\Cell\Cell $cell,
        SalesOrder $salesOrder
    ) {
        switch (strtoupper($cell->getValue())) {
            case '%SUPPLIER_NAME%':
                $cell->setValue($salesOrder->supplier->name);
                break;
            case '%SUPPLIER_ADDRESS_AS_LINE%':
                $cell->setValue($salesOrder->supplier->delivery_address);
                break;
            case '%SUPPLIER_TELEPHONE%':
                $cell->setValue($salesOrder->supplier->contact_phone);
                break;
            case '%DOCUMENT_NUMBER%':
                $cell->setValue($salesOrder->sales_order_number);
                break;
            case '%DOCUMENT_TYPE%':
                $cell->setValue($salesOrder->stage_description);
                break;
        }
    }
}
