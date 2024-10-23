<template>
    <b-modal
        ref="modal"
        @ok.prevent="execute"
        title="Multi Product"
        ok-title="Update Products"
        :ok-disabled="okDisabled"
        :cancel-disabled="processing"
    >
        <p>
            You have selected <strong>{{ productCount }}</strong> products to modify.
        </p>
        <div v-if="copiedPricing">
            <p>Selecting "Paste Pricing" below will apply the pricing from
                <strong>{{ copiedPricing.product.item_number }} / {{ copiedPricing.product.name }}</strong>
                to all selected products.
            </p>
        </div>
        <b-checkbox-group v-model="selected">
            <b-form-checkbox class="d-block" value="paste" v-if="copiedPricing">Paste Pricing</b-form-checkbox>
            <b-form-checkbox class="d-block" value="submit">Submit for approval</b-form-checkbox>
            <b-form-checkbox class="d-block" value="approve" v-if="isApprover">Approve</b-form-checkbox>
        </b-checkbox-group>

        <div class="mt-4">
            <h6>Progress</h6>
            <b-progress :value="progress" :max="productCount" show-progress animated />
            <p class="mt-3">
                Once you've clicked "update products", it's important to let this process
                run to completion without navigating away from this page. It could take
                a few minutes, depending on how many products you've selected.
            </p>
        </div>

    </b-modal>
</template>

<script>
export default {
    name: "MultiProductPasteModal",
    props: {
        copiedPricing: Object,
        selectedProducts: Array,
        isApprover: Boolean,
        pricingModal: Object
    },
    computed: {
        okDisabled() {
            return this.productCount < 1 || this.selected.length < 1 || this.processing;
        },
        productCount() {
            if (this.selectedProducts && Array.isArray(this.selectedProducts)) {
                return this.selectedProducts.length;
            }
            return 0;
        }
    },
    methods: {
        open() {
            this.progress = 0;
            this.processing = false;
            this.$refs.modal.show();
        },
        async execute() {
            this.processing = true;
            const operations = {
                paste: this.selected.indexOf('paste') > -1,
                submit: this.selected.indexOf('submit') > -1,
                approve: this.selected.indexOf('approve') > -1,
            };
            for (const productId of this.selectedProducts) {
                try {
                    await this.pricingModal.headlessPaste({id: productId}, operations, this.copiedPricing);
                    this.progress++;
                    if (this.progress >= this.productCount) {
                        this.processing = false;
                        this.$refs.modal.hide();
                        this.$emit('updated');
                    }
                } catch (error) {
                    this.processing = false;
                    this.$bvToast.toast("Error during processing", {
                        variant: 'danger'
                    });
                }
            }
        }
    },
    data() {
        return {
            processing: false,
            progress: 0,
            selected: []
        }
    }
}
</script>

<style scoped>

</style>