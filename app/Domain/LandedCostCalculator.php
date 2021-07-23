<?php


namespace App\Domain;


class LandedCostCalculator
{
    private $importDuty;
    private $commissionRate;
    private $currencyConversionRate;

    public function __construct($importDuty, $commissionRate)
    {
        $this->importDuty = $importDuty;
        $this->commissionRate = $commissionRate;
        $this->currencyConversionRate = 1;
    }

    /**
     * @param $rate
     * @return $this
     */
    public function setCurrencyConversionRate($rate)
    {
        $this->currencyConversionRate = $rate;
        return $this;
    }

    /**
     * @param $unitPrice
     * @return float|int
     */
    public function calculatedLandedCostPerUnit($unitPrice)
    {
        $importDuty = 1 + ($this->importDuty/100);
        $commission = 1 + ($this->commissionRate/100);
        return ($unitPrice * $commission * $importDuty) / $this->currencyConversionRate;
    }
}
