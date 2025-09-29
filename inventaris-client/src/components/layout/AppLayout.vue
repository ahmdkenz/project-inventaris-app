<template>
  <div class="app-layout">
    <Sidebar />
    <div class="main-container">
      <Header />
      <main class="main-content">
        <div class="content-container">
          <slot name="header"></slot>
          <slot></slot>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import Header from './Header.vue';
import Sidebar from './Sidebar.vue';

export default {
  name: "AppLayout",
  components: {
    Header,
    Sidebar
  }
};
</script>

<style>
@import '@/styles/app-layout.css';
</style>