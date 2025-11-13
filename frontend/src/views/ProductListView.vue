<template>
  <div class="bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
          {{ categoryId ? 'Products in Category' : 'All Products' }}
        </h1>
        <router-link
          to="/cart"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
        >
          <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          Cart
        </router-link>
      </div>

      <!-- Search and filter -->
      <div class="mb-6">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search products..."
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
          @input="handleSearch"
        />
      </div>

      <!-- Products grid -->
      <div v-if="loading" class="text-center py-12">
        <p class="text-gray-500">Loading products...</p>
      </div>
      <div v-else-if="products.length === 0" class="text-center py-12">
        <p class="text-gray-500">No products found.</p>
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
          <p v-if="product.compare_price" class="text-sm text-gray-500 line-through">
            Rp {{ formatPrice(product.compare_price) }}
          </p>
          <div class="mt-2 flex space-x-2">
            <router-link
              :to="`/products/${product.id}`"
              class="flex-1 text-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              View
            </router-link>
            <button
              @click="addToCart(product)"
              class="flex-1 px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
            >
              Add to Cart
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { productApi, cartApi } from '@/services/api'

interface Product {
  id: number
  name: string
  price: number
  compare_price: number | null
  images: string[]
}

const route = useRoute()
const categoryId = ref<number | null>(null)
const products = ref<Product[]>([])
const loading = ref(false)
const searchQuery = ref('')

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const loadProducts = async () => {
  try {
    loading.value = true
    const params: any = {}
    
    if (categoryId.value) {
      params.category_id = categoryId.value
    }
    
    if (searchQuery.value) {
      params.search = searchQuery.value
    }
    
    const response = await productApi.list(params)
    products.value = response.data.data || []
  } catch (error) {
    console.error('Failed to load products:', error)
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  loadProducts()
}

const addToCart = async (product: Product) => {
  try {
    await cartApi.addItem({
      product_id: product.id,
      quantity: 1,
    })
    alert('Product added to cart!')
  } catch (error) {
    console.error('Failed to add to cart:', error)
    alert('Failed to add product to cart')
  }
}

onMounted(() => {
  if (route.params.categoryId) {
    categoryId.value = Number(route.params.categoryId)
  }
  loadProducts()
})

watch(() => route.params.categoryId, (newVal) => {
  if (newVal) {
    categoryId.value = Number(newVal)
  } else {
    categoryId.value = null
  }
  loadProducts()
})
</script>
