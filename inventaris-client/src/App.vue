<template>
  <NotificationSystem ref="notificationSystem" />
  <router-view />
</template>

<script>
import NotificationSystem from './components/common/NotificationSystem.vue';
import { useNotification } from './composables/useNotification';
import { onMounted } from 'vue';

export default {
  components: {
    NotificationSystem
  },
  setup() {
    const notification = useNotification();
    
    onMounted(() => {
      notification.setNotificationInstance(this.$refs.notificationSystem);
    });
    
    return { notification };
  }
}
</script>

<style>
@import './styles/fonts.css';
@import './styles/reset.css';
@import './styles/global-components.css';
@import './styles/universal-minimal.css';
</style>