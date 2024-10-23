<template>
    <b-form-group class="mb-2" :label="topLabel">
        <template #description>
            <div v-if="amzDigitalTaxValue">
                <div class="d-flex">
                    <div class="w-50">Amz Digital Tax</div>
                    <span>{{ amzDigitalTaxValue }}</span>
                </div>
            </div>
        </template>
        <b-input-group size="sm">
            <b-input-group-prepend class="w-50">
                <b-input-group-text class="w-100">
                    <slot name="label">{{ label }}</slot>
                </b-input-group-text>
            </b-input-group-prepend>
            <b-form-input
                v-if="value || editable"
                v-model.number="editableValue"
                class="border-success"
                @change="emit"
                size="sm"
            />
            <div class="form-control bg-light" v-else>{{ fixed }}</div>
            <b-input-group-append class="w-15">
                <b-input-group-text class="w-100" v-html="currencySymbol" />
            </b-input-group-append>
        </b-input-group>
    </b-form-group>
</template>

<script>
export default {
    name: "PricingFormInput",
    props: {
        topLabel: String,
        editable: Boolean,
        value: [String, Number],
        fixed: [String, Number],
        label: String,
        currencySymbol: String,
        amzDigitalTaxValue: [String, Number]
    },
    watch: {
        value: {
            immediate: true,
            handler(curr) {
                if (curr !== null || !Number.isNaN(curr)) {
                    this.editableValue = curr;
                }
            }
        }
    },
    computed: {
        hasAmzDigitalTaxValue() {
            return typeof this.amzDigitalTaxValue !== 'undefined';
        }
    },
    methods: {
        emit() {
            this.$emit('input', this.editableValue);
        }
    },
    data() {
        return {
            editableValue: 0
        }
    }
}
</script>
<style scoped>
.form-control.bg-light {
    background-color: #ddd !important;
}
</style>