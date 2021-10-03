<template>
    <div class="row mb-2">
        <div class="col-3 col-sm-3 align-self-center">
            <strong>{{ title }}</strong>
        </div>
        <div class="col-7 col-sm-4 align-self-center">
            <b-form-group class="mb-0">
                <b-input-group size="sm">
                    <b-input :value="length" @change="$emit('length-updated', $event)"/>
                    <b-input :value="width" @change="$emit('width-updated', $event)"/>
                    <b-input :value="height" @change="$emit('height-updated', $event)"/>
                </b-input-group>
            </b-form-group>
        </div>
        <div class="col-2 col-sm-1">
            <div class="text-center">Pass</div>
            <div class="text-center">
                <b-form-checkbox
                    size="lg"
                    @input="$emit('pass-updated', $event)"
                />
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <b-form-textarea
                :placeholder="commentsPlaceholder"
                size="sm"
                @input="$emit('comments-updated', $event)"
            />
        </div>
    </div>
</template>

<script>
export default {
    name: "InspectionDimensionsRow",
    props: {
        inputType: String,
        title: String,
        length: Number,
        width: Number,
        height: Number,
        value: [Number, String],
        commentsPlaceholder: String,
        commentsVModel: Object,
        isPassVModel: Object
    }
}
</script>

<style scoped>

</style>
