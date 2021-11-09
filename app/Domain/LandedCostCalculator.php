<?php


namespace App\Domain;


use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;

class LandedCostCalculator
{

    /**
     * @param SalesOrderLine $line
     * @param $qtyOnBoat
     * @param $invoiceAmount
     * @return array
     */
    public function calculateFromSalesOrderLine(SalesOrderLine $line, $qtyOnBoat, $invoiceAmount)
    {
        $salesOrder = $line->sales_order;
        $goodsTotal = $line->unit_price * $qtyOnBoat;
        $shippingCost = $invoiceAmount - $goodsTotal;
        $shippingCostPerUnit = $shippingCost / $qtyOnBoat;
        $fx = $salesOrder->fx_rate;

        return [
            'qty_shipped' => $qtyOnBoat,
            'goods_cost' => $goodsTotal,
            'goods_cost_gbp' => $goodsTotal * $fx,
            'shipping_cost_gbp' =>  $shippingCost * $fx,
            'shipping_cost_gbp_per_unit' => $shippingCostPerUnit * $fx,
            'est_landed_cost_per_unit' => $line->unit_price + $shippingCostPerUnit,
            'est_landed_cost_per_unit_gbp' => $line->unit_price_gbp + ($shippingCostPerUnit * $fx),
            'invoice_amt' => $invoiceAmount
        ];
    }
}
