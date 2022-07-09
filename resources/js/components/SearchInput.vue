<template>
    <b-input-group>
        <b-form-input
            type="search"
            :value="value"
            @input="$emit('input', $event)"
            :placeholder="placeholder"
            class="border-right-0"
            @search="$emit('search', $event)"
        />
        <b-input-group-append>
            <b-input-group-text class="bg-white">
                <b-icon-search class="search-action" @click="$emit('search', value)" />
            </b-input-group-text>
            <slot></slot>
        </b-input-group-append>
    </b-input-group>
</template>

<script>
export default {
    props: {
        value: String,
        placeholder: {
            type: String,
            default: 'Search'
        }
    },
    name: "SearchInput",
}
</script>
<style>
    .search-action {
      cursor: pointer
    }
</style>

