import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('../views/AboutView.vue'),
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: { guest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue'),
      meta: { guest: true },
    },
    {
      path: '/dashboard',
      component: () => import('../views/DashboardLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'dashboard',
          component: () => import('../views/DashboardView.vue'),
        },
        {
          path: 'stores',
          name: 'stores',
          component: () => import('../views/StoreListView.vue'),
        },
        {
          path: 'stores/create',
          name: 'store-create',
          component: () => import('../views/StoreFormView.vue'),
        },
        {
          path: 'stores/:id',
          name: 'store-detail',
          component: () => import('../views/StoreFormView.vue'),
        },
        {
          path: 'stores/:id/edit',
          name: 'store-edit',
          component: () => import('../views/StoreFormView.vue'),
        },
      ],
    },
    // Store frontend routes (for tenant stores)
    {
      path: '/store',
      name: 'store-home',
      component: () => import('../views/StoreHomeView.vue'),
    },
    {
      path: '/products',
      name: 'products',
      component: () => import('../views/ProductListView.vue'),
    },
    {
      path: '/products/:id',
      name: 'product-detail',
      component: () => import('../views/ProductDetailView.vue'),
    },
    {
      path: '/categories/:categoryId',
      name: 'category-products',
      component: () => import('../views/ProductListView.vue'),
    },
    {
      path: '/cart',
      name: 'cart',
      component: () => import('../views/CartView.vue'),
    },
  ],
})

// Navigation guard
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Try to fetch user if not already loaded
  if (!authStore.user && !authStore.loading) {
    await authStore.fetchUser()
  }
  
  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } 
  // Check if route is for guests only (login/register)
  else if (to.meta.guest && authStore.isAuthenticated) {
    next('/dashboard')
  } 
  else {
    next()
  }
})

export default router
