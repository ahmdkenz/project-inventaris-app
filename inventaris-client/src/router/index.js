import { createRouter, createWebHistory } from 'vue-router';

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/auth/LoginView.vue'),
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../views/auth/RegisterView.vue'),
  },
  {
    path: '/dashboard',
    name: 'StaffDashboard',
    component: () => import('../views/dashboard/StaffDashboard.vue'),
    meta: { requiresAuth: true, role: 'staff' }
  },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: () => import('../views/dashboard/AdminDashboard.vue'),
    meta: { requiresAuth: true, role: 'admin' }
  },
  // Product routes
  {
    path: '/products',
    name: 'ProductList',
    component: () => import('../views/products/ProductListView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/products/create',
    name: 'ProductCreate',
    component: () => import('../views/products/ProductCreateView.vue'),
    meta: { requiresAuth: true, role: 'admin' }
  },
  // Stock routes
  {
    path: '/stocks/adjustment',
    name: 'StockAdjustment',
    component: () => import('../views/stocks/StockAdjustmentView.vue'),
    meta: { requiresAuth: true }
  },
  // Order routes
  {
    path: '/orders/:id',
    name: 'OrderDetail',
    component: () => import('../views/orders/OrderDetailView.vue'),
    meta: { requiresAuth: true }
  },
  // Reports routes
  {
    path: '/reports',
    name: 'Reports',
    component: () => import('../views/reports/ReportView.vue'),
    meta: { requiresAuth: true }
  },
  // Settings routes (admin only)
  {
    path: '/settings/users',
    name: 'UserManagement',
    component: () => import('../views/settings/UserManagementView.vue'),
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/settings/system',
    name: 'SystemSettings',
    component: () => import('../views/settings/SystemSettingsView.vue'),
    meta: { requiresAuth: true, role: 'admin' }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guard
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('authToken');
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  
  if (to.meta.requiresAuth) {
    if (!token) {
      next('/login');
      return;
    }
    
    if (to.meta.role && user.role !== to.meta.role) {
      // Redirect to appropriate dashboard based on role
      if (user.role === 'admin') {
        next('/admin/dashboard');
      } else {
        next('/dashboard');
      }
      return;
    }
  }
  
  next();
});

export default router;