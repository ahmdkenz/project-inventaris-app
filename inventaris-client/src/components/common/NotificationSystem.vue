<template>
  <div class="notification-container">
    <transition-group name="notification-list">
      <div v-for="notification in notifications" :key="notification.id" 
           :class="['notification', `notification-${notification.type}`]">
        <div class="notification-icon" v-if="notification.icon">
          <!-- Success Icon -->
          <svg v-if="notification.type === 'success'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
          
          <!-- Error Icon -->
          <svg v-else-if="notification.type === 'error'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
          </svg>
          
          <!-- Warning Icon -->
          <svg v-else-if="notification.type === 'warning'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
          
          <!-- Info Icon -->
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
          </svg>
        </div>
        <div class="notification-body">
          <div class="notification-title" v-if="notification.title">{{ notification.title }}</div>
          <div class="notification-message">{{ notification.message }}</div>
        </div>
        <div class="notification-close" @click="close(notification.id)">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script>
export default {
  name: 'NotificationSystem',
  data() {
    return {
      notifications: [],
      counter: 0,
    };
  },
  methods: {
    show(notification) {
      const id = this.counter++;
      const defaultDuration = 5000;

      // Default icon to true if not specified
      if (notification.icon === undefined) {
        notification.icon = true;
      }
      
      // Create the notification object
      const notificationObj = {
        id,
        type: notification.type || 'info',
        message: notification.message || '',
        title: notification.title || '',
        duration: notification.duration || defaultDuration,
        icon: notification.icon,
      };
      
      // Add notification to the list
      this.notifications.push(notificationObj);
      
      // Remove after duration
      if (notificationObj.duration !== 0) {
        setTimeout(() => {
          this.close(id);
        }, notificationObj.duration);
      }
      
      return id;
    },

    close(id) {
      const index = this.notifications.findIndex(n => n.id === id);
      if (index !== -1) {
        // Add class for animation
        const element = document.querySelector(`.notification[data-id="${id}"]`);
        if (element) {
          element.classList.add('slide-out');
          setTimeout(() => {
            this.notifications.splice(index, 1);
          }, 300); // Duration of the animation
        } else {
          this.notifications.splice(index, 1);
        }
      }
    },

    success(message, options = {}) {
      return this.show({
        type: 'success',
        message,
        ...options,
      });
    },

    error(message, options = {}) {
      return this.show({
        type: 'error',
        message,
        ...options,
      });
    },

    warning(message, options = {}) {
      return this.show({
        type: 'warning',
        message,
        ...options,
      });
    },

    info(message, options = {}) {
      return this.show({
        type: 'info',
        message,
        ...options,
      });
    },

    clearAll() {
      this.notifications = [];
    }
  }
};
</script>

<style scoped>
@import '@/styles/notification.css';

.notification-list-enter-active, .notification-list-leave-active {
  transition: all 0.3s;
}

.notification-list-enter, .notification-list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

.notification-list-move {
  transition: transform 0.3s;
}
</style>