<?php

namespace App\Listeners;

use App\Events\Interfaces\ModifiesEstimatedLandedCosts;
use App\Events\ShippingCostSettingChanged;
use App\Exceptions\ZeroUnitsPerCaseException;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateAllCurrentEstimatedLandedCosts
{

    private ProductService $productService;
    private \Psr\Log\LoggerInterface $log;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
        $this->log = Log::channel('elc');
    }

    /**
     * @param ShippingCostSettingChanged $event
     * @return void
     */
    public function handle(ShippingCostSettingChanged $event)
    {
        $prefix = class_basename(__METHOD__);
        $this->log->debug("$prefix New cost settings have come in, forcing a recalculation of all products...");
        DB::beginTransaction();
        Product::query()->chunk(500, function($products) use ($prefix) {
            /** @var Product[] $products */
            $this->log->debug("Recalculating chunk of " . count($products) . " products...");
            foreach ($products as $product) {
                try {
                    $before = $product->est_landed_cost_gbp;
                    $this->productService->updateEstimatedLandedCost($product);
                    $after = $product->est_landed_cost_gbp;
                    $this->log->debug("$prefix Product #{$product->id}/{$product->item_number} was $before, now $after");
                } catch (ZeroUnitsPerCaseException $ex) {
                    $this->log->debug("$prefix Product #{$product->id}/{$product->item_number} cannot be calculated, zero units per case");
                }
                $product->save();
            }
            $this->log->debug("Chunk completed!");
        });
        DB::commit();
        $this->log->debug("$prefix Finished recalculating ELC for all products");
    }
}
