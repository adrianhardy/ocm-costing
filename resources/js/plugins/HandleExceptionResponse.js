export default {
    install(Vue) {
        Vue.prototype.$handleExceptionResponse = function(error, popup)
        {
            if (popup && error.response.data.message && error.response.status !== 422) {
                this.$bvModal.msgBoxOk(error.response.data.message, {
                    title: 'Unhandled Error'
                });
            }
            if (popup && error.response.data.errors) {
                let message = '';

                if (!error.response.data.errors) {
                    message = error.response.data.message;
                } else {
                    const errors = error.response.data.errors
                    let messages = [];
                    const fields = Object.keys(errors);
                    for (const field in fields) {
                        const msg = errors[fields[field]][0];
                        messages.push(this.$createElement('div', msg));
                    }
                    this.$bvModal.msgBoxOk(messages, {
                        title: 'Invalid data submitted'
                    });
                }
            }
            this.errors = error.response.data.errors;
        }
    }
}



