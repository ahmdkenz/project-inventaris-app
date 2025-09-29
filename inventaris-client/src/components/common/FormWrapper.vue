<template>
  <div class="card">
    <div class="card-header">
      <h1 class="card-title">{{ title }}</h1>
      <router-link :to="backLink" class="btn btn-secondary" v-if="backLink">← {{ backText || 'Kembali' }}</router-link>
    </div>
    
    <div class="card-body">
      <slot></slot>
    </div>
    
    <div v-if="error" class="alert alert-error">
      {{ error }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'FormWrapper',
  props: {
    title: {
      type: String,
      required: true
    },
    backLink: {
      type: String,
      default: ''
    },
    backText: {
      type: String,
      default: 'Kembali'
    },
    error: {
      type: String,
      default: ''
    }
  }
}
</script>

<style>
@import '@/styles/form-wrapper.css';
</style>