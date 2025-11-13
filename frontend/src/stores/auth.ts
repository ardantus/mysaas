import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/services/api'
import type { AxiosError } from 'axios'

interface User {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  created_at: string
  updated_at: string
}

interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

interface LoginData {
  email: string
  password: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => user.value !== null)

  const fetchUser = async () => {
    try {
      loading.value = true
      error.value = null
      const response = await authApi.user()
      user.value = response.data.user
      return true
    } catch (err) {
      user.value = null
      return false
    } finally {
      loading.value = false
    }
  }

  const register = async (data: RegisterData) => {
    try {
      loading.value = true
      error.value = null
      const response = await authApi.register(data)
      user.value = response.data.user
      return { success: true }
    } catch (err) {
      const axiosError = err as AxiosError<{ message?: string; errors?: Record<string, string[]> }>
      error.value = axiosError.response?.data?.message || 'Registration failed'
      return { 
        success: false, 
        errors: axiosError.response?.data?.errors 
      }
    } finally {
      loading.value = false
    }
  }

  const login = async (data: LoginData) => {
    try {
      loading.value = true
      error.value = null
      const response = await authApi.login(data)
      user.value = response.data.user
      return { success: true }
    } catch (err) {
      const axiosError = err as AxiosError<{ message?: string; errors?: Record<string, string[]> }>
      error.value = axiosError.response?.data?.message || 'Login failed'
      return { 
        success: false, 
        errors: axiosError.response?.data?.errors 
      }
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      loading.value = true
      error.value = null
      await authApi.logout()
      user.value = null
      return { success: true }
    } catch (err) {
      const axiosError = err as AxiosError<{ message?: string }>
      error.value = axiosError.response?.data?.message || 'Logout failed'
      return { success: false }
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    loading,
    error,
    isAuthenticated,
    fetchUser,
    register,
    login,
    logout,
  }
})
