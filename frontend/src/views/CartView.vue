<template>
  <div class="bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

      <div v-if="loading" class="text-center py-12">
        <p class="text-gray-500">Loading cart...</p>
      </div>
      <div v-else-if="!cart || cart.items.length === 0" class="text-center py-12">
        <svg
          class="mx-auto h-12 w-12 text-gray-400"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
          />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Your cart is empty</h3>
        <p class="mt-1 text-sm text-gray-500">Start shopping to add items to your cart.</p>
        <div class="mt-6">
          <router-link
            to="/products"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
          >
            Continue Shopping
          </router-link>
        </div>
      </div>
      <div v-else class="lg:grid lg:grid-cols-12 lg:gap-x-12 lg:items-start">
        <div class="lg:col-span-7">
          <ul role="list" class="border-t border-b border-gray-200 divide-y divide-gray-200">
            <li v-for="item in cart.items" :key="item.id" class="flex py-6 sm:py-10">
              <div class="flex-shrink-0">
                <img
                  :src="item.product?.images?.[0] || 'https://via.placeholder.com/200'"
                  :alt="item.product?.name"
                  class="w-24 h-24 rounded-md object-center object-cover sm:w-32 sm:h-32"
                />
              </div>

              <div class="ml-4 flex-1 flex flex-col justify-between sm:ml-6">
                <div class="relative pr-9 sm:grid sm:grid-cols-2 sm:gap-x-6 sm:pr-0">
                  <div>
                    <div class="flex justify-between">
                      <h3 class="text-sm">
                        <router-link
                          :to="`/products/${item.product?.id}`"
                          class="font-medium text-gray-700 hover:text-gray-800"
                        >
                          {{ item.product?.name }}
                        </router-link>
                      </h3>
                    </div>
                    <p class="mt-1 text-sm font-medium text-gray-900">
                      Rp {{ formatPrice(item.price) }}
                    </p>
                  </div>

                  <div class="mt-4 sm:mt-0 sm:pr-9">
                    <label :for="`quantity-${item.id}`" class="sr-only">Quantity</label>
                    <select
                      :id="`quantity-${item.id}`"
                      :value="item.quantity"
                      @change="updateQuantity(item.id, $event)"
                      class="max-w-full rounded-md border border-gray-300 py-1.5 text-base leading-5 font-medium text-gray-700 text-left shadow-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                      <option v-for="n in 10" :key="n" :value="n">{{ n }}</option>
                    </select>

                    <div class="absolute top-0 right-0">
                      <button
                        @click="removeItem(item.id)"
                        type="button"
                        class="-m-2 p-2 inline-flex text-gray-400 hover:text-gray-500"
                      >
                        <span class="sr-only">Remove</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                          />
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>

                <p class="mt-4 flex text-sm text-gray-700 space-x-2">
                  <span>Subtotal: Rp {{ formatPrice(item.price * item.quantity) }}</span>
                </p>
              </div>
            </li>
          </ul>
        </div>

        <!-- Order summary -->
        <div class="mt-16 rounded-lg bg-gray-50 px-4 py-6 sm:p-6 lg:col-span-5 lg:mt-0 lg:p-8">
          <h2 class="text-lg font-medium text-gray-900">Order summary</h2>

          <dl class="mt-6 space-y-4">
            <div class="flex items-center justify-between">
              <dt class="text-sm text-gray-600">Subtotal</dt>
              <dd class="text-sm font-medium text-gray-900">Rp {{ formatPrice(subtotal) }}</dd>
            </div>
            <div class="border-t border-gray-200 pt-4 flex items-center justify-between">
              <dt class="text-base font-medium text-gray-900">Order total</dt>
              <dd class="text-base font-medium text-gray-900">Rp {{ formatPrice(subtotal) }}</dd>
            </div>
          </dl>

          <div class="mt-6">
            <button
              @click="checkout"
              class="w-full bg-indigo-600 border border-transparent rounded-md shadow-sm py-3 px-4 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-50 focus:ring-indigo-500"
            >
              Checkout
            </button>
          </div>

          <div class="mt-6 text-center">
            <router-link to="/products" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
              Continue Shopping
              <span aria-hidden="true"> &rarr;</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { cartApi } from '@/services/api'

interface CartItem {
  id: number
  product?: {
    id: number
    name: string
    images: string[]
  }
  quantity: number
  price: number
}

interface Cart {
  items: CartItem[]
}

const router = useRouter()
const cart = ref<Cart | null>(null)
const loading = ref(false)

const subtotal = computed(() => {
  if (!cart.value) return 0
  return cart.value.items.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const loadCart = async () => {
  try {
    loading.value = true
    const response = await cartApi.get()
    cart.value = response.data
  } catch (error) {
    console.error('Failed to load cart:', error)
  } finally {
    loading.value = false
  }
}

const updateQuantity = async (itemId: number, event: Event) => {
  const quantity = Number((event.target as HTMLSelectElement).value)
  try {
    await cartApi.updateItem(itemId, { quantity })
    await loadCart()
  } catch (error) {
    console.error('Failed to update quantity:', error)
  }
}

const removeItem = async (itemId: number) => {
  if (!confirm('Remove this item from cart?')) return
  
  try {
    await cartApi.removeItem(itemId)
    await loadCart()
  } catch (error) {
    console.error('Failed to remove item:', error)
  }
}

const checkout = () => {
  // For now, just show an alert. In production, this would go to a checkout page
  alert('Checkout functionality will be implemented with payment integration')
}

onMounted(() => {
  loadCart()
})
</script>
