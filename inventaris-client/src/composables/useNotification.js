import { ref, markRaw, reactive } from 'vue';

// Create a notification instance that can be reused across the app
const notificationInstance = ref(null);

export function useNotification() {
  const notifications = reactive({
    items: [],
    counter: 0
  });

  /**
   * Set the notification component instance
   */
  const setNotificationInstance = (instance) => {
    notificationInstance.value = markRaw(instance);
  };

  /**
   * Show a notification
   */
  const show = (options) => {
    if (notificationInstance.value) {
      return notificationInstance.value.show(options);
    } else {
      console.warn('Notification component not initialized yet');
      return null;
    }
  };

  /**
   * Show a success notification
   */
  const success = (message, options = {}) => {
    return show({
      type: 'success',
      message,
      ...options
    });
  };

  /**
   * Show an error notification
   */
  const error = (message, options = {}) => {
    return show({
      type: 'error',
      message,
      ...options
    });
  };

  /**
   * Show a warning notification
   */
  const warning = (message, options = {}) => {
    return show({
      type: 'warning',
      message,
      ...options
    });
  };

  /**
   * Show an info notification
   */
  const info = (message, options = {}) => {
    return show({
      type: 'info',
      message,
      ...options
    });
  };

  /**
   * Close a specific notification
   */
  const close = (id) => {
    if (notificationInstance.value) {
      notificationInstance.value.close(id);
    }
  };

  /**
   * Clear all notifications
   */
  const clearAll = () => {
    if (notificationInstance.value) {
      notificationInstance.value.clearAll();
    }
  };

  return {
    setNotificationInstance,
    show,
    success,
    error,
    warning,
    info,
    close,
    clearAll
  };
}
