<template>
  <div class="admin-dashboard">
    <div class="dashboard-header">
      <h1>Store Admin Dashboard</h1>
      <p>Manage your products, orders, and store settings</p>
    </div>

    <div class="dashboard-stats">
      <div class="stat-card">
        <h3>Total Orders</h3>
        <p class="stat-value">{{ stats.totalOrders }}</p>
      </div>
      <div class="stat-card">
        <h3>Pending Orders</h3>
        <p class="stat-value pending">{{ stats.pendingOrders }}</p>
      </div>
      <div class="stat-card">
        <h3>Total Products</h3>
        <p class="stat-value">{{ stats.totalProducts }}</p>
      </div>
      <div class="stat-card">
        <h3>Revenue</h3>
        <p class="stat-value">Rp {{ formatPrice(stats.revenue) }}</p>
      </div>
    </div>

    <div class="dashboard-sections">
      <div class="section">
        <h2>Recent Orders</h2>
        <div v-if="orders.length > 0" class="orders-list">
          <div v-for="order in orders" :key="order.id" class="order-item">
            <div class="order-info">
              <p class="order-number">Order #{{ order.order_number }}</p>
              <p class="order-customer">{{ order.customer_name }}</p>
              <p class="order-date">{{ formatDate(order.created_at) }}</p>
            </div>
            <div class="order-status">
              <span :class="['status-badge', order.status]">
                {{ order.status }}
              </span>
            </div>
            <div class="order-actions">
              <select 
                v-model="order.status" 
                @change="updateOrderStatus(order)"
                class="status-select"
              >
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
              <button @click="viewOrder(order.id)" class="view-btn">View</button>
            </div>
          </div>
        </div>
        <p v-else class="no-data">No orders yet</p>
      </div>

      <div class="section">
        <div class="section-header">
          <h2>Quick Actions</h2>
        </div>
        <div class="quick-actions">
          <button @click="goToProducts" class="action-btn">
            Manage Products
          </button>
          <button @click="goToCategories" class="action-btn">
            Manage Categories
          </button>
          <button @click="goToOrders" class="action-btn">
            View All Orders
          </button>
          <button @click="goToSettings" class="action-btn">
            Store Settings
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { orderApi } from '../services/api'

const router = useRouter()
const orders = ref<any[]>([])
const stats = ref({
  totalOrders: 0,
  pendingOrders: 0,
  totalProducts: 0,
  revenue: 0,
})

const formatPrice = (price: number) => {
  return price.toLocaleString('id-ID')
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const loadOrders = async () => {
  try {
    const response = await orderApi.list({ per_page: 10 })
    orders.value = response.data.data
    
    // Calculate stats
    stats.value.totalOrders = response.data.total
    stats.value.pendingOrders = orders.value.filter(o => o.status === 'pending').length
    stats.value.revenue = orders.value.reduce((sum, o) => sum + parseFloat(o.total), 0)
  } catch (error) {
    console.error('Failed to load orders:', error)
  }
}

const updateOrderStatus = async (order: any) => {
  try {
    await orderApi.updateStatus(order.id, { status: order.status })
    alert('Order status updated successfully! Customer will be notified via WhatsApp.')
    loadOrders()
  } catch (error) {
    console.error('Failed to update order status:', error)
    alert('Failed to update order status')
  }
}

const viewOrder = (orderId: number) => {
  router.push(`/admin/orders/${orderId}`)
}

const goToProducts = () => {
  router.push('/admin/products')
}

const goToCategories = () => {
  router.push('/admin/categories')
}

const goToOrders = () => {
  router.push('/admin/orders')
}

const goToSettings = () => {
  router.push('/admin/settings')
}

onMounted(() => {
  loadOrders()
})
</script>

<style scoped>
.admin-dashboard {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

.dashboard-header {
  margin-bottom: 30px;
}

.dashboard-header h1 {
  margin: 0 0 10px;
  color: #2c3e50;
}

.dashboard-header p {
  color: #7f8c8d;
  margin: 0;
}

.dashboard-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.stat-card h3 {
  margin: 0 0 10px;
  color: #7f8c8d;
  font-size: 14px;
  font-weight: normal;
}

.stat-value {
  margin: 0;
  font-size: 32px;
  font-weight: bold;
  color: #2c3e50;
}

.stat-value.pending {
  color: #e67e22;
}

.dashboard-sections {
  display: grid;
  gap: 30px;
}

.section {
  background: white;
  padding: 25px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.section h2 {
  margin: 0 0 20px;
  color: #2c3e50;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border: 1px solid #ecf0f1;
  border-radius: 4px;
}

.order-info p {
  margin: 5px 0;
}

.order-number {
  font-weight: bold;
  color: #2c3e50;
}

.order-customer {
  color: #7f8c8d;
}

.order-date {
  font-size: 12px;
  color: #95a5a6;
}

.status-badge {
  padding: 5px 15px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: bold;
  text-transform: uppercase;
}

.status-badge.pending {
  background: #fff3cd;
  color: #856404;
}

.status-badge.confirmed {
  background: #d1ecf1;
  color: #0c5460;
}

.status-badge.processing {
  background: #e2e3e5;
  color: #383d41;
}

.status-badge.shipped {
  background: #cfe2ff;
  color: #084298;
}

.status-badge.delivered {
  background: #d1e7dd;
  color: #0f5132;
}

.status-badge.cancelled {
  background: #f8d7da;
  color: #842029;
}

.order-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.status-select {
  padding: 5px 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.view-btn {
  padding: 5px 15px;
  background: #3498db;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.view-btn:hover {
  background: #2980b9;
}

.no-data {
  text-align: center;
  color: #7f8c8d;
  padding: 40px;
}

.quick-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.action-btn {
  padding: 15px;
  background: #3498db;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  font-weight: bold;
}

.action-btn:hover {
  background: #2980b9;
}
</style>
