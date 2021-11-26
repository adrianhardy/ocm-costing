export default {
    /**
     *
     * @param volume_cubic_ft
     * @param unitPriceUsd
     * @param exchange_rate_usd
     * @param shipping_cost_per_cubic_foot
     * @param commissionRatePc
     * @param importDutyPc
     * @param unitsPerCase
     * @returns {number}
     */
    calculate(
        volume_cubic_ft,
        unitPriceUsd,
        exchange_rate_usd,
        shipping_cost_per_cubic_foot,
        commissionRatePc,
        importDutyPc,
        unitsPerCase
    ) {
        const shippingCostUsd = shipping_cost_per_cubic_foot / exchange_rate_usd;
        const commissionUplift = 1 + (commissionRatePc/100);
        const importDutyUplift = 1 + (importDutyPc/100);

        const importCostUsd = unitPriceUsd  * importDutyUplift;
        let  costToShipUsd = (importCostUsd + (volume_cubic_ft * shippingCostUsd))  / unitsPerCase;

        if (commissionRatePc > 0) {
            costToShipUsd = costToShipUsd * commissionUplift;
        }

        return costToShipUsd * exchange_rate_usd;
    }
}
