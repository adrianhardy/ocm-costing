<template>
    <b-modal ref="modal" no-close-on-backdrop title="Create new Sales Order" @ok="save" ok-title="Create">
        <b-form-group label="Supplier" v-if="!isSupplier">
            <supplier-select v-model="supplier" response-include="currency" @input="currency = supplier.currency"/>
        </b-form-group>
        <b-form-group label="Factory">
            <factory-select v-model="factory"></factory-select>
        </b-form-group>
        <b-form-group label="Currency">
            <currency-select v-model="currency"/>
        </b-form-group>
        <b-form-group label="Reference" description="This can be changed later">
            <b-form-input v-model="reference"/>
        </b-form-group>
    </b-modal>
</template>

<script>
import SupplierSelect from "../../components/SupplierSelect";
import CurrencySelect from "../../components/CurrencySelect";
import FactorySelect from "../../components/FactorySelect";

export default {
    name: "CreateSalesOrderModal",
    components: {CurrencySelect, SupplierSelect, FactorySelect},
    props: {isSupplier: Boolean, selected: Array},
    methods: {
        open() {
            this.reference = '';
            this.currency = {};
            this.supplier = {};
            this.$refs['modal'].show();
        },
        close() {
            this.$refs['modal'].hide();
        },
        save($event) {
            $event.preventDefault();
            const loader = this.$loading.show();
            this.$http.post(`/api/sales-orders`, {
                reference: this.reference,
                supplier: this.supplier,
                currency: this.currency,
                factory: this.factory,
                products_ids: this.selected?this.selected:''
            }).then(response => {
                window.location.href = `/sales-orders/${response.data.id}/edit`;
            }).catch(error => {
                loader.hide();
                this.$handleExceptionResponse(error, true);
            })
        }
    },
    data() {
        return {
            currency: {},
            supplier: {},
            reference: '',
            factory: {}
        }
    }
}
</script>

<style scoped>

</style>
