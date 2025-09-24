<template>
  <button class="secondary">
    <slot></slot>
  </button>
</template>

<script>
export default {
  name: "SecondaryButton",
};
</script>

<style scoped>
.secondary {
  background-color: #6c757d;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
}
</style>
