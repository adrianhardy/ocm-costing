<template>
    <div>
        <div class="d-flex justify-content-between mb-3">
            <b-button variant="outline-secondary" @click="prev()"><b-icon-arrow-left /></b-button>
            <div>
                Viewing {{ current+1 }} of {{ products.length }}
            </div>
            <b-button variant="outline-secondary" @click="next()"><b-icon-arrow-right /></b-button>
        </div>
        <b-table-simple small>
            <b-tr>
                <b-th>Item #</b-th>
                <b-td>{{ product.item_number }}</b-td>
            </b-tr>
            <b-tr>
                <b-th>Article #</b-th>
                <b-td>{{ product.article_number }}</b-td>
            </b-tr>
            <b-tr>
                <b-th>Name</b-th>
                <b-td class="truncated" v-b-tooltip.hover :title="product.name">{{ product.name }}</b-td>
            </b-tr>
            <b-tr>
                <b-th>Packing</b-th>
                <b-td class="truncated" v-b-tooltip.hover :title="product.packing_instructions">{{ product.packing_instructions }}</b-td>
            </b-tr>
            <b-tr>
                <b-th>Unit Price</b-th>
                <b-td>{{ product.unit_price }}</b-td>
            </b-tr>
            <b-tr>
                <b-th>Cases</b-th>
                <b-td>{{ product.cases }}</b-td>
            </b-tr>
            <b-tr>
                <b-th>Case Qty</b-th>
                <b-td>{{ product.case_qty }}</b-td>
            </b-tr>
            <b-tr>
                <b-th>Cu. ft/Case</b-th>
                <b-td>{{ product.cu_ft_per_case }}</b-td>
            </b-tr>
            <b-tr>
                <b-th>Total Cu. ft</b-th>
                <b-td>{{ product.total_cu_ft }}</b-td>
            </b-tr>
            <b-tr v-if ="product.image">
                <b-td colspan="2" class="text-center">
                    <b-img style="max-height: 250px" class="mx-auto img-fluid" :src="`data:image/png;base64, ${product.image}`" />
                </b-td>
            </b-tr>
        </b-table-simple>
    </div>
</template>

<script>
export default {
    props: ['code', 'headerRows', 'file', 'mapping'],
    name: "ProductPreviewer",
    computed: {
        product() {
            return this.products[this.current] || {};
        }
    },
    watch: {
        mapping() {
            console.log("MAPPING CHANGED");
            this.updatePreview();
        },
        headerRows() {
            this.current = 0;
            this.updatePreview();
        },
        file() {
            this.current = 0;
            this.updatePreview();
        }
    },
    methods: {
        updatePreview() {
            const url = `/admin/imports/preview/${this.code}`;
            this.$http.put(url, {
                code: this.code,
                mapping: this.mapping,
                skip_rows: this.headerRows
            }).then(response => {
                this.products = response.data.items;
            });
        },
        next() {
            if (this.current < this.products.length-1) {
                this.current++;
            }
        },
        prev() {
            if (this.current > 0) {
                this.current--;
            }
        }
    },
    data() {
        return {
            current: 0,
            products: []
        }
    }
}
</script>
