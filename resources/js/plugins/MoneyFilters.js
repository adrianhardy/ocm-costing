const moneyFormat = new Intl.NumberFormat('en-GB', {
    currency: 'GBP',
    style: 'currency'
});

const eurMoneyFormat = new Intl.NumberFormat('en-GB', {
    currency: 'EUR',
    style: 'currency'
});

export default {
    install(Vue) {
        Vue.filter('cash_value', value => {
            if (isNaN(value)) {
                value = 0;
            }
            return moneyFormat.format(value);
        });
        Vue.filter('eur_cash_value', value => {
            if (isNaN(value)) {
                value = 0;
            }
            return eurMoneyFormat.format(value);
        });
        Vue.filter('2dp_value', value => {
            if (isNaN(value)) {
                value = 0;
            }
            value = parseFloat(value);
            return value?value.toFixed(2):0.00;
        });
    }
}
