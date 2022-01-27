<template>
    <div>
        <b-card class="shadow-sm">
            <template #header>
                <table-header v-model="pagination">
                    <slot>
                        <div class="w-30">
                            <search-input v-model="search" @search="$refs['table'].refresh()"/>
                        </div>
                        <b-dropdown class="align-middle float-right" size="lg" text="" variant="outline-secondary" right>
                            <b-dropdown-form style="width:400px">
                                <b-form-radio v-model="searchField" name="filter" value="article_number:like" class="text-nowrap" @change="$refs['table'].refresh()">By Article #</b-form-radio>
                                <b-form-radio v-model="searchField" name="filter" value="item_number:like" class="text-nowrap" @change="$refs['table'].refresh()">By Item #</b-form-radio>
                                <b-form-radio v-model="searchField" name="filter" value="name:like" class="text-nowrap" @change="$refs['table'].refresh()">By Name</b-form-radio>
                                <b-form-radio v-model="searchField" name="filter" value="barcode:like" class="text-nowrap" @change="$refs['table'].refresh()">By Barcode</b-form-radio>
                                <b-dropdown-divider/>
                                <factory-select v-model="searchFactory" @selected="$refs['table'].refresh()"/>
                            </b-dropdown-form>
                        </b-dropdown>
                        <b-button href="/admin/imports/create">Import</b-button>
                        <b-button variant="primary" href="/products/create">Create</b-button>
                    </slot>
                </table-header>
            </template>
            <b-table ref="table" :current-page="pagination.currentPage" hover :busy.sync="loading" :fields="fields" :items="items" sort-by="article_number">
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
import FactorySelect from "../../components/FactorySelect";
export default {
    name: "ProductsList",
    components: {FactorySelect, SearchInput, TableHeader},
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
                    search: this.search,
                    searchFields: this.searchField,
                    search_factory_id: this.searchFactory.id,
                    include: 'quotes_lines,media'
                },{
                    'article_number' : 'sorted_article_number'
                }
            );
        }
    },
    data() {
        return {
            search: '',
            searchField: null,
            searchFactory: {},
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
