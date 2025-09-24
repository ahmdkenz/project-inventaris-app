<template>
  <input
    :type="type"
    :value="value"
    @input="$emit('input', $event.target.value)"
  />
</template>

<script>
export default {
  name: "FormInput",
  props: {
    type: {
      type: String,
      default: "text",
    },
    value: String,
  },
};
</script>

<style scoped>
input {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
}
</style>
