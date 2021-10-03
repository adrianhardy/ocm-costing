export default {
    install(Vue) {
        Vue.filter('dotdotdot', (value, max = 30) => {

            if (!value) {
                return value;
            }

            if (value.length > max) {
                return value.slice(0, max) + '...';
            }
            return value;
        });
    }
}
