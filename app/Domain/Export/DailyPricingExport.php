<?php


namespace App\Domain\Export;


use App\Models\DailyPricing;
use Illuminate\Support\Collection;
use League\Csv\Exception;
use League\Csv\Writer;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DailyPricingExport
{
    protected $writer;
    protected $dailyPricings = array();
    protected $log;
    protected $pa;
    protected $columns = array(
        'product.item_number',
        'product.name',
        'country.name',
        'suggested_price',
        'fba_fee',
        'submitted_at',
        'submitter.name'
    );


    public function __construct($dailyPricings)
    {
        $this->dailyPricings = $dailyPricings;
        $this->pa = PropertyAccess::createPropertyAccessor();

    }

    /**
     *
     * @param $dailyPricings
     * @return void
     */
    public function setPricings($dailyPricings){
        $this->dailyPricings = $dailyPricings;
    }

    /**
     * If we call this multiple times, then the first time we'll get the
     * headers written to the CSV. On subsequent calls, we'll only get
     * the contents of $this->accounts written to the CSV. This means
     * we can chunk the query and just call "setAccounts" followed by
     * "buildExport" as many times as we like and we end up with a .
     * single CSV file...
     * @return Writer|null
     */
    public function buildExport()
    {
        $headers = $this->columns;
        try {
            if ($this->writer) {
                $export = $this->writer;
            } else {
                $export = Writer::createFromFileObject(new \SplTempFileObject(256 * 1024 * 1024));
                $export->setDelimiter(',');
                $export->setNewline("\r\n");
                $export->insertOne($headers);
                $this->writer = $export;
            }

            foreach ($this->dailyPricings as $pricing) {
                $exportItem = [];
                foreach ($this->columns as $column) {
                    $exportItem[] = $this->extractProperty($pricing, $column);
                }
                $export->insertOne($exportItem);
            }
            return $export;
        } catch (Exception $e) {
            $this->log->critical(
                $e->getMessage()
            );
            return null;
        }
    }




    /**
     * @return null
     */
    public function getCsvDownloadResponse()
    {
        $export = $this->buildExport();
        return $export->output();
    }
    /**
     * @param $data
     * @param $property
     * @return string
     */
    function extractProperty($data,$property): ?string
    {
        $elements = explode(".",$property);
        $propertyArray = "";
        foreach ($elements as $element){
            $propertyArray .= '['.$element.']';
        }
        try {
            return "".$this->pa->getValue($data,$propertyArray);
        } catch (\Exception $ignore){}
        return "";
    }
}