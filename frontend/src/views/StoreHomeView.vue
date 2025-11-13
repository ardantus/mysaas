<template>
  <div class="bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <!-- Store header -->
      <div class="py-8 text-center border-b">
        <h1 class="text-4xl font-bold text-gray-900">Welcome to Our Store</h1>
        <p class="mt-2 text-lg text-gray-600">Discover our amazing products</p>
      </div>

      <!-- Featured Products -->
      <div class="py-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Featured Products</h2>
        <div v-if="loading" class="text-center py-12">
          <p class="text-gray-500">Loading products...</p>
        </div>
        <div v-else-if="products.length === 0" class="text-center py-12">
          <p class="text-gray-500">No products available yet.</p>
        </div>
        <div v-else class="grid grid-cols-1 gap-y-10 gap-x-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8">
          <div v-for="product in products" :key="product.id" class="group">
            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-lg bg-gray-200 xl:aspect-w-7 xl:aspect-h-8">
              <img
                :src="product.images?.[0] || 'https://via.placeholder.com/400'"
                :alt="product.name"
                class="h-full w-full object-cover object-center group-hover:opacity-75"
              />
            </div>
            <h3 class="mt-4 text-sm text-gray-700">{{ product.name }}</h3>
            <p class="mt-1 text-lg font-medium text-gray-900">
              Rp {{ formatPrice(product.price) }}
            </p>
            <div class="mt-2">
              <router-link
                :to="`/products/${product.id}`"
                class="text-indigo-600 hover:text-indigo-800 text-sm font-medium"
              >
                View Details →
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Categories Section -->
      <div class="py-12 border-t">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Shop by Category</h2>
        <div v-if="categories.length === 0" class="text-center py-12">
          <p class="text-gray-500">No categories available yet.</p>
        </div>
        <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
          <router-link
            v-for="category in categories"
            :key="category.id"
            :to="`/categories/${category.id}`"
            class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500"
          >
            <div class="flex-1 min-w-0">
              <span class="absolute inset-0" aria-hidden="true"></span>
              <p class="text-sm font-medium text-gray-900">{{ category.name }}</p>
              <p class="text-sm text-gray-500 truncate">{{ category.description }}</p>
            </div>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { productApi, categoryApi } from '@/services/api'

interface Product {
  id: number
  name: string
  price: number
  images: string[]
}

interface Category {
  id: number
  name: string
  description: string
}

const products = ref<Product[]>([])
const categories = ref<Category[]>([])
const loading = ref(false)

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

onMounted(async () => {
  try {
    loading.value = true
    // Load featured products
    const productsRes = await productApi.list({ featured: true })
    products.value = productsRes.data.data || []
    
    // Load categories
    const categoriesRes = await categoryApi.list()
    categories.value = categoriesRes.data.data || []
  } catch (error) {
    console.error('Failed to load data:', error)
  } finally {
    loading.value = false
  }
})
</script>
