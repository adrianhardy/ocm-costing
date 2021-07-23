<template>
    <div>
        <b-card class="shadow-sm">
            <template #header>
                <table-header v-model="pagination">
                    <slot>
                        <b-button href="/admin/imports/create">Import Products</b-button>
                    </slot>
                </table-header>
            </template>
            <b-table ref="table" :current-page="pagination.currentPage" hover :busy.sync="loading" :fields="fields" :items="items" sort-by="name">
                <template #table-busy>
                    <div class="text-center my-2">
                        <b-spinner class="align-middle" type="grow" variant="success"></b-spinner>
                    </div>
                </template>
                <template #cell(image)="data">
                    <b-avatar v-if="data.item.media[0]">
                        <b-img fluid :src="data.item.media[0].url" />
                    </b-avatar>
                </template>
            </b-table>
        </b-card>
    </div>
</template>

<script>
import TableHeader from '../../components/table/TableHeader';
export default {
    name: "ProductsList",
    components: {TableHeader},
    methods: {
        items(ctx) {
            return this.$paginatedFetch(
                '/api/products',
                this.pagination,
                ctx, {
                    include: 'quotes_lines,media'
                }
            );
        }
    },
    data() {
        return {
            pagination: {
                total: 0,
                perPage: 100,
                currentPage: 1,
            },
            loading: false,
            fields: [
                {
                    key: 'image'
                },
                {
                    key: 'name',
                    sortable: true
                },{
                    key: 'item_number',
                    sortable: true
                },{
                    key: 'article_number',
                    sortable: true
                },{
                    key: 'barcode',
                    sortable: true
                }
            ]
        }
    }
}
</script>

<style scoped>

</style>
