<template>
  <select :value="value" @change="$emit('change', $event.target.value)">
    <option v-for="option in options" :key="option.value" :value="option.value">
      {{ option.label }}
    </option>
  </select>
</template>

<script>
export default {
  name: "FormSelect",
  props: {
    options: {
      type: Array,
      required: true,
    },
    value: String,
  },
};
</script>

<style scoped>
select {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
}
</style>
