export default {

    /**
     *
     * @param cubicFeet
     * @param unitPrice
     * @param shippingCostPerCubicFoot
     * @param gbpToUsdExchangeRate
     * @param commissionRatePc
     * @param importDutyPc
     * @param isExcludedFromCommission
     * @param unitsPerVolume
     * @returns {number} landed cost in gbp
     */
    calculate(
        cubicFeet,
        unitPrice,
        shippingCostPerCubicFoot,
        gbpToUsdExchangeRate,
        commissionRatePc,
        importDutyPc,
        isExcludedFromCommission,
        unitsPerVolume
    ) {
        const commissionUplift = 1 + (commissionRatePc/100);
        const importDutyUplift = 1 + (importDutyPc/100);

        const shippingCostGbp = cubicFeet * shippingCostPerCubicFoot;
        const shippingCostPerUnitGbp = shippingCostGbp / unitsPerVolume;

        let importCostGbp = (unitPrice / gbpToUsdExchangeRate);

        if (commissionRatePc > 0 && !isExcludedFromCommission) {
            importCostGbp = importCostGbp * commissionUplift;
        }

        let  landedCost = importCostGbp + shippingCostPerUnitGbp ;

        return landedCost * importDutyUplift;
    }
}
