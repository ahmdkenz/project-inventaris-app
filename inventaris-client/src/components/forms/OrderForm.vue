<template>
  <form @submit.prevent="handleSubmit">
    <slot></slot>
  </form>
</template>

<script>
export default {
  name: "OrderForm",
  methods: {
    handleSubmit() {
      this.$emit("submit");
    },
  },
};
</script>

<style scoped>
form {
  display: flex;
  flex-direction: column;
}
</style>
