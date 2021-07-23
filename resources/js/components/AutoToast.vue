<template></template>
<script>
export default {
    name: "AutoToast",
    computed: {
        flashes() {
            return window.__flashes;
        }
    },
    mounted() {
        this.flashes.forEach(flash => {
            this.$bvToast.toast(flash.message, {
                variant: flash.type,
                title: flash.title,
                autoHideDelay: 5000,
                appendToast: true
            });
        })
    }
}
</script>

<style scoped>

</style>
