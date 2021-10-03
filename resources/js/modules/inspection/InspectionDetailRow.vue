<template>
    <div class="row mb-2">
        <div class="col-3 col-sm-3 align-self-center">
            <strong>{{ title }}</strong>
        </div>
        <div class="col-7 col-sm-4 align-self-center">
            <b-form-input size="sm" v-if="inputType === 'text'" v-model="value"/>
            <b-form-textarea size="sm" v-else-if="inputType === 'textarea'" v-model="value" />
            <p v-else>{{ value }}</p>
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
    name: "InspectionDetailRow",
    props: {
        inputType: String,
        title: String,
        value: [Number, String],
        commentsPlaceholder: String,
        commentsVModel: Object,
        isPassVModel: Object
    }
}
</script>

<style scoped>

</style>
