<template>
  <div class="bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="loading" class="text-center py-12">
        <p class="text-gray-500">Loading product...</p>
      </div>
      <div v-else-if="!product" class="text-center py-12">
        <p class="text-gray-500">Product not found.</p>
      </div>
      <div v-else class="lg:grid lg:grid-cols-2 lg:gap-x-8 lg:items-start">
        <!-- Image gallery -->
        <div class="flex flex-col-reverse">
          <div class="aspect-w-1 aspect-h-1 w-full">
            <img
              :src="selectedImage || 'https://via.placeholder.com/800'"
              :alt="product.name"
              class="w-full h-full object-center object-cover rounded-lg"
            />
          </div>
          <div v-if="product.images && product.images.length > 1" class="mt-4 flex gap-2">
            <button
              v-for="(image, index) in product.images"
              :key="index"
              @click="selectedImage = image"
              class="relative w-20 h-20 rounded-md overflow-hidden border-2"
              :class="selectedImage === image ? 'border-indigo-600' : 'border-gray-300'"
            >
              <img :src="image" :alt="`Product image ${index + 1}`" class="w-full h-full object-cover" />
            </button>
          </div>
        </div>

        <!-- Product info -->
        <div class="mt-10 px-4 sm:px-0 sm:mt-16 lg:mt-0">
          <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">{{ product.name }}</h1>
          
          <div class="mt-3">
            <h2 class="sr-only">Product information</h2>
            <p class="text-3xl text-gray-900">Rp {{ formatPrice(product.price) }}</p>
            <p v-if="product.compare_price" class="mt-1 text-lg text-gray-500 line-through">
              Rp {{ formatPrice(product.compare_price) }}
            </p>
          </div>

          <div class="mt-6">
            <h3 class="sr-only">Description</h3>
            <div class="text-base text-gray-700 space-y-6">
              <p>{{ product.description }}</p>
            </div>
          </div>

          <div v-if="product.category" class="mt-6">
            <h3 class="text-sm font-medium text-gray-900">Category</h3>
            <router-link
              :to="`/categories/${product.category.id}`"
              class="mt-2 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              {{ product.category.name }}
            </router-link>
          </div>

          <div v-if="product.stock !== undefined" class="mt-6">
            <p class="text-sm text-gray-500">
              <span :class="product.stock > 0 ? 'text-green-600' : 'text-red-600'">
                {{ product.stock > 0 ? `${product.stock} in stock` : 'Out of stock' }}
              </span>
            </p>
          </div>

          <div class="mt-10 flex sm:flex-col1">
            <div class="flex items-center mr-4">
              <label for="quantity" class="mr-2 text-sm font-medium text-gray-700">Quantity:</label>
              <input
                id="quantity"
                v-model.number="quantity"
                type="number"
                min="1"
                :max="product.stock"
                class="w-20 px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            <button
              @click="addToCart"
              :disabled="product.stock === 0"
              class="max-w-xs flex-1 bg-indigo-600 border border-transparent rounded-md py-3 px-8 flex items-center justify-center text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-50 focus:ring-indigo-500 disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              {{ product.stock === 0 ? 'Out of Stock' : 'Add to Cart' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { productApi, cartApi } from '@/services/api'

interface Product {
  id: number
  name: string
  description: string
  price: number
  compare_price: number | null
  stock: number
  images: string[]
  category?: {
    id: number
    name: string
  }
}

const route = useRoute()
const router = useRouter()
const product = ref<Product | null>(null)
const loading = ref(false)
const selectedImage = ref<string>('')
const quantity = ref(1)

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const addToCart = async () => {
  if (!product.value) return
  
  try {
    await cartApi.addItem({
      product_id: product.value.id,
      quantity: quantity.value,
    })
    alert('Product added to cart!')
    router.push('/cart')
  } catch (error) {
    console.error('Failed to add to cart:', error)
    alert('Failed to add product to cart')
  }
}

onMounted(async () => {
  try {
    loading.value = true
    const response = await productApi.get(Number(route.params.id))
    product.value = response.data
    if (product.value && product.value.images && product.value.images.length > 0) {
      const firstImage = product.value.images[0]
      if (firstImage) {
        selectedImage.value = firstImage
      }
    }
  } catch (error) {
    console.error('Failed to load product:', error)
  } finally {
    loading.value = false
  }
})
</script>
