const dateFormatter = new Intl.DateTimeFormat('en-GB', { dateStyle: 'short'});

export default {
    install(Vue) {
        Vue.filter('date_dmY', date => {
            return dateFormatter.format(new Date(date));
        });
    }
}
