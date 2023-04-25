<template>
    <div>
        <b-card class="shadow-sm">
            <template #header>
                <table-header v-model="pagination">
                    <slot>
                        <search-input class="w-50" v-model="search" @search="updateHash()">
                            <slot>
                                <b-dropdown class="align-middle float-right" size="lg" text="" variant="outline-secondary" right>
                                    <b-dropdown-form style="width:400px">
                                        <b-form-group label="Search by">
                                            <b-form-radio v-model="searchField" name="filter" value="" class="text-nowrap" @change="updateHash">Everything</b-form-radio>
                                            <b-form-radio v-model="searchField" name="filter" value="article_number:like" class="text-nowrap" @change="updateHash">By Article #</b-form-radio>
                                            <b-form-radio v-model="searchField" name="filter" value="item_number:like" class="text-nowrap" @change="updateHash">By Item #</b-form-radio>
                                            <b-form-radio v-model="searchField" name="filter" value="name:like" class="text-nowrap" @change="updateHash">By Name</b-form-radio>
                                            <b-form-radio v-model="searchField" name="filter" value="barcode:like" class="text-nowrap" @change="updateHash">By Barcode</b-form-radio>
                                        </b-form-group>
                                        <b-dropdown-divider/>
                                        <b-form-group label="Filter by Supplier">
                                            <factory-select v-model="searchFactory" @selected="factorySelect"/>
                                        </b-form-group>
                                        <b-dropdown-divider/>
                                        <b-form-group label="Stock Levels">
                                            <b-form-checkbox v-model="includeStockLevels">Include stock levels on export (warning: slow)</b-form-checkbox>
                                        </b-form-group>
                                    </b-dropdown-form>
                                </b-dropdown>
                            </slot>
                        </search-input>
                        <b-button variant="primary" class="ml-2" @click="exportProducts">Export</b-button>
                        <b-button class="ml-2" href="/products/create" variant="primary">Create</b-button>
                    </slot>
                </table-header>
            </template>
            <b-table
                ref="table"
                :current-page="pagination.currentPage"
                :per-page="pagination.perPage"
                hover
                :busy.sync="loading"
                :fields="fields"
                :items="items"
                sort-by="article_number"
            >
                <template #head(selected_for_export)="data">
                    <a href="#" @click.prevent="selectAllForExport(true)">All</a>
                </template>
                <template #cell(selected_for_export)="data, index">
                    <b-form-checkbox size="lg" :checked="selectedForExport.indexOf(data.item.id) >= 0" @change="selectForExport(data.item, $event)" />
                </template>
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
            <b-button @click="createSalesOrder" size="lg" block :disabled="selectedForExport.length < 1" variant="primary">
                <a v-if="selectedForExport < 1">No Products Selected</a>
                <a v-else>Create new Sales Order with {{selectedForExport.length}} products</a>
            </b-button>
        </b-card>
        <create-sales-order-modal ref="create-sales-order-modal" :selected="this.selectedForExport"></create-sales-order-modal>
    </div>
</template>

<script>
import TableHeader from '../../components/table/TableHeader';
import SearchInput from "../../components/SearchInput";
import FactorySelect from "../../components/FactorySelect";
import CreateSalesOrderModal from "../sales-orders/CreateSalesOrderModal";
export default {
    name: "ProductsList",
    components: {FactorySelect, SearchInput, TableHeader, CreateSalesOrderModal},
    props: {
        isSupplier: Boolean
    },
    mounted(){
        this.updateSearch();
    },
    watch: {
        $route($to, $from) {
            this.updateSearch();
            this.$refs['table'].refresh();
        }
    },
    computed: {
        localSearchPayload(){
            return {
                search: this.search?this.search:'',
                searchField: this.searchField?this.searchField:this.searchField,
                searchFactoryID: this.searchFactory?this.searchFactory.id:'',
                searchFactoryName: this.searchFactory?this.searchFactory.name:''
            }
        },
        fields() {
            let fields = [
                {
                    key: 'selected_for_export',
                    sortable: false

                },{
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
        async exportProducts(){
            let params = new URLSearchParams({
                include: this.include,
                includeStockLevels: this.includeStockLevels,
                export: true
            });

            if(this.search !== '') {
                params.append('search', this.search)
            }

            if(this.country) {
                params.append('search_factory_id', this.searchFactory.id)
            }

            const link = document.createElement('a');
            link.href = '/api/products?' + params.toString();
            link.download = 'products_export.csv';
            console.log(link);
            link.click();
        },
        createSalesOrder(){
            this.$refs['create-sales-order-modal'].open()
        },
        selectAllForExport(value) {
            if (value) {
                this.selectedForExport = _.uniq([...this.selectedForExport, ..._.map(this.products, 'id')]);
            }
        },
        selectForExport(product, value) {
            if (!value) {
                const idx = this.selectedForExport.indexOf(product.id);
                this.selectedForExport.splice(idx, 1);

            } else {
                this.selectedForExport.push(product.id);
            }
        },
        selectedRows() {
            return this.items.filter(item => item.selected)
        },
        factorySelect(){
            this.$nextTick(() => {
                this.updateHash();
            });
        },
        // when we click "search" we change the fragment in the url bar
        // based on our current selection - we also request a navigation
        // purely to plonk it in history
        updateHash() {
            this.$refs['table'].refresh();
            this.$nextTick(() => {
                const params = new URLSearchParams(this.localSearchPayload);
                window.location.hash = params.toString();
            })
        },
        updateSearch(){
            const parsedHash = new URLSearchParams(window.location.hash.substr(1))
            this.search = parsedHash.get('search')?parsedHash.get('search'):'';
            this.searchField = parsedHash.get('searchField')?parsedHash.get('searchField'):'';
            this.searchFactory = null;
            if (parsedHash.get('searchFactoryID')) {
                this.searchFactory = {
                    id: parsedHash.get('searchFactoryID'),
                    name: parsedHash.get('searchFactoryName')
                };
            }

        },

        items(ctx) {

            const payload = {
                search: this.search,
                search_factory_id: this.searchFactory?this.searchFactory.id:null,
                include: 'quotes_lines,media,factories',
            };

            if (this.searchField) {
                payload.searchFields = this.searchField;
            }

            const products =  this.$paginatedFetch(
                '/api/products',
                this.pagination,
                ctx, payload,{
                    'article_number' : 'sorted_article_number'
                }
            );
            this.products = products
            return products
        }
    },
    data() {
        return {
            selected: [],
            selectedForExport: [],
            search: '',
            searchField: 'article_number',
            searchFactory: null,
            products: [],
            pagination: {
                total: 0,
                perPage: 100,
                currentPage: 1,
            },
            includeStockLevels: false,
            loading: false
        }
    }
}
</script>

<style scoped>

</style>
