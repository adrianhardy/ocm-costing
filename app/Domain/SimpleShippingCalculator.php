<?php


namespace App\Domain;


class SimpleShippingCalculator
{
    /**
     * @var Dimensions
     */
    private $caseVol;

    /**
     * @var float
     */
    private $unitPrice;

    /**
     * @var float
     */
    private $costToShipPerCubicUnit;

    /**
     * @var int
     */
    private $unitsPerCase;

    /**
     * @var string
     */
    private $costToShipUnit;

    public function __construct($costToShipPerCubicUnit, $unit)
    {
        $this->setCostToShipPerCubicUnit($costToShipPerCubicUnit);
        $this->costToShipUnit = $unit;
    }

    /**
     * @return Dimensions
     */
    public function getCaseVol(): Dimensions
    {
        return $this->caseVol;
    }

    /**
     * @param Dimensions $caseVol
     * @return $this
     */
    public function setCaseVol(Dimensions $caseVol)
    {
        $this->caseVol = $caseVol;
        return $this;
    }

    /**
     * @return float
     */
    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    /**
     * @param float $unitPrice
     * @return $this
     */
    public function setUnitPrice(float $unitPrice)
    {
        $this->unitPrice = $unitPrice;
        return $this;
    }

    /**
     * @return float
     */
    public function getCostToShipPerCubicUnit(): float
    {
        return $this->costToShipPerCubicUnit;
    }

    /**
     * @param float $costToShipPerCubicUnit
     * @return $this
     */
    public function setCostToShipPerCubicUnit(float $costToShipPerCubicUnit)
    {
        $this->costToShipPerCubicUnit = $costToShipPerCubicUnit;
        return $this;
    }

    /**
     * @return int
     */
    public function getUnitsPerCase(): int
    {
        return $this->unitsPerCase;
    }

    /**
     * @param int $unitsPerCase
     */
    public function setUnitsPerCase(int $unitsPerCase): void
    {
        $this->unitsPerCase = $unitsPerCase;
    }

    /**
     * @return float|int
     */
    public function calculateTotalCostPerUnit()
    {
        switch ($this->costToShipUnit) {
            case 'metric':
                $cubicM = $this->caseVol->asMetric()->volume('metric');
                $totalCost = $this->getCostToShipPerCubicUnit()*$cubicM;
                break;
            case 'imperial':
                $cubicFt = $this->caseVol->asMetric()->volume('imperial');
                $totalCost = $this->getCostToShipPerCubicUnit()*$cubicFt;
                break;
        }
        return $totalCost / $this->getUnitsPerCase();
    }
}
