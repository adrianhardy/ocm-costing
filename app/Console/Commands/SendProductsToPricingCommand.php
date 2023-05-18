<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use App\Services\PricingService;
use Illuminate\Support\Facades\DB;

class SendProductsToPricingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pricing:products:send {--pretend}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send all products to pricing';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(PricingService $pricingService)
    {
        /** @var Product[] $products */
        $products = Product::query()
            // where no product pricings exist, create one.
            ->whereDoesntHave('product_pricings_product')
            ->get();

        DB::beginTransaction();
        foreach ($products as $product) {
            $this->comment("Sending Product #{$product->id} to pricing with ELC {$product->est_landed_cost_gbp}");
            $pricingService->sendToPricing($product);
        }
        DB::commit();
    }
}
