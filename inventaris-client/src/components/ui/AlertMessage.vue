<template>
  <div>
    <p>{{ message }}</p>
  </div>
</template>

<script>
export default {
  name: "AlertMessage",
  props: {
    message: String,
  },
};
</script>

<style scoped>
.alert {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
}
</style>
