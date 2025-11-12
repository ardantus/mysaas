<template>
  <div class="shopping-cart">
    <h2>Shopping Cart</h2>

    <div v-if="cart && cart.items && cart.items.length > 0">
      <div v-for="item in cart.items" :key="item.id" class="cart-item">
        <div class="item-info">
          <h3>{{ item.product.name }}</h3>
          <p class="item-price">Rp {{ formatPrice(item.price) }}</p>
        </div>
        
        <div class="item-actions">
          <div class="quantity-control">
            <button @click="decreaseQuantity(item)" class="qty-btn">-</button>
            <input 
              v-model.number="item.quantity" 
              @change="updateQuantity(item)"
              type="number" 
              min="1"
              class="qty-input"
            />
            <button @click="increaseQuantity(item)" class="qty-btn">+</button>
          </div>
          
          <p class="item-subtotal">
            Subtotal: Rp {{ formatPrice(item.price * item.quantity) }}
          </p>
          
          <button @click="removeItem(item.id)" class="remove-btn">Remove</button>
        </div>
      </div>

      <div class="cart-summary">
        <h3>Total: Rp {{ formatPrice(total) }}</h3>
        <button @click="proceedToCheckout" class="checkout-btn">
          Proceed to Checkout
        </button>
      </div>
    </div>

    <div v-else class="empty-cart">
      <p>Your cart is empty</p>
      <router-link to="/products" class="continue-shopping">
        Continue Shopping
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { cartApi } from '../services/api'

const router = useRouter()
const cart = ref<any>(null)
const loading = ref(false)

const formatPrice = (price: number) => {
  return price.toLocaleString('id-ID')
}

const total = computed(() => {
  if (!cart.value || !cart.value.items) return 0
  return cart.value.items.reduce((sum: number, item: any) => {
    return sum + (item.price * item.quantity)
  }, 0)
})

const loadCart = async () => {
  loading.value = true
  try {
    const response = await cartApi.get()
    cart.value = response.data.cart
  } catch (error) {
    console.error('Failed to load cart:', error)
  } finally {
    loading.value = false
  }
}

const updateQuantity = async (item: any) => {
  try {
    await cartApi.updateItem(item.id, { quantity: item.quantity })
    await loadCart()
  } catch (error) {
    console.error('Failed to update quantity:', error)
    alert('Failed to update quantity')
  }
}

const increaseQuantity = (item: any) => {
  item.quantity++
  updateQuantity(item)
}

const decreaseQuantity = (item: any) => {
  if (item.quantity > 1) {
    item.quantity--
    updateQuantity(item)
  }
}

const removeItem = async (itemId: number) => {
  if (confirm('Remove this item from cart?')) {
    try {
      await cartApi.removeItem(itemId)
      await loadCart()
    } catch (error) {
      console.error('Failed to remove item:', error)
      alert('Failed to remove item')
    }
  }
}

const proceedToCheckout = () => {
  router.push('/checkout')
}

onMounted(() => {
  loadCart()
})
</script>

<style scoped>
.shopping-cart {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.cart-item {
  display: flex;
  justify-content: space-between;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
  margin-bottom: 15px;
}

.item-info h3 {
  margin: 0 0 10px;
}

.item-price {
  color: #27ae60;
  font-weight: bold;
}

.item-actions {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 10px;
}

.quantity-control {
  display: flex;
  align-items: center;
  gap: 10px;
}

.qty-btn {
  width: 30px;
  height: 30px;
  border: 1px solid #ddd;
  background: white;
  cursor: pointer;
  border-radius: 4px;
}

.qty-btn:hover {
  background: #f0f0f0;
}

.qty-input {
  width: 60px;
  text-align: center;
  padding: 5px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.item-subtotal {
  font-weight: bold;
  color: #27ae60;
}

.remove-btn {
  padding: 5px 15px;
  background-color: #e74c3c;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.remove-btn:hover {
  background-color: #c0392b;
}

.cart-summary {
  margin-top: 30px;
  padding: 20px;
  border: 2px solid #27ae60;
  border-radius: 8px;
  text-align: right;
}

.cart-summary h3 {
  color: #27ae60;
  margin-bottom: 15px;
}

.checkout-btn {
  padding: 15px 30px;
  background-color: #27ae60;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
  font-weight: bold;
}

.checkout-btn:hover {
  background-color: #229954;
}

.empty-cart {
  text-align: center;
  padding: 40px;
}

.empty-cart p {
  font-size: 18px;
  color: #666;
  margin-bottom: 20px;
}

.continue-shopping {
  display: inline-block;
  padding: 10px 20px;
  background-color: #3498db;
  color: white;
  text-decoration: none;
  border-radius: 4px;
}

.continue-shopping:hover {
  background-color: #2980b9;
}
</style>
