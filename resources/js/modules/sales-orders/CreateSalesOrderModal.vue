<template>
  <b-modal ref="modal" title="Create new Sales Order" @ok="save" ok-title="Create">
    <b-form-group label="Supplier">
      <supplier-select v-model="supplier" />
    </b-form-group>
    <b-form-group label="Currency">
      <currency-select v-model="currency" />
    </b-form-group>
    <b-form-group label="Reference" description="This can be changed later">
      <b-form-input v-model="reference" />
    </b-form-group>
  </b-modal>
</template>

<script>
import SupplierSelect from "../../components/SupplierSelect";
import CurrencySelect from "../../components/CurrencySelect";
export default {
  name: "CreateSalesOrderModal",
  components: {CurrencySelect, SupplierSelect},
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
        currency: this.currency
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
      reference: ''
    }
  }
}
</script>

<style scoped>

</style>
