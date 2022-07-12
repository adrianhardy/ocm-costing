<template>
    <span></span>
</template>

<script>
export default {
    name: "SendAllProductsToPricingLink",
    methods: {
        async send() {
            let result = await this.$bvModal.msgBoxConfirm(
                "Are you sure you want to send ALL products to pricing? This will " +
                "invalidate all prices and all products will have to be " +
                "re-priced and re-approved", {
                    title: 'Please Confirm'
                }
            );

            if (!result) {
                return;
            }

            const loading = this.$loading.show();
            const url = '/api/products/pricing'
            this.$http.post(url).then(() => {
                loading.hide();
            }).catch(error => {
                this.$handleExceptionResponse(error, true);
                loading.hide();
            })
        },

    }
}
</script>

<style scoped>

</style>
