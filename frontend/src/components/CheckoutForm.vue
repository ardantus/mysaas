<template>
  <div class="checkout">
    <h2>Checkout</h2>

    <form @submit.prevent="submitOrder" class="checkout-form">
      <div class="form-group">
        <label for="customer_name">Full Name *</label>
        <input 
          id="customer_name"
          v-model="formData.customer_name" 
          type="text" 
          required
          class="form-input"
        />
      </div>

      <div class="form-group">
        <label for="customer_phone">Phone Number (WhatsApp) *</label>
        <input 
          id="customer_phone"
          v-model="formData.customer_phone" 
          type="tel" 
          required
          placeholder="08123456789"
          class="form-input"
        />
      </div>

      <div class="form-group">
        <label for="customer_email">Email (Optional)</label>
        <input 
          id="customer_email"
          v-model="formData.customer_email" 
          type="email"
          class="form-input"
        />
      </div>

      <div class="form-group">
        <label for="customer_address">Delivery Address *</label>
        <textarea 
          id="customer_address"
          v-model="formData.customer_address" 
          required
          rows="4"
          class="form-input"
        ></textarea>
      </div>

      <div class="form-group">
        <label for="shipping_cost">Shipping Cost (Rp)</label>
        <input 
          id="shipping_cost"
          v-model.number="formData.shipping_cost" 
          type="number"
          min="0"
          class="form-input"
        />
      </div>

      <div class="form-group">
        <label for="notes">Order Notes (Optional)</label>
        <textarea 
          id="notes"
          v-model="formData.notes" 
          rows="3"
          placeholder="Any special instructions..."
          class="form-input"
        ></textarea>
      </div>

      <div class="order-summary">
        <h3>Order Summary</h3>
        <p>Subtotal: Rp {{ formatPrice(subtotal) }}</p>
        <p>Shipping: Rp {{ formatPrice(formData.shipping_cost || 0) }}</p>
        <p class="total">Total: Rp {{ formatPrice(total) }}</p>
      </div>

      <button type="submit" :disabled="submitting" class="submit-btn">
        {{ submitting ? 'Processing...' : 'Place Order via WhatsApp' }}
      </button>

      <p class="whatsapp-note">
        After placing your order, you will be contacted via WhatsApp to confirm your order details.
      </p>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { orderApi } from '../services/api'

const router = useRouter()
const submitting = ref(false)
const subtotal = ref(0) // This should be loaded from cart

const formData = ref({
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  customer_address: '',
  shipping_cost: 0,
  notes: '',
})

const total = computed(() => {
  return subtotal.value + (formData.value.shipping_cost || 0)
})

const formatPrice = (price: number) => {
  return price.toLocaleString('id-ID')
}

const submitOrder = async () => {
  submitting.value = true
  try {
    const response = await orderApi.checkout(formData.value)
    alert('Order placed successfully! The store will contact you via WhatsApp shortly.')
    router.push(`/order/${response.data.order.id}`)
  } catch (error: any) {
    console.error('Failed to place order:', error)
    alert(error.response?.data?.error || 'Failed to place order')
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.checkout {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
}

.checkout-form {
  background: white;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
  color: #333;
}

.form-input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.form-input:focus {
  outline: none;
  border-color: #3498db;
}

.order-summary {
  margin: 30px 0;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 4px;
}

.order-summary h3 {
  margin-top: 0;
}

.order-summary p {
  margin: 10px 0;
  font-size: 16px;
}

.order-summary .total {
  font-size: 20px;
  font-weight: bold;
  color: #27ae60;
  margin-top: 15px;
  padding-top: 15px;
  border-top: 2px solid #ddd;
}

.submit-btn {
  width: 100%;
  padding: 15px;
  background-color: #25d366;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
  font-weight: bold;
}

.submit-btn:hover:not(:disabled) {
  background-color: #20ba5a;
}

.submit-btn:disabled {
  background-color: #bdc3c7;
  cursor: not-allowed;
}

.whatsapp-note {
  margin-top: 15px;
  text-align: center;
  font-size: 14px;
  color: #666;
  font-style: italic;
}
</style>
