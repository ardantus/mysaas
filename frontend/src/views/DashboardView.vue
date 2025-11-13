<template>
  <div>
    <div class="px-4 sm:px-0">
      <h3 class="text-2xl font-semibold leading-6 text-gray-900">Dashboard</h3>
      <p class="mt-2 text-sm text-gray-700">
        Welcome back! Manage your stores and track your business.
      </p>
    </div>
    <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
      <!-- Stats cards -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <dt class="text-sm font-medium text-gray-500 truncate">Total Stores</dt>
          <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ stats.totalStores }}</dd>
        </div>
      </div>
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <dt class="text-sm font-medium text-gray-500 truncate">Active Stores</dt>
          <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ stats.activeStores }}</dd>
        </div>
      </div>
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <dt class="text-sm font-medium text-gray-500 truncate">Total Products</dt>
          <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ stats.totalProducts }}</dd>
        </div>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="mt-8">
      <h4 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h4>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <router-link
          to="/dashboard/stores/create"
          class="relative block w-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          <svg
            class="mx-auto h-12 w-12 text-gray-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 4v16m8-8H4"
            />
          </svg>
          <span class="mt-2 block text-sm font-medium text-gray-900">Create New Store</span>
        </router-link>
        <router-link
          to="/dashboard/stores"
          class="relative block w-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          <svg
            class="mx-auto h-12 w-12 text-gray-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
            />
          </svg>
          <span class="mt-2 block text-sm font-medium text-gray-900">Manage Stores</span>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { storeApi } from '@/services/api'

const stats = ref({
  totalStores: 0,
  activeStores: 0,
  totalProducts: 0,
})

onMounted(async () => {
  try {
    const response = await storeApi.list()
    stats.value.totalStores = response.data.total || 0
    stats.value.activeStores = response.data.data?.filter((s: any) => s.is_active).length || 0
  } catch (error) {
    console.error('Failed to load stats:', error)
  }
})
</script>
