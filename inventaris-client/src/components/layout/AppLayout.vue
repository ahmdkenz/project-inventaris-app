<template>
  <div class="app-layout">
    <Sidebar />
    <div class="main-container">
      <Header />
      <main class="main-content">
        <div class="content-wrapper">
          <slot name="header"></slot>
          <slot></slot>
        </div>
      </main>
      <Footer />
    </div>
  </div>
</template>

<script>
import Header from './Header.vue';
import Sidebar from './Sidebar.vue';
import Footer from './Footer.vue';

export default {
  name: "AppLayout",
  components: {
    Header,
    Sidebar,
    Footer
  }
};
</script>

<style>
@import '@/styles/app-layout.css';
</style>