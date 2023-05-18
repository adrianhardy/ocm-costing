<?php


namespace App\Domain\Export;


use Carbon\Carbon;
use GuzzleHttp\Client;
use League\Csv\Exception;
use League\Csv\Writer;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ProductExport
{
    protected $writer;

    /** @var \App\Models\Product[] */
    protected $products = [];

    protected $log;
    protected $pa;


    protected $fullExportColumns = [
        'item_number',
        'article_number',
        'name',
        'unit_weight_kg',
        'case_weight_kg',
        'inner_qty',
        'units_per_case',
        'height_m',
        'length_m',
        'width_m',
        'supplier.name',
        'primary_sku',
        'additional_skus',
        'barcode',
        'unit_price',
        'unit_price_gbp',
        'packing_instructions',
        'product_description',
        'is_excluded_from_commission',
        'primary_asin',
        'additional_skus',
        'additional_asins',
        'uk_postage_method',
        'eu_postage_method',
        'row_postage_method'
    ];

    protected $columns;

    protected $simplifiedChinaExportColumns = [
        'item_number',
        'article_number',
        'name',
        'packing_instructions',
        'product_description',
        'current_landed_cost_per_unit_gbp'
    ];


    /**
     * @var mixed
     */
    private $stockLevels;
    /**
     * @var bool
     */
    private $stockLevelRequired = false;

    /**
     * @var mixed
     */
    private $stockTakeStartDate;

    public function __construct($products)
    {
        $this->products = $products;
        $this->pa = PropertyAccess::createPropertyAccessor();
        $this->columns = $this->fullExportColumns;
        $this->stockTakeStartDate = Carbon::now()->subYear()->startOfDay();
    }

    /**
     * @param Carbon $date
     * @return $this
     */
    public function setStockLevelRequired(Carbon $date = null)
    {
        $this->stockLevelRequired = true;
        if ($date) {
            $this->stockTakeStartDate = $date;
        }
        return $this;
    }

    public function setSimplifiedChinaExport()
    {
        $this->columns = $this->simplifiedChinaExportColumns;
        return true;
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

        if ($this->stockLevelRequired) {
            $headers[] = 'Current Stock';
            $headers[] = 'Stock as at ' . $this->stockTakeStartDate->format('d/m/Y');
            $headers[] = 'Goods In since';
            $headers[] = 'Dispatched since';
        }

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

            foreach ($this->products as $product) {
                $exportItem = [];
                foreach ($this->columns as $column) {
                    $exportItem[] = $this->extractProperty($product, $column);
                }

                $stockLevelToday = 'N/A';
                $stockLevelHistoric = 'N/A';
                $stockDispatched = 'N/A';
                $stockReceived = 'N/A';
                if($this->stockLevelRequired === true) {
                    // i'm assuming that stock levels can come in as null if nothing gets found
                    // so we're making sure we have it so the foreach doesn't throw an issue
                    if($this->stockLevels) {
                        foreach($this->stockLevels as $level) {
                            if ($level->item->item_number === $product->article_number) {
                                $stockLevelToday = $level->level_today;
                                $stockLevelHistoric = $level->level_historic;
                                $stockDispatched = $level->dispatched;
                                $stockReceived = $level->goods_in;
                                break;
                            }
                        }
                    }


                    $exportItem[] = $stockLevelToday;
                    $exportItem[] = $stockLevelHistoric;
                    $exportItem[] = $stockReceived;
                    $exportItem[] = $stockDispatched;
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

    public function getStock()
    {

        if($this->stockLevelRequired === true) {
            $client = new Client([
                'base_uri' => config('raf.base_uri'),
                'headers' => [
                    'Authorization' => 'Bearer ' . config('raf.api_key')
                ]
            ]);

            $response = $client->request('GET', '/api/stock-export', [
                'query' => [
                    'items' => collect($this->products)->pluck('article_number')->toArray(),
                    'selected_date' => $this->stockTakeStartDate->format('Y-m-d')
                ]]);

            $this->stockLevels = json_decode($response->getBody());
        }
    }

    /**
     *
     * @param $products
     * @return void
     */
    public function setProducts($products)
    {
        $this->products = $products;
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