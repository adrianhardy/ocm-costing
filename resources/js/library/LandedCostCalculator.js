export default {

    /**
     *
     * @param caseVolumeCubicFt
     * @param unitCostUsd
     * @param shippingCostPerCubicFoot
     * @param gbpToUsdExchangeRate
     * @param commissionRatePc
     * @param importDutyPc
     * @param isExcludedFromCommission
     * @param unitsPerCase
     * @returns {number} landed cost in gbp
     */
    calculate(
        caseVolumeCubicFt,
        unitCostUsd,
        shippingCostPerCubicFoot,
        gbpToUsdExchangeRate,
        commissionRatePc,
        importDutyPc,
        isExcludedFromCommission,
        unitsPerCase
    ) {
        const commissionUplift = 1 + (commissionRatePc/100);
        const importDutyUplift = 1 + (importDutyPc/100);
        const shippingCostGbp = caseVolumeCubicFt * shippingCostPerCubicFoot;
        const shippingCostPerUnitGbp = shippingCostGbp / unitsPerCase;
        const unitCostGbp = unitCostUsd / gbpToUsdExchangeRate;
        const unitCostWithCommissionGbp = unitCostGbp * commissionUplift;
        const costAfterCommission = (unitCostWithCommissionGbp + shippingCostPerUnitGbp) * importDutyUplift;
        return costAfterCommission.toFixed(2);
    }
}
