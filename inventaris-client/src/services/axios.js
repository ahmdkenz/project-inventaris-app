import axios from 'axios';

// Gunakan variabel env jika tersedia, jika tidak fallback ke localhost:8000
const apiBase = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';
console.log('Using API base URL:', apiBase);

const apiClient = axios.create({
  baseURL: `${apiBase}/api`,
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  withCredentials: false,
});

// Request interceptor
apiClient.interceptors.request.use(
  async (config) => {
    // Cek kedua key untuk token (untuk kompatibilitas)
    const token = localStorage.getItem('authToken') || localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
      console.log('Request with token:', config.url);
    } else {
      console.warn('No token found for request:', config.url);
    }
    return config;
  },
  (error) => Promise.reject(error)
);

apiClient.interceptors.response.use(
  (response) => {
    console.log('API Response Success:', response.config.url);
    return response;
  },
  (error) => {
    console.error('API Error:', error.config?.url, error.response?.status, error.message);
    
    if (error.response) {
      // Handle unauthorized (expired/invalid token)
      if (error.response.status === 401) {
        console.warn('Unauthorized access - redirecting to login');
        localStorage.removeItem('authToken');
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = '/login';
      }
      
      // Handle inactive account
      if (error.response.status === 403 && 
          error.response.data && 
          error.response.data.message === 'Account is inactive') {
        console.warn('Account inactive - redirecting to login');
        localStorage.removeItem('authToken');
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        // Redirect with message
        window.location.href = '/login?error=inactive';
      }
    }
    return Promise.reject(error);
  }
);

export default apiClient;