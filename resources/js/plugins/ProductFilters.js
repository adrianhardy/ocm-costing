import media from '../library/ProductImageUrl';

export default {
    install(Vue) {
        Vue.filter('product_unit_price', product => {
            if (!product) {
                return 0;
            }
            return product.unit_price;
        });
        Vue.filter('product_image_url', product => {
            if (!product) {
                return;
            }
            return media.first(product);
        });
    }
}
