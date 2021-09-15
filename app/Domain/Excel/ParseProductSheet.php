<?php


namespace App\Domain\Excel;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParseProductSheet
{
    protected $log;

    /** @var Worksheet */
    protected $sheet;

    public $images;

    public $rows;

    public $quoteNumber;

    public function __construct()
    {
        $this->log = Log::channel('import');
    }


    /**
     * Represent $file as a complex collection of objects.
     * Return value is a collection of collections - rows -> cells
     *
     * Each cell has 'raw', 'value', 'coords' and 'formatted' keys
     *
     * @param \SplFileInfo $file
     * @return \Illuminate\Support\Collection
     * @throws \PhpOffice\PhpSpreadsheet\Calculation\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function read(\SplFileInfo $file)
    {
        $this->quoteNumber = null;

        $log = Log::channel('import');
        $log->info("Reading XLSX document...");
        $reader = IOFactory::createReaderForFile($file->getRealPath());
        $reader->setReadEmptyCells(false);
        $document = $reader->load($file->getRealPath());
        $this->sheet = $document->getAllSheets()[0];
        $log->info("Completed reading, parsing ...");


        $raw = collect();
        foreach ($this->sheet->getRowIterator() as $i => $row) {

            if ($i < 10 && !$this->quoteNumber) {
                $this->extractQuoteNumber(collect(iterator_to_array($row->getCellIterator())));
            }

            $nonEmptyCells = collect(iterator_to_array($row->getCellIterator()))->filter(function(Cell $cell) {
                return trim($cell->getValue()) != '';
            });

            if ($nonEmptyCells->count() < 1) {
                continue;
            }

            $data = collect();
            foreach ($row->getCellIterator() as $column => $cell) {
                try {
                    $formatted = $cell->getFormattedValue();
                } catch (\Exception $ex) {
                    $formatted = '';
                }
                try {
                    $calculated = $cell->getCalculatedValue();
                } catch (\Exception $ex) {
                    $calculated = '';
                }
                $datum = [
                    'raw'           => $cell->getValue(),
                    'value'         => $cell->getValue(),
                    'coords'        => [
                        'column'    => $column,
                        'row'       => $i,
                    ],
                    'formatted'     => $formatted
                ];
                if ($cell->isFormula()) {
                    $datum['value'] = $calculated;
                }
                $data->push($datum);
            }
            $raw->push($data);
        }

        $this->scanImages();

        $this->rows = $raw;
        return $this->rows;
    }

    /**
     * Find a quote number within $cells.
     * @param Collection|Cell[] $cells
     */
    public function extractQuoteNumber(Collection $cells)
    {
        foreach ($cells as $cell) {
            $stump = preg_replace('/[^a-zA-Z]/', '', $cell->getValue());
            if (stristr($stump, 'customerpono') || stristr($stump, 'batchno')) {
                $parts = explode(':', $cell->getValue(), 2);
                if (count($parts) == 2) {
                    $this->quoteNumber = $parts[1];
                    return;
                }
            }
        }
    }

    public function scanImages()
    {
        $images = array_map(function($drawing) {
            $payload = [
                'coords' => [
                    'column' => substr($drawing->getCoordinates(), 0, 1),
                    'row' => substr($drawing->getCoordinates(), 1)
                ],
                'blob' => null
            ];
            if ($drawing instanceof MemoryDrawing) {
                $temp = fopen('php://temp', 'w');
                imagepng($drawing->getImageResource(), $temp);
                $bytes = ftell($temp);
                rewind($temp);
                $payload['blob'] = fread($temp, $bytes);
            } else {
                $zip = fopen($drawing->getPath(), 'r');
                $blob = '';
                while (!feof($zip)) {
                    $blob .= fread($zip, 1024);
                }
                $payload['blob'] = $blob;
            }

            if ($payload['blob']) {
                return $payload;
            }

            return null;

        }, (array) $this->sheet->getDrawingCollection());

        $this->images = Collection::wrap($images);
    }



}
