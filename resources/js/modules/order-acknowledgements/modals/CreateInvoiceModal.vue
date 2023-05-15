<template>
    <b-modal title="Create Invoice" size="xl" ref="modal" centered ok-title="Create" @ok.prevent="create()">
        <div class="row">
            <div class="col-12 col-md-4">
                <b-form-group label="Supplier Invoice Reference">
                    <b-form-input v-model="invoice.invoice_reference" />
                </b-form-group>
            </div>
            <div class="col-12 col-md-4">
                <b-form-group label="Invoice Date">
                    <b-form-datepicker
                        v-model="invoice.invoice_date"
                        :date-format-options="{ year: '2-digit', month: '2-digit', day: '2-digit'}"
                    />
                </b-form-group>
            </div>
            <div class="col-12 col-md-4">
                <b-form-group label="Pricing Priority / All">
                    <b-form-select @input="setAllPriority($event)">
                        <b-form-select-option value="low">Low</b-form-select-option>
                        <b-form-select-option value="medium">Medium</b-form-select-option>
                        <b-form-select-option value="high">High</b-form-select-option>
                    </b-form-select>
                </b-form-group>
            </div>
        </div>
        <b-table v-if="invoice.lines" :fields="fields" :items="invoice.lines">
            <template #cell(selected)="data">
                <b-form-checkbox v-model="data.item.checked"></b-form-checkbox>
            </template>
            <template #cell(photo)="data">
                <img style="max-width: 100%; max-height: 100px" v-if="data.item.product.media && data.item.product.media[0]" :src="data.item.product.media[0].url" />
            </template>
            <template #cell(item_number)="data">
                <b-form-input readonly v-model="data.item.item_number" />
            </template>
            <template #cell(name)="data">
                <b-form-input readonly v-model="data.item.name" />
            </template>
            <template #cell(barcode)="data">
                <b-form-input readonly v-model="data.item.barcode" />
            </template>
            <template #cell(qty_received)="data">
                <b-form-input :readonly="!data.item.checked" type="number" v-model="data.item.invoice_qty_received" />
            </template>
            <template #cell(ordered)="data">
                <b-form-input readonly v-model="data.item.units_requested" />
            </template>
            <template #cell(unit_price)="data">
                <b-form-input :readonly="!data.item.checked" type="number" v-model="data.item.invoice_unit_price" />
            </template>
            <template #cell(priority)="data">
                <b-form-select :disabled="!data.item.checked" :rerenderCount="rerenderCounter" v-model="data.item.priority">
                    <b-select-option value="low">Low</b-select-option>
                    <b-select-option value="medium">Medium</b-select-option>
                    <b-select-option value="high">High</b-select-option>
                </b-form-select>
            </template>
            <template #cell(errors)="data">
                <b-badge v-show="data.item.invoice_qty_received > data.item.qty_remaining" variant="danger">
                    Too many
                </b-badge>
                <b-badge variant="danger" v-show="data.item.invoice_unit_price > 0 && (parseFloat(data.item.invoice_unit_price) !== parseFloat(data.item.unit_price))">
                    Price mismatch
                </b-badge>
            </template>
        </b-table>
    </b-modal>
</template>

<script>
export default {
    name: "CreateInvoiceModal",
    props: {
        salesOrder: Object
    },
    computed: {
        fields(){
            return [
                {key: 'selected', label: '', tdClass: 'align-middle', thClass: 'align-middle'},
                {key: 'photo', label: 'Photo', tdClass: 'align-middle', thClass: 'align-middle'},
                {key: 'item_number', label: 'Item #', tdClass: 'align-middle', thClass: 'align-middle'},
                {key: 'name', label: 'Product Details', tdClass: 'align-middle', thClass: 'align-middle'},
                {key: 'barcode', label: 'Barcode', tdClass: 'align-middle', thClass: 'align-middle'},
                {key: 'qty_received', label: 'Received', tdClass: 'align-middle', thClass: 'align-middle w-5    '},
                {key: 'ordered', label: 'Ordered', tdClass: 'align-middle', thClass: 'align-middle w-10'},
                {key: 'unit_price', label: 'Unit Price', tdClass: 'align-middle', thClass: 'align-middle w-10'},
                {key: 'priority', label: 'Priority', tdClass: 'align-middle', thClass: 'align-middle w-10'},
                {key: 'errors', label: '', tdClass: 'align-middle', thClass: 'align-middle w-5'},
            ]
        }
    },
    methods: {
        setAllPriority(newPriority) {
            for (let i = 0; i < this.invoice.lines.length; i++) {
                this.invoice.lines[i].priority = newPriority;
            }
            // force Vue to notice the nested change on priority
            // by default, this.invoice.lines[i].priority won't
            // be watched for changes, just this.invoice
            this.rerenderCounter++;
        },
        create(){
            const loading = this.$loading.show();
            const url = '/api/invoices'
            this.$http.post(url, this.invoice).then(response => {
                loading.hide()
                window.location.href = `/invoices/${response.data.id}/edit`
            }).catch(error => {
                this.$handleExceptionResponse(error, true)
                loading.hide();
            })
        },
        open(){
            this.$refs['modal'].show();
            // use cloneDeep so that lines get cloned, rather than referenced
            this.invoice = _.cloneDeep(this.salesOrder);
            _.forEach(this.invoice.lines, function(line) {
                line.priority = null;
            });
        },
        close(){
            this.$refs['modal'].hide();
        }
    },
    data(){
        return {
            invoice: {},
            allPriority: null,
            rerenderCounter: 0
        }
    }
}
</script>

<style scoped>

</style>