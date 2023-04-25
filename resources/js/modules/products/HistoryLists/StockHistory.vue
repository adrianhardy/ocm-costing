<template>
    <div>
        <b-table
            small
            ref="table"
            :current-page="pagination.currentPage"
            hover
            :fields="fields"
            :items="items"
            sort-by="reference"
            show-empty
            empty-text="No Invoices To Show"
        >
            <template #table-busy>
                <div class="text-center my-2">
                    <b-spinner class="align-middle" type="grow" variant="success"></b-spinner>
                </div>
            </template>
            <template #cell(date)="data">
                <span v-if="data.item.date">{{ new Date(data.item.date) | date('dd/MM/yy') }}</span>
            </template>
        </b-table>
    </div>
</template>

<script>
import TableHeader from "../../../components/table/TableHeader";
import SearchInput from "../../../components/SearchInput";
import {dateFilter} from "vue-date-fns";

export default {
    name: "InvoiceHistory",
    props: {
        productId: Number
    },
    components: {
        TableHeader, SearchInput
    },
    filters: {
        date: dateFilter
    },
    computed: {
        fields() {
            return [
                {key:'date', label: 'Date'},
                {key:'stock', label: 'Stock'}
            ];
        },
    },
    methods: {
        items(ctx) {
            const url = `/api/products/${this.productId}/stock-changes`;
            return this.$paginatedFetch(
                url,
                this.pagination,
                ctx, {
                    product_id: this.productId
                }
            );
        }
    },
    data() {
        return {
            search: '',
            stage: null,
            pagination: {
                total: 0,
                perPage: 100,
                currentPage: 1,
            },
        }
    }
}
</script>

<style scoped>

</style>