export default {
    install(Vue) {
        /**
         *
         * @param url
         * @param pagination
         * @param ctx
         * @param params
         * @param mapping provide an option to map columns for sorting
         * @returns {Promise<AxiosResponse<any>>}
         */
        Vue.prototype.$paginatedFetch = function(url, pagination, ctx, params = {}, mapping = {})
        {
            const sortBy = mapping[ctx.sortBy]?mapping[ctx.sortBy]:ctx.sortBy;

            const payload = Object.assign({
                page: pagination.currentPage,
                sortedBy: ctx.sortDesc ? 'desc' : 'asc',
                orderBy: sortBy
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
