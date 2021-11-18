<template>
  <b-modal ref="modal" title="Users with access to this Sales Order" ok-only size="xl" ok-title="Close">
    <div class="row">
      <div class="col-12 col-md-8">
        <b-table-simple>
          <b-thead>
            <b-tr>
              <b-th></b-th>
              <b-th>Name</b-th>
              <b-th>Email</b-th>
              <b-th>Expires At</b-th>
              <b-th>Used At</b-th>
            </b-tr>
            <b-tr v-if="tokens.length < 1">
              <b-td colspan="4">No supplier access has been granted</b-td>
            </b-tr>
            <b-tr v-for="token in tokens" :key="token.id">
              <b-td><a href="#" @click.prevent="trash(token)"><b-icon-x class="text-danger" /></a></b-td>
              <b-td>{{ token.user.name }}</b-td>
              <b-td>{{ token.user.email }}</b-td>
              <b-td>{{ new Date(token.expires_at) | date('dd/MM/yy HH:mm') }}</b-td>
              <b-td>
                <span v-if="token.consumed_at">
                  {{ token.consumed_at | date('dd/MM/yy HH:mm') }}
                </span>
              </b-td>
            </b-tr>
          </b-thead>
        </b-table-simple>
      </div>
      <div class="col-12 col-md-4">
        <h6>Invite suppliers to edit this sales order</h6>
        <p>Search for the supplier user below and then grant them access for the period specified:</p>
        <div class="row">
          <div class="col-12">
            <b-form-group label="User">
              <user-select v-model="invitee" filter-role="supplier"/>
            </b-form-group>
          </div>
          <div class="col-12">
            <b-form-group label="Period">
              <b-form-select v-model="period">
                <b-form-select-option value="24">24 Hours</b-form-select-option>
                <b-form-select-option value="48">48 Hours</b-form-select-option>
                <b-form-select-option value="168">7 Days</b-form-select-option>
                <b-form-select-option value="336">14 Days</b-form-select-option>
                <b-form-select-option value="504">21 Days</b-form-select-option>
              </b-form-select>
            </b-form-group>
          </div>
          <div class="col-12">
            <b-form-checkbox v-model="allowAll">
              Provide access to products and sales orders
            </b-form-checkbox>
          </div>
          <div class="col-12 mt-2">
            <b-button variant="success" size="lg" block @click="add">Add</b-button>
          </div>
        </div>
      </div>
    </div>
  </b-modal>
</template>

<script>
import {dateFilter} from "vue-date-fns";
import UserSelect from "../../components/UserSelect";

export default {
  name: "SupplierAccessModal",
  components: {UserSelect},
  props: {salesOrder: Object},
  filters: {
    date: dateFilter
  },
  methods: {
    callXhr(method, url, payload) {
      const loading = this.$loading.show();
      this.$http({
        url: url,
        method: method,
        data: payload,
        params: {include: 'user'}
      }).then(response => {
        this.tokens = response.data.items;
        loading.hide();
      }).catch(error => {
        console.log(error);
        this.$handleExceptionResponse(error, true);
        loading.hide();
      });
    },
    open() {
      this.$refs['modal'].show();
      const url = `/api/sales-orders/${this.salesOrder.id}/tokens`;
      this.callXhr('get', url, null);
    },
    trash(token) {
      const url = `/api/sales-orders/${this.salesOrder.id}/tokens/${token.id}`;
      this.callXhr('delete', url, null);
    },
    add() {
      const url = `/api/sales-orders/${this.salesOrder.id}/tokens`;
      this.callXhr('post', url, {
        user: this.invitee,
        period: this.period,
        allow_all: this.allowAll
      });
    },
    close() {
      this.$refs['modal'].hide();
    }
  },
  data() {
    return {
      period: 24,
      allowAll: false,
      invitee: null,
      tokens: []
    }
  }
}
</script>

<style scoped>

</style>
