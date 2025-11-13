import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

export const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
})

// Auth API
export const authApi = {
  register: (data: { name: string; email: string; password: string; password_confirmation: string }) =>
    api.post('/register', data),
  login: (data: { email: string; password: string }) =>
    api.post('/login', data),
  logout: () => api.post('/logout'),
  user: () => api.get('/user'),
}

// Store API
export const storeApi = {
  list: () => api.get('/stores'),
  create: (data: any) => api.post('/stores', data),
  get: (id: number) => api.get(`/stores/${id}`),
  update: (id: number, data: any) => api.put(`/stores/${id}`, data),
  delete: (id: number) => api.delete(`/stores/${id}`),
}

// Category API
export const categoryApi = {
  list: (params?: any) => api.get('/categories', { params }),
  get: (id: number) => api.get(`/categories/${id}`),
  create: (data: any) => api.post('/admin/categories', data),
  update: (id: number, data: any) => api.put(`/admin/categories/${id}`, data),
  delete: (id: number) => api.delete(`/admin/categories/${id}`),
}

// Product API
export const productApi = {
  list: (params?: any) => api.get('/products', { params }),
  get: (id: number) => api.get(`/products/${id}`),
  byCategory: (categoryId: number, params?: any) => 
    api.get(`/products/category/${categoryId}`, { params }),
  create: (data: any) => api.post('/admin/products', data),
  update: (id: number, data: any) => api.put(`/admin/products/${id}`, data),
  delete: (id: number) => api.delete(`/admin/products/${id}`),
}

// Cart API
export const cartApi = {
  get: () => api.get('/cart'),
  addItem: (data: { product_id: number; quantity: number }) => 
    api.post('/cart/add', data),
  updateItem: (itemId: number, data: { quantity: number }) => 
    api.put(`/cart/update/${itemId}`, data),
  removeItem: (itemId: number) => api.delete(`/cart/remove/${itemId}`),
  clear: () => api.delete('/cart/clear'),
}

// Order API
export const orderApi = {
  checkout: (data: any) => api.post('/orders/checkout', data),
  get: (id: number) => api.get(`/orders/${id}`),
  list: (params?: any) => api.get('/admin/orders', { params }),
  updateStatus: (id: number, data: { status: string; notes?: string }) => 
    api.put(`/admin/orders/${id}/status`, data),
}

export default api
