<?php

namespace App\Domain;

class ProductPricingViabilityChecker
{
    public function __construct()
    {

    }

    public function isViable($profit, $productCostGbp, $customMinimumProfit = null)
    {
        $minimumProfit = 2.50;

        if($customMinimumProfit) {
            $minimumProfit = $customMinimumProfit;
        } else {
            switch(true) {
                case $productCostGbp <= 9.99:
                    $minimumProfit = 2.50;
                    break;
                case $productCostGbp <= 19.99:
                    $minimumProfit = 6;
                    break;
                case $productCostGbp > 20:
                    $minimumProfit = 10;
                    break;
                default:
                    $minimumProfit = 2.50;
                    break;
            }
        }

        $viable = $profit >= $minimumProfit;

        return $viable;
    }
}