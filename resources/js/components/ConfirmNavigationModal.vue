<template>
  <div>
    <slot :confirm="confirm"></slot>
    <b-modal ref="modal" :title="title" @ok="ok" :ok-title="confirmLabel">
      {{ message }}
    </b-modal>
  </div>
</template>

<script>
export default {
  name: "ConfirmNavigationModal",
  props: {
      title: String,
      message: String,
      confirmLabel: String,
      deleteUrl: String
  },
  methods: {
      confirm() {
        console.log("WEJrjiwerwerewr");
        this.$refs['modal'].show();
      },
      ok(e) {
          const loader = this.$loading.show();
          e.preventDefault();
          window.location.href = this.deleteUrl;
      }
  }
}
</script>

<style scoped>

</style>
