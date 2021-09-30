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
        <b-form-group label-size="sm" size="sm" label-cols-sm="4">
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
    </div>
</template>

<script>
export default {
    name: "ProductDimensionsInput",
    props: {value: Object}
}
</script>

<style scoped>
    .dimensions-input ::v-deep legend {
        padding-top: 2px;
        padding-bottom: 4px;
        line-height: 100%;
    }
</style>
