<template>
    <div class="row mb-2">
        <div class="col-3 col-sm-3 align-self-center">
            <strong>{{ title }}</strong>
        </div>
        <div class="col-7 col-sm-4 align-self-center">
            <b-form-input size="sm" v-if="inputType === 'text'" :value="value" @input="$emit('input', $event)"/>
            <b-form-textarea size="sm" v-else-if="inputType === 'textarea'" :value="value" @input="$emit('input', $event)" />
            <p v-else>{{ value }}</p>
        </div>
        <div class="col-2 col-sm-1">
            <div class="text-center" v-if="!hidePass">Pass</div>
            <div class="text-center">
                <b-form-checkbox
                    v-if="!hidePass"
                    size="lg"
                    :checked="passValue"
                    @input="$emit('pass-updated', $event)"
                    :checked-value="true"
                    :unchecked-value="false"
                />
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <b-form-textarea
                :placeholder="commentsPlaceholder"
                :value="commentsValue"
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
        passValue: Boolean,
        commentsValue: [Number, String],
        commentsPlaceholder: String,
        hidePass: Boolean
    }
}
</script>

<style scoped>

</style>
