<?php


namespace App\Domain;

use App\Models\Currency;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;

class LandedCostCalculator
{

    /**
     * @var
     */
    private $shippingCostPerCubicFootGbp;

    public function __construct(
        $shippingCostPerCubicFootGbp
    ) {
        $this->shippingCostPerCubicFootGbp = $shippingCostPerCubicFootGbp;
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
        $shippingCostGbp = $cubicFeet * $this->shippingCostPerCubicFootGbp;
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
        $shipping = $this->calculate($cubicFeet, $unitCount);

        $shippingCostPerUnitGbp = $shipping['shipping_cost_per_unit_gbp'];

        $commissionUplift = 1 + (setting('default_commission_rate_pc')/100);
        $importDutyUplift = 1 + (setting('default_import_duty_pc')/100);

        /** @var Currency $usd */
        $usd = Currency::query()->where('code', 'USD')->first();

        $importCostGbp = ($unitPrice * $usd->default_fx_to_gbp);
        $costToShipGbp = $importCostGbp + $shippingCostPerUnitGbp;
        if (!$excludedFromCommission) {
            $costToShipGbp = $costToShipGbp * $commissionUplift;
        }

        $costToShipGbp = $costToShipGbp * $importDutyUplift;

        return [
            'shipping_cost_gbp'             => $shipping['shipping_cost_gbp'],
            'shipping_cost_per_unit_gbp'    => $shippingCostPerUnitGbp,
            'est_landed_cost_per_unit_gbp'  => $costToShipGbp,
        ];

    }
}
