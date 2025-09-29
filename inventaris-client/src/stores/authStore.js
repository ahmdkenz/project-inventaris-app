// File: src/stores/authStore.js

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from '@/services/axios';

export const useAuthStore = defineStore('auth', () => {
  // Coba ambil data user dari localStorage saat pertama kali store dibuat
  const user = ref(JSON.parse(localStorage.getItem('user')));
  const token = ref(localStorage.getItem('authToken'));

  // Set header Authorization jika token ada saat aplikasi dimuat ulang
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
  }

  const isLoggedIn = computed(() => !!user.value && !!token.value);
  const isAdmin = computed(() => user.value?.role === 'admin');
  const isStaff = computed(() => user.value?.role === 'staff');

  function login(userData, authToken) {
    // Simpan data ke state Pinia
    user.value = userData;
    token.value = authToken;
    
    // Simpan ke localStorage
    localStorage.setItem("user", JSON.stringify(userData));
    localStorage.setItem("authToken", authToken);
    
    // Set header default untuk request axios selanjutnya
    axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
  }

  function logout() {
    // Hapus data dari state Pinia
    user.value = null;
    token.value = null;

    // Hapus dari localStorage
    localStorage.removeItem('user');
    localStorage.removeItem('authToken');

    // Hapus header Authorization
    delete axios.defaults.headers.common['Authorization'];
  }

  return { user, token, isLoggedIn, isAdmin, isStaff, login, logout };
});