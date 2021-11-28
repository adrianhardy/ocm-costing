<template>
    <div>
        <b-card class="shadow-sm">
            <template #header>
                <table-header v-model="pagination">
                    <slot>
                        <search-input v-model="search" @search="$refs['table'].refresh()"/>
                        <b-button @click="location.href='/admin/imports/create'">Import</b-button>
                        <b-button variant="primary" @click="location.href='/products/create'">Create</b-button>
                    </slot>
                </table-header>
            </template>
            <b-table ref="table" :current-page="pagination.currentPage" hover :busy.sync="loading" :fields="fields" :items="items" sort-by="name">
                <template #table-busy>
                    <div class="text-center my-2">
                        <b-spinner class="align-middle" type="grow" variant="success"></b-spinner>
                    </div>
                </template>
                <template #cell(name)="data">
                    <a :href="`/products/${data.item.id}/edit`">{{ data.value }}</a>
                </template>
                <template #cell(image)="data">
                    <b-avatar v-if="data.item.media[0]">
                        <b-img fluid :src="data.item.media[0].url" />
                    </b-avatar>
                </template>
                <template #cell(est_landed_cost_gbp)="data">
                  <span v-if="data.value">
                    {{ data.value | cash_value }}
                  </span>
                </template>
            </b-table>
        </b-card>
    </div>
</template>

<script>
import TableHeader from '../../components/table/TableHeader';
import SearchInput from "../../components/SearchInput";
export default {
    name: "ProductsList",
    components: {SearchInput, TableHeader},
    props: {
        isSupplier: Boolean
    },
    computed: {
        fields() {
            let fields = [
                {
                    key: 'article_number',
                    sortable: true

                },{
                    key: 'item_number',
                    sortable: true
                },{
                    key: 'image',
                },{
                    key: 'name',
                    sortable: true
                },{
                    key: 'barcode',
                    sortable: true
                }
            ];

            if (!this.isSupplier) {
                fields.push({
                    key: 'est_landed_cost_gbp',
                    label: 'ELC GBP',
                    sortable: true
                });
            }

            return fields;
        }
    },
    methods: {
        items(ctx) {
            return this.$paginatedFetch(
                '/api/products',
                this.pagination,
                ctx, {
                    search:this.search,
                    include: 'quotes_lines,media'
                }
            );
        }
    },
    data() {
        return {
            search: '',
            pagination: {
                total: 0,
                perPage: 100,
                currentPage: 1,
            },
            loading: false
        }
    }
}
</script>

<style scoped>

</style>
