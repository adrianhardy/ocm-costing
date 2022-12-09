<?php


namespace App\Domain;

use App\Models\Currency;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use Illuminate\Support\Facades\Log;
use Monolog\Logger;

class LandedCostCalculator
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $log;

    protected $customUsdFxRate;

    /**
     * @var
     */
    private $shippingCostPerCubicFootGbp;

    public function __construct(
        $shippingCostPerCubicFootGbp,
        $customUsdFxRate = null
    ) {
        $this->log = Log::channel('elc');
        $this->shippingCostPerCubicFootGbp = $shippingCostPerCubicFootGbp;
        $this->customUsdFxRate = $customUsdFxRate;
    }

    public function setLogger(Logger $logger)
    {
        $this->log = $logger;
    }

    /**
     * @param SalesOrderLine $line
     * @param $unitCount
     * @param $invoiceAmount
     * @return array
     */
    public function calculate(
        $cubicFeet,
        $unitCount
    ) {

        if ($unitCount < 1) {
            return [
                'shipping_cost_gbp' => 0,
                'shipping_cost_per_unit_gbp' => 0
            ];
        }

        $shippingCostGbp = round($cubicFeet * $this->shippingCostPerCubicFootGbp, 4);
        $shippingCostPerUnit = $shippingCostGbp / $unitCount;

        return [
            'shipping_cost_gbp' =>  $shippingCostGbp,
            'shipping_cost_per_unit_gbp' => $shippingCostPerUnit,
        ];
    }

    public function calculateWithDuties(
        $cubicFeet,
        $unitCount,
        $unitPrice,
        $excludedFromCommission
    ) {

        $this->log->debug(__METHOD__);

        $shipping = $this->calculate($cubicFeet, $unitCount);
        $shippingCostPerUnitGbp = $shipping['shipping_cost_per_unit_gbp'];

        $commissionUplift = 1 + (setting('default_commission_rate_pc')/100);
        $importDutyUplift = 1 + (setting('default_import_duty_pc')/100);

        /** @var Currency $usd */
        $usd = Currency::query()->where('code', 'USD')->first();

        $usdFxRate = $usd->currency_fx_rate;
        if ($this->customUsdFxRate) {
            $usdFxRate = $this->customUsdFxRate;
        }

        $unitPriceGbp = round($unitPrice * (1/$usdFxRate), 2);
        $this->log->debug("Shipping Cost Per Carton: {$shipping['shipping_cost_gbp']}");
        $this->log->debug("Shipping Cost Per Unit GBP: {$shippingCostPerUnitGbp}");
        $this->log->debug("Cubic Ft: {$cubicFeet}");
        $this->log->debug("unit price : $unitPrice, FX Rate {$usdFxRate}, Unit Cost (raw): $unitPriceGbp");

        $commissionMultiplier = $excludedFromCommission?1:$commissionUplift;
        $importCostGbp = ($unitPriceGbp * $commissionMultiplier);

        $costToShipGbp = $importCostGbp + $shippingCostPerUnitGbp;
        $costToShipGbp = $costToShipGbp * $importDutyUplift;

        return [
            'shipping_cost_gbp'             => $shipping['shipping_cost_gbp'],
            'shipping_cost_per_unit_gbp'    => $shippingCostPerUnitGbp,
            'est_landed_cost_per_unit_gbp'  => $costToShipGbp,
        ];

    }
}
