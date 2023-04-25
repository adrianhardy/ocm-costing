<template>
    <div>
        <table-header v-model="pagination" />
        <b-table
            ref="table"
            :current-page="pagination.currentPage"
            hover
            :fields="fields"
            :items="items"
            sort-by="reference"
            show-empty
            empty-text="No Order Acknowledgements To Show"
        >
            <template #table-busy>
                <div class="text-center my-2">
                    <b-spinner class="align-middle" type="grow" variant="success"></b-spinner>
                </div>
            </template>
            <template #cell(reference)="data">
                <a :href="`/order-acknowledgements/${data.item.id}/edit`">{{ data.value }}</a>
            </template>
            <template #cell(date)="data">
                <span>{{ new Date(data.item.created_at) | date('dd/MM/yy HH:mm') }}</span>
            </template>
        </b-table>
    </div>
</template>

<script>
import {dateFilter} from "vue-date-fns";
import SupplierSelect from "../../../components/SupplierSelect";
import SearchInput from "../../../components/SearchInput";
import CreateSalesOrderModal from "../../sales-orders/CreateSalesOrderModal";
import TableHeader from "../../../components/table/TableHeader";

export default {
    name: "OrderAcknowledgementHistory",
    props: {
        productId: Number
    },
    filters: {
        date: dateFilter
    },
    components: {SupplierSelect, SearchInput, CreateSalesOrderModal, TableHeader},
    computed: {
        fields() {
            return [
                {
                    key: 'date',
                    label: 'Date',
                    tdClass: 'align-middle',
                    thClass: 'align-middle'
                }, {
                    key: 'supplier.name',
                    sortable: true,
                    label: 'Supplier',
                    tdClass: 'align-middle',
                    thClass: 'align-middle'
                }, {
                    key: 'reference',
                    label: 'Our Reference',
                    sortable: true,
                    tdClass: 'align-middle',
                    thClass: 'align-middle'
                },  {
                    key: 'supplier_reference',
                    label: 'Supplier Reference',
                    sortable: true,
                    tdClass: 'align-middle',
                    thClass: 'align-middle'
                },
            ];
        }
    },
    methods: {
        items(ctx) {
            const url = `/api/order-acknowledgements`;
            return this.$paginatedFetch(
                url,
                this.pagination,
                ctx, {
                    search: this.search,
                    stage: this.stage,
                    include: 'supplier',
                    product_id: this.productId
                }
            );
        }
    },
    data() {
        return {
            search: '',
            stage: this.initStage,
            pagination: {
                total: 0,
                perPage: 100,
                currentPage: 1,
            },
            modal: {
                supplier: {},
                our_reference: null,
                supplier_reference: null
            }
        }
    }
}
</script>

<style scoped>

</style>