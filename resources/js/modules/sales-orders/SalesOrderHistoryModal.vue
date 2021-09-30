<template>
    <b-modal ref="modal" title="Sales Order History" ok-only size="xl">
        <b-table-simple>
            <b-thead>
                <b-tr>
                    <b-th>Product</b-th>
                    <b-th>Article #</b-th>
                    <b-th>Item #</b-th>
                    <b-th>Packing Instructions</b-th>
                    <b-th>Unit Price</b-th>
                    <b-th>Units</b-th>
                </b-tr>
                <b-tr v-for="line in current.model_data.lines" :key="line.id">
                    <b-td>{{ line.product.name }}</b-td>
                    <b-td>{{ line.article_number }}</b-td>
                    <b-td>{{ line.item_number }}</b-td>
                    <b-td>{{ line.packing_instructions }}</b-td>
                    <b-td>{{ line.unit_price }}</b-td>
                    <b-td>{{ line.units_requested }}</b-td>
                </b-tr>
            </b-thead>
        </b-table-simple>
        <template #modal-footer="{ok}">
            <div v-if="current && current.created_at">
                <b-input-group>
                    <b-button :disabled="currentIndex <= 0" @click="currentIndex--">
                        <b-icon-arrow-left/>
                    </b-button>
                    <b-input-group-text>
                        {{ new Date(current.created_at) | date('dd/MM/yy HH:mm') }}
                    </b-input-group-text>
                    <b-button @click="currentIndex++" :disabled="currentIndex >= history.length - 1">
                        <b-icon-arrow-right/>
                    </b-button>
                    <b-input-group-text>{{ current.user.name }}</b-input-group-text>
                    <b-button variant="primary" @click="ok" class="ml-2">Close</b-button>
                </b-input-group>
            </div>
        </template>
    </b-modal>
</template>

<script>
import {dateFilter} from "vue-date-fns";

export default {
    name: "SalesOrderHistoryModal",
    props: {salesOrder: Object},
    filters: {
        date: dateFilter
    },
    computed: {
        current() {
            if (!this.history[this.currentIndex]) {
                return {
                    model_data: {}
                }
            }
            return this.history[this.currentIndex]
        }
    },
    methods: {
        open() {
            this.$refs['modal'].show();
            const url = `/api/sales-orders/${this.salesOrder.id}/history`;
            this.$http.get(url, {params: {include:'user'}}).then(response => {
                this.history = response.data.items;
                this.currentIndex = 0;
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
            })
        },
        close() {
            this.$refs['modal'].hide();
        }
    },
    data() {
        return {
            history: [],
            currentIndex: 0
        }
    }
}
</script>

<style scoped>

</style>
