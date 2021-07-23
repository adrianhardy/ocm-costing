export default {
    install(Vue) {
        Vue.prototype.$paginatedFetch = function(url, pagination, ctx, params = {})
        {
            const payload = Object.assign({
                page: pagination.currentPage,
                sortedBy: ctx.sortDesc ? 'desc' : 'asc',
                orderBy: ctx.sortBy
            }, params);

            if (pagination.perPage > 0) {
                payload.per_page = pagination.perPage;
            }

            return this.$http
                .get(url, {params: payload})
                .then(response => {
                    pagination.currentPage = response.data.current_page;
                    pagination.total = response.data.total;
                    pagination.perPage = response.data.per_page;
                    return response.data.items || [];
                });
        }
    }
}
