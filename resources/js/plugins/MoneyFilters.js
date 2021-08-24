const moneyFormat = new Intl.NumberFormat('en-GB', {
    currency: 'GBP',
    style: 'currency'
});

export default {
    install(Vue) {
        Vue.filter('cash_value', value => {
            return moneyFormat.format(value);
        });
        Vue.filter('2dp_value', value => {
            return value?value.toFixed(2):0.00;
        });
    }
}
