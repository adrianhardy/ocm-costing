<template>
  <b-modal ref="modal" size="lg" title="Update shipment information">
      <p>
          Using the shipping invoice received, please enter the shipping reference
          along with the quantities on each of the lines, and the amount invoiced.
      </p>
      <div class="row">
        <div class="col-6">
          <b-form-group label="Shipping Reference">
            <b-form-input v-model="shipping_reference" />
          </b-form-group>
        </div>
        <div class="col-6">
          <b-form-group label="Invoice Currency">
            <currency-select />
          </b-form-group>
        </div>
      </div>
      <b-table :items="lines" :fields="fields">
        <template #cell(qty_shipped)="data">
          <b-form-input v-model="data.item.qty_shipped" size="sm" />
        </template>
        <template #cell(invoice_amt)="data">
          <b-form-input v-model="data.item.invoice_amt" size="sm" />
        </template>
      </b-table>
  </b-modal>
</template>

<script>
import CurrencySelect from "../../components/CurrencySelect";
export default {
  name: "UpdateInboundShipmentsModal",
  components: {CurrencySelect},
  props: {
    lines: Array
  },
  data() {
    return {
      shipping_reference: ''
    }
  },
  computed: {
    fields() {
      return [{
        key: 'product.name',
        label: 'Product',
        thClass: 'w-30',
        tdClass: 'align-middle'
      }, {
        key: 'article_number',
        label: 'Art #',
        thClass: 'w-10 text-nowrap',
        tdClass: 'align-middle'
      }, {
        key: 'units_requested',
        label: 'Qty',
        thClass: 'w-7',
        tdClass: 'align-middle'
      }, {
        key: 'qty_shipped',
        label: 'Qty Shipped',
        tdClass: 'align-middle w-15'
      }, {
        key: 'invoice_amt',
        label: 'Invoice Amt',
        tdClass: 'align-middle w-15'
      }, {
        key: 'est_landed_cost',
        label: 'Est. Landed Cost',
        tdClass: 'align-middle'
      }]
    }
  },
  methods: {
    open() {
      this.$refs['modal'].show();
    },
    close() {
      this.$refs['modal'].hide();
    }
  }
}
</script>

<style scoped>

</style>
