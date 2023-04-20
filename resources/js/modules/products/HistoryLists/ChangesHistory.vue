<template>
    <b-table v-else ref="product-history" small :items="productLog" :fields="fields"
             sticky-header="600px">
        <template #cell(details)="row">
            <b-link @click="row.toggleDetails">
                <b-icon-chevron-down v-if="!row.detailsShowing" />
                <b-icon-chevron-up v-else />
            </b-link>
        </template>
        <template #row-details="data">
            <b-table-simple>
                <tr>
                    <th class="w-50">Field</th>
                    <th>New Value</th>
                </tr>
                <tr v-for="(property, index) in data.item.attributes">
                    <td>
                        {{index}}
                    </td>
                    <td>
                        {{property}}
                    </td>
                </tr>
            </b-table-simple>
        </template>
        <template #cell(updated_at)="data">
            {{ new Date(data.value) | date('dd/MM/yy HH:mm') }}
        </template>
        <template #cell(unit_price)="data">
            {{ data.value | 2dp_value }}
        </template>
        <template #cell(attributes.case_cubic_ft)="data">
                                    <span
                                        v-b-popover.hover="`${data.item.attributes.length_cm}cm x ${data.item.attributes.width_cm}cm x ${data.item.attributes.height_cm}cm`"
                                        title="LxWxH" style="cursor:pointer">
                                        {{ data.value }}
                                    </span>
        </template>
        <template #cell(landed_cost_per_unit_gbp)="data">
            {{ data.value | cash_value }}
        </template>
        <template #cell(extra)="data">
            <div v-if="data.item && data.item.attributes">
                                        <span v-b-popover.hover="data.item.attributes.primary_asin" title="ASIN"
                                              style="cursor:pointer">
                                        <b-icon-box-seam/>
                                      </span>
                <span v-b-popover.hover="data.item.attributes.primary_sku"
                      title="SKU" style="cursor:pointer">
                                        <b-icon-box-seam/>
                                      </span>
                <span v-b-popover.hover="data.item.attributes.packing_instructions"
                      title="Packing Instructions" style="cursor:pointer">
                                        <b-icon-box-seam/>
                                      </span>
                <span v-b-popover.hover="data.item.attributes.product_description"
                      title="Product Description" style="cursor:pointer">
                                        <b-icon-tag/>
                                      </span>
                <span style="cursor:pointer" v-b-popover.hover="data.item.attributes.barcode"
                      title="Barcode">
                                        <b-icon-upc-scan/>
                                      </span>
            </div>
        </template>
    </b-table>
</template>

<script>
export default {
    name: "ChangesHistory",
    props: {
        productId: Number,
        fields: {
            type: Array
        },
    },
    methods: {
        productLog() {
            const url = `/api/products/${this.productId}/history`;
            return this.$http.get(url).then(response => {
                return _.map(response.data.items, row => {
                    return {
                        updated_at: row.updated_at,
                        attributes: row.properties.attributes
                    }
                });
            });
        },
    }
}
</script>

<style scoped>

</style>