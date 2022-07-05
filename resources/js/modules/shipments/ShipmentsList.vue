<template>
    <div>
        <b-card class="shadow-sm">
            <template #header>
                <table-header v-model="pagination">
                    <slot>
                        <search-input class="w-50" v-model="search" @search="$refs['table'].refresh()"/>
                        <b-button>
                            Create New Shipment
                        </b-button>
                    </slot>
                </table-header>
            </template>
            <b-table
                ref="table"
                :fields="fields"
                :items="items"
                :current-page="pagination.currentPage"
                :per-page="pagination.perPage"
            >
                <template #cell(reference)="data">
                    <b-link :href="`/shipments/${data.item.id}`">
                        {{data.value}}
                    </b-link>
                </template>
                <template #cell(departed_at)="data">
                    <span v-if="data.value">
                        {{ data.value | date_dmY }}
                    </span>
                </template>
                <template #cell(sales_order.sales_order_number)="data">
                    <b-link :href="`/sales-orders/${data.value}/edit`">
                        {{data.value}}
                    </b-link>
                </template>
            </b-table>
        </b-card>
    </div>
</template>

<script>
import TableHeader from "../../components/table/TableHeader";
import SearchInput from "../../components/SearchInput";

export default {
    name: "ShipmentsList",
    components: {
        SearchInput,
        TableHeader
    },
    computed: {
        fields(){
            return [
                {key: 'reference', label: 'Reference'},
                {key: 'sales_order.sales_order_number', label: 'Sales Order #'},
                {key: 'departed_at', label: 'Departed'},
                {key: 'bl_number', label: 'BL #'},
                {key: 'container_number', label: 'Container #'},
                {key: 'vessel_name', label: 'Vessel'},
            ]
        },
        include(){
            return 'sales_order,sales_order_lines'
        }
    },
    methods: {
        items(ctx) {
            const url = `/api/shipments`;
            const include = this.include
            return this.$paginatedFetch(
                url,
                this.pagination,
                ctx, {
                    search: this.search,
                    include
                })
        }
    },
    data(){
        return {
            search: '',
            pagination: {
                total: 0,
                perPage: 100,
                currentPage: 1,
            }
        }
    }
}
</script>

<style scoped>

</style>
