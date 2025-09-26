import axios from 'axios';

const apiBase = 'http://localhost:8000';

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
    const token = localStorage.getItem('authToken');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response) {
      // Handle unauthorized (expired/invalid token)
      if (error.response.status === 401) {
        localStorage.removeItem('authToken');
        localStorage.removeItem('user');
        window.location.href = '/login';
      }
      
      // Handle inactive account
      if (error.response.status === 403 && 
          error.response.data && 
          error.response.data.message === 'Account is inactive') {
        localStorage.removeItem('authToken');
        localStorage.removeItem('user');
        // Redirect with message
        window.location.href = '/login?error=inactive';
      }
    }
    return Promise.reject(error);
  }
);

export default apiClient;