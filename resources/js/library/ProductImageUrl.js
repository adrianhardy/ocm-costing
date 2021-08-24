export default {
    first(product) {
        if (product.media && product.media[0]) {
            return product.media[0].url;
        }
        return 'https://via.placeholder.com/144x180';
    }
}
