<template>
  <b-modal id="pricing" :title="value.product.name" ref="modal" ok-title="Save" @ok="save($event)" cancel-title="Close" ok-title-html="Update" centered size="xl">
    <div>
      <div class="row">
        <div class="col-12 col-md-6">
          <div>
            <h5>Carton</h5>
          </div>
          <div class="row"> <!-- Left Side of Modal -->
            <div class="col-4">
              <b-form-group label="Carton Quantity">
                <b-form-input readonly v-model="value.product.units_per_case"/>
              </b-form-group>
            </div>
            <div class="col-4">
              <b-form-group label="Inner Qty">
                <b-form-input readonly v-model="value.product.inner_qty"/>
              </b-form-group>
            </div>
            <div class="col-4">
              <b-form-group label="Case Weight">
                <b-form-input readonly v-model="value.product.case_weight_kg"/>
              </b-form-group>
            </div>
          </div>
          <div class="row">
            <div class="col-4">
              <b-form-group label="Length (cm)">
                <b-form-input readonly v-model="value.product.length_cm"/>
              </b-form-group>
            </div>
            <div class="col-4">
              <b-form-group label="Width (cm)">
                <b-form-input readonly v-model="value.product.width_cm"/>
              </b-form-group>
            </div>
            <div class="col-4">
              <b-form-group label="Height (cm)">
                <b-form-input readonly v-model="value.product.height_cm"/>
              </b-form-group>
            </div>
          </div>
          <h5>Unit</h5>
          <div class="row">
            <div class="col-6">
              <b-form-group label="Unit Price">
                <b-form-input readonly :value="value.product.unit_price"/>
              </b-form-group>
            </div>
            <div class="col-6">
              <b-form-group label="Unit Weight (Kg)">
                <b-form-input readonly v-model="value.product.unit_height_kg"/>
              </b-form-group>
            </div>
          </div>
          <div class="row">
            <div class="col-4">
              <b-form-group label="Length (cm)">
                <b-form-input readonly v-model="value.product.unit_length_cm"/>
              </b-form-group>
            </div>
            <div class="col-4">
              <b-form-group label="Width (cm)">
                <b-form-input readonly v-model="value.product.unit_width_cm"/>
              </b-form-group>
            </div>
            <div class="col-4">
              <b-form-group label="Height (cm)">
                <b-form-input readonly v-model="value.product.unit_height_cm"/>
              </b-form-group>
            </div>
          </div>
          <div>
            <h5>Image</h5>
            <div class="p-1">
              <b-img fluid  style="max-height: 200px" class="img-thumnail" :src="value.product | product_image_url"/>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <b-tabs content-class="pt-3">
            <b-tab title="Pricing">
              <h5>Pricing</h5>
              <div class="row">
                <div class="col-4">
                  <b-form-group label="Est. Landed Cost">
                    <b-input-group prepend="£">
                      <b-form-input readonly v-model="value.est_landed_cost_per_unit_gbp"/>
                    </b-input-group>
                  </b-form-group>
                </div>
                <div class="col-4">
                  <b-form-group label="Suggested Unit Price">
                    <b-input-group prepend="£">
                      <b-form-input readonly v-model="value.suggested_unit_price_gbp"/>
                    </b-input-group>
                  </b-form-group>
                </div>
                <div class="col-4">
                  <b-form-group label="Our Unit Price">
                    <b-input-group prepend="£">
                      <b-form-input v-model="value.unit_price_gbp"/>
                    </b-input-group>
                  </b-form-group>
                </div>
              </div>
              <h5>Costs</h5>
              <div class="row">
                <div class="col-4">
                  <b-form-group label="Marketplace Comm.">
                    <b-input-group prepend="%">
                      <b-form-input v-model="value.marketplace_commission_pc"/>
                    </b-input-group>
                  </b-form-group>
                </div>
                <div class="col-4">
                  <b-form-group label="Marketplace Fee">
                    <b-input-group prepend="£">
                      <b-form-input v-model="value.marketplace_fee"/>
                    </b-input-group>
                  </b-form-group>
                </div>
                <div class="col-4">
                  <b-form-group label="Unit Shipping">
                    <b-input-group prepend="£">
                      <b-form-input v-model="value.unit_shipping_gbp"/>
                    </b-input-group>
                  </b-form-group>
                </div>
              </div>
              <hr />
              <div class="d-flex justify-content-between">
                <div class="h5">
                  <label>Calculated Profit</label>
                  <div>{{ value.profit | cash_value }}</div>
                </div>
                <div class="h5">
                  <label>Margin</label>
                  <div>{{ (value.margin_pc * 100).toFixed(2) }}%</div>
                </div>
              </div>
            </b-tab>
            <b-tab title="History"> <!-- History Tab -->
              <b-table size="sm" ref="table" :fields="fields" :items="items">
                <template #cell(created_at)="data">
                  {{ data.value | date_dmY }}
                </template>
                <template #cell(approved_at)="data">
                  {{ data.value | date_dmY }}
                </template>
              </b-table>
            </b-tab>
          </b-tabs>
        </div>
      </div>
    </div>
  </b-modal>
</template>

<script>
export default {
  name: "PricingModal",
  methods: {
    save(event){
      event.preventDefault();
      this.update()
    },
    update(){
      this.$http.put(`/api/pricing/${this.value.id}`, {...this.value}, {params: {
          include: `product,product.media`
        }}).then(response => {
          this.value = response.data;
        this.$emit('updated');
      }).catch(error => {
        this.$handleExceptionResponse(error, true)
      })
    },
    open(line) {
      this.value = line;
      this.$http.get(`api/pricing/${this.value.id}`, {
        params: {
          include: `product,product.media`
        }
      }).then(response => {
        this.value = response.data
        this.$refs['modal'].show();
      }).catch(error => {
        this.$handleExceptionResponse(error, true)
      })
    },
    items(ctx){
      return this.$paginatedFetch(
          `api/pricing/${this.value.id}/history`,
          this.pagination,
          ctx, {
            include: 'approver'
          });
    },
    close() {
      this.$refs['modal'].hide();
    }
  },
  data(){
    return {
      value: {},
      pagination: {
        total: 0,
        perPage: 100,
        currentPage: 1,
        sortBy: 'approved_at'
      },
      fields: [
        {key: 'approved_at', label: 'Approved At', sortable: true},
        {key: 'approver.name', label: 'Approved By', sortable: true},
        {key: 'unit_price_gbp', label: 'Unit Price'},
        {key: 'profit', label: 'Profit'},
        {key: 'margin_pc', label: 'Margin'},

      ]
    }
  }
}
</script>

<style scoped>

</style>
