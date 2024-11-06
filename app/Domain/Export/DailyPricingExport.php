<?php


namespace App\Domain\Export;


use App\Models\DailyPricing;
use Illuminate\Support\Collection;
use League\Csv\Exception;
use League\Csv\Writer;
use Shuchkin\SimpleXLSXGen;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DailyPricingExport
{
    protected $writer;
    protected $dailyPricings = array();
    protected $exportLines = array();
    protected $log;
    protected $pa;
    protected $columns = array(
        'product.primary_sku',
        'product.item_number',
        'product.name',
        'country.iso_2_code',
        'suggested_price',
        'fba_fee',
        'submitted_at',
        'submitter.name',
        'local_profit',
        'product.final_cost_gbp',
        'product.daily_pricing_notes'
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
     *
     * We were using chunk and calling buildExport multiple times to
     * add to a single csv export, since moving to using XLS, we want
     * to build up a massive array of lines.
     *
     * @return Writer|null
     */
    public function buildExport()
    {
        try {
            foreach ($this->dailyPricings as $pricing) {
                $exportItem = [];
                foreach ($this->columns as $column) {
                    $exportItem[] = $this->extractProperty($pricing, $column);
                }
                $this->exportLines[] = $exportItem;
            }
        } catch (Exception $e) {
            $this->log->critical(
                $e->getMessage()
            );
            return null;
        }
    }

    public function getXLSDownloadResponse()
    {
        $lines = [];
        $lines[] = $this->columns;
        foreach($this->exportLines as $line) {
            $lines[] = $line;
        }

        $export = SimpleXLSXGen::fromArray($lines);
        foreach($this->columns as $key=>$column) {
            $export->setColWidth($key+1, 25);
        }

        return $export;
    }


    /**
     * @return null
     *
     * @deprecated To have better control over column sizes, we're exporting as .xls
     * @see DailyPricingExport::getXLSDownloadResponse()
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