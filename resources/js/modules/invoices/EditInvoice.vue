<template>
    <div>
        <b-card class="shadow-sm">
            <template #header>
                <div class="row w-100">
                    <div class="col-12 col-md-4">
                        <div>
                            <h4 class="m-0">
                                Invoice: {{ reference }}
                            </h4>
                            <b-badge variant="success" v-if="!invoice.is_approval_required && invoice.created_at">
                                Approved At:
                                {{ new Date(invoice.created_at) | date('dd/MM/yy HH:mm') }}
                            </b-badge>
                            <b-badge variant="success" v-if="invoice.approved_at">
                                Approved At:
                                {{ new Date(invoice.approved_at) | date('dd/MM/yy HH:mm') }}
                            </b-badge>
                        </div>
                    </div>
                    <div class="col-12 col-md-2 offset-md-8">
                        <b-form-group label="Invoice Date">
                            <b-form-datepicker :disabled="!invoice.is_approval_required || invoice.approved_at" v-model="invoice.invoice_date"/>
                        </b-form-group>
                    </div>
                    <div class="col-12 col-md-2">
                        <b-form-group  v-if="invoice.is_approval_required && !invoice.approved_at" label="Set Priority All">
                            <b-form-select class="w-100" @input="setAllPriority($event)">
                                <b-form-select-option value="low">Low</b-form-select-option>
                                <b-form-select-option value="medium">Medium</b-form-select-option>
                                <b-form-select-option value="high">High</b-form-select-option>
                            </b-form-select>
                        </b-form-group>
                    </div>
                </div>
            </template>
            <b-table :items="invoice.lines" :fields="fields">
                <template #cell(product)="data">
                    <span>{{ data.item.sales_order_line.product.name }} / {{ data.item.sales_order_line.product.primary_sku }}</span>
                </template>
                <template #cell(item_number)="data">
                    <span>{{data.item.sales_order_line.product.item_number}}</span>
                </template>
                <template #cell(qty)="data">
                    <b-form-input v-if="invoice.is_approval_required && !invoice.approved_at" :readonly="!canApprove"
                                  v-model="data.item.qty"/>
                    <span v-else>{{ data.item.qty }}</span>
                </template>
                <template #cell(unit_price)="data">
                    <b-input-group
                        prepend="£"
                        v-if="invoice.is_approval_required && !invoice.approved_at"
                    >
                        <b-form-input :readonly="!canApprove" v-model="data.item.unit_price"/>
                    </b-input-group>
                    <span v-else>{{ data.item.unit_price | cash_value }}</span>
                </template>
                <template #cell(sales_order_line.unit_price)="data">
                    <span>{{ data.value | cash_value }}</span>
                </template>
                <template #cell(qty_received_excl_me)="data">
                    <span>{{ data.item.qty_received_excl_me }}</span>
                </template>
                <template #cell(qty_received_total)="data">
                    <span>{{ data.item.qty_received_excl_me + (parseInt(data.item.qty) || 0) }}</span>
                </template>
                <template #cell(sub_total)="data">
                    <span>{{ data.item.qty * data.item.unit_price | cash_value }}</span>
                </template>
                <template #cell(pricing_priority)="data">
                    <b-form-select v-if="invoice.is_approval_required && !invoice.approved_at" :rerenderCounter="rerenderCounter" v-model="data.item.pricing_priority">
                        <b-form-select-option value="low">Low</b-form-select-option>
                        <b-form-select-option value="medium">Medium</b-form-select-option>
                        <b-form-select-option value="high">High</b-form-select-option>
                    </b-form-select>
                    <span v-else>{{data.item.pricing_priority}}</span>
                </template>
                <template #cell(status)="data">
                    <b-badge v-show="data.item.qty_received_excl_me + (parseInt(data.item.qty) || 0) > data.item.sales_order_line.qty_remaining" variant="danger">
                        Too many
                    </b-badge>
                    <b-badge variant="danger" v-show="data.item.unit_price > 0 && (parseFloat(data.item.sales_order_line.unit_price) !== parseFloat(data.item.unit_price))">
                        Price mismatch
                    </b-badge>
                </template>
            </b-table>

            <div v-if="invoice.is_approval_required">
                <div v-if="!invoice.approved_at">
                    <div v-if="!canApprove" class="d-flex justify-content-end">
                        <b-badge variant="danger">
                            Requires Administrator Approval
                        </b-badge>
                    </div>
                    <div v-else class="d-flex justify-content-between">
                        <b-form-checkbox v-model="invoice.close_oa">
                            Close off OA so no more invoices can be generated
                        </b-form-checkbox>
                        <div>
                            <b-button class="mr-2" variant="danger" @click="destroy()">
                                Delete
                            </b-button>
                            <b-button variant="success" @click="approve">
                                Approve
                            </b-button>
                        </div>
                    </div>
                </div>
            </div>
        </b-card>
    </div>
</template>

<script>
export default {
    name: "EditInvoice",
    props: {
        invoiceId: String,
        canApprove: {
            type: Boolean,
            default: false
        }
    },
    mounted() {
        this.refresh()
    },
    computed: {
        fields() {
            return [
                {
                    key: 'product',
                    label: 'Product',
                    thClass: 'align-middle',
                    tdClass: 'align-middle'
                }, {
                    key: 'item_number',
                    label: "Item #",
                    thClass: 'align-middle',
                    tdClass: 'align-middle'
                }, {
                    key: 'qty_received_excl_me',
                    label: "Received (other invoices)",
                    thClass: 'align-middle',
                    tdClass: 'align-middle'
                }, {
                    key: 'qty',
                    label: "R'cvd on inv.",
                    thClass: 'align-middle w-1 text-nowrap',
                    tdClass: 'align-middle'
                }, {
                    key: 'qty_received_total',
                    label: "Total R'cvd",
                    thClass: 'align-middle',
                    tdClass: 'align-middle'
                }, {
                    key: 'sales_order_line.units_requested',
                    label: 'Ordered',
                    thClass: 'align-middle',
                    tdClass: 'align-middle'
                }, {
                    key: 'unit_price',
                    label: "Unit Price",
                    thClass: 'align-middle w-10',
                    tdClass: 'align-middle'
                }, {
                    key: 'sales_order_line.unit_price',
                    label: 'OA Price',
                    thClass: 'align-middle ',
                    tdClass: 'align-middle'
                }, {
                    key: 'sub_total',
                    label: "Sub Total",
                    thClass: 'align-middle',
                    tdClass: 'align-middle'
                }, {
                    key: 'pricing_priority',
                    label: 'Priority',
                    thClass: 'align-middle',
                    tdClass: 'align-middle'
                },{
                    key: 'status',
                    label: '',
                    tdClass: 'align-middle'
                }
            ]
        },
        include() {
            return 'creator,approver,sales_order,sales_order.supplier,lines,lines.sales_order_line,lines.sales_order_line.product';
        }
    },
    methods: {
        async destroy() {
            let result = await this.$bvModal.msgBoxConfirm(
                'Are you sure you want to delete this invoice? This action cannot be undone', {
                centered: true,
                title: 'Please Confirm',
                okTitle: 'Yes'
            })

            if (!result) {
                return;
            }

            const loading = this.$loading.show();
            const url = `/api/invoices/${this.invoice.id}`
            this.$http.delete(url).then(response => {
                loading.hide();
                window.location.href = '/invoices'
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loading.hide();
            })
        },
        async approve() {
            let result = await this.$bvModal.msgBoxConfirm('Are you sure you want to approve this invoice?', {
                centered: true,
                title: 'Please Confirm',
                okTitle: 'Yes'
            });

            if (!result) {
                return;
            }

            const loading = this.$loading.show();
            const url = `/api/invoices/${this.invoice.id}`;
            this.$http.put(url, this.invoice, {params: {include: this.include}}).then(response => {
                this.refresh();
                loading.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loading.hide();
            })
        },
        setAllPriority(newPriority) {
            for (let i = 0; i < this.invoice.lines.length; i++) {
                this.invoice.lines[i].pricing_priority = newPriority;
            }
            // force Vue to notice the nested change on priority
            // by default, this.invoice.lines[i].priority won't
            // be watched for changes, just this.invoice
            this.rerenderCounter++;
        },
        getSubTotal(qty, price) {
            return qty * price;
        },
        refresh() {
            const loading = this.$loading.show();
            const url = `/api/invoices/${this.invoiceId}`
            this.$http.get(url, {params: {include: this.include}}).then(response => {
                this.invoice = response.data;
                this.reference = response.data.reference;
                loading.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loading.hide();
            })
        }
    },
    data() {
        return {
            invoice: {},
            reference: '',
            allPricing: null,
            rerenderCounter: null
        }
    }
}
</script>

<style scoped>

</style>