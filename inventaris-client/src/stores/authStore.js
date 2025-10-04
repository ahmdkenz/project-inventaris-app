// File: src/stores/authStore.js

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from '@/services/axios';

export const useAuthStore = defineStore('auth', () => {
  // Coba ambil data user dari localStorage saat pertama kali store dibuat
  let userData = null;
  try {
    userData = JSON.parse(localStorage.getItem('user'));
    console.log('User data loaded from localStorage:', userData ? 'found' : 'not found');
  } catch (e) {
    console.error('Error parsing user data from localStorage:', e);
    // Reset invalid data
    localStorage.removeItem('user');
  }
  
  const user = ref(userData);
  const token = ref(localStorage.getItem('authToken') || localStorage.getItem('token'));

  // Set header Authorization jika token ada saat aplikasi dimuat ulang
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
    console.log('Token set in axios defaults on app load');
  } else {
    console.warn('No token found in localStorage');
  }

  const isLoggedIn = computed(() => !!user.value && !!token.value);
  const isAdmin = computed(() => user.value?.role === 'admin');
  const isStaff = computed(() => user.value?.role === 'staff');

  function login(userData, authToken) {
    // Simpan data ke state Pinia
    user.value = userData;
    token.value = authToken;
    
    // Simpan ke localStorage (dua versi untuk kompatibilitas)
    localStorage.setItem("user", JSON.stringify(userData));
    localStorage.setItem("authToken", authToken);
    localStorage.setItem("token", authToken); // Simpan juga dengan key 'token'
    
    // Set header default untuk request axios selanjutnya
    axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
  }

  function logout() {
    // Hapus data dari state Pinia
    user.value = null;
    token.value = null;

    // Hapus dari localStorage (kedua versi)
    localStorage.removeItem('user');
    localStorage.removeItem('authToken');
    localStorage.removeItem('token');

    // Hapus header Authorization
    delete axios.defaults.headers.common['Authorization'];
  }

  return { user, token, isLoggedIn, isAdmin, isStaff, login, logout };
});