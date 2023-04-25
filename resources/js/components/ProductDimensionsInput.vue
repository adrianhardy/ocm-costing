<template>
    <div class="dimensions-input mt-1">
        <b-form-group label-size="sm" size="sm" label-cols-sm="4" class="mb-0">
            <template #label>
                Carton<br />
                <small>LxWxH cm</small>
            </template>
            <b-input-group size="sm">
                <b-input v-model="value.length_cm" @change="$emit('updated', $event)"/>
                <b-input v-model="value.width_cm" @change="$emit('updated', $event)"/>
                <b-input v-model="value.height_cm" @change="$emit('updated', $event)"/>
            </b-input-group>
        </b-form-group>
        <b-form-group label-size="sm" size="sm" label-cols-sm="4" class="mb-0">
            <template #label>
                Unit<br />
                <small>LxWxH cm</small>
            </template>
            <b-input-group size="sm">
                <b-input v-model="value.unit_length_cm" @change="$emit('updated', $event)"/>
                <b-input v-model="value.unit_width_cm" @change="$emit('updated', $event)"/>
                <b-input v-model="value.unit_height_cm" @change="$emit('updated', $event)"/>
            </b-input-group>
        </b-form-group>
        <div v-if="!orderAcknowledgement">
            <b-form-group label-size="sm" size="sm" label-cols-sm="4" class="mb-0">
                <template #label>
                    Carton<br />
                    <small>ft<sup>3</sup></small>
                </template>
                <b-input-group size="sm">
                    <b-input readonly :value="caseCubicFt"/>
                </b-input-group>
            </b-form-group>
            <b-form-group label-size="sm" size="sm" label-cols-sm="4">
                <template #label>
                    Carton<br />
                    <small>m<sup>3</sup></small>
                </template>
                <b-input-group size="sm">
                    <b-input readonly :value="caseCubicM"/>
                </b-input-group>
            </b-form-group>
        </div>
    </div>
</template>

<script>
export default {
    name: "ProductDimensionsInput",
    props: {
        value: Object,
        orderAcknowledgement: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        caseCubicFt() {
            return this.value.case_cubic_ft.toFixed(2);
        },
        caseCubicM() {
            return this.value.case_cubic_m.toFixed(2);
        }
    }
}
</script>

<style scoped>
    .dimensions-input ::v-deep legend {
        padding-top: 2px;
        padding-bottom: 4px;
        line-height: 100%;
    }
</style>
