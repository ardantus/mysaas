<template>
  <div class="product-list">
    <h2>Products</h2>
    
    <div class="filters">
      <input 
        v-model="searchQuery" 
        @input="searchProducts" 
        placeholder="Search products..."
        class="search-input"
      />
      
      <select v-model="selectedCategory" @change="filterByCategory" class="category-select">
        <option value="">All Categories</option>
        <option v-for="category in categories" :key="category.id" :value="category.id">
          {{ category.name }}
        </option>
      </select>
    </div>

    <div class="products-grid">
      <div v-for="product in products" :key="product.id" class="product-card">
        <img 
          v-if="product.images && product.images.length > 0" 
          :src="product.images[0]" 
          :alt="product.name"
          class="product-image"
        />
        <div class="product-info">
          <h3>{{ product.name }}</h3>
          <p class="product-price">Rp {{ formatPrice(product.price) }}</p>
          <p v-if="product.compare_price" class="product-compare-price">
            <s>Rp {{ formatPrice(product.compare_price) }}</s>
          </p>
          <p class="product-stock">Stock: {{ product.stock }}</p>
          <button 
            @click="addToCart(product.id)" 
            :disabled="product.stock === 0"
            class="add-to-cart-btn"
          >
            {{ product.stock > 0 ? 'Add to Cart' : 'Out of Stock' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="loading">Loading...</div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { productApi, categoryApi, cartApi } from '../services/api'

const products = ref<any[]>([])
const categories = ref<any[]>([])
const searchQuery = ref('')
const selectedCategory = ref('')
const loading = ref(false)

const formatPrice = (price: number) => {
  return price.toLocaleString('id-ID')
}

const loadProducts = async () => {
  loading.value = true
  try {
    const params: any = {}
    if (searchQuery.value) params.search = searchQuery.value
    if (selectedCategory.value) params.category_id = selectedCategory.value
    
    const response = await productApi.list(params)
    products.value = response.data.data
  } catch (error) {
    console.error('Failed to load products:', error)
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  try {
    const response = await categoryApi.list()
    categories.value = response.data
  } catch (error) {
    console.error('Failed to load categories:', error)
  }
}

const searchProducts = () => {
  loadProducts()
}

const filterByCategory = () => {
  loadProducts()
}

const addToCart = async (productId: number) => {
  try {
    await cartApi.addItem({ product_id: productId, quantity: 1 })
    alert('Product added to cart!')
  } catch (error) {
    console.error('Failed to add to cart:', error)
    alert('Failed to add product to cart')
  }
}

onMounted(() => {
  loadProducts()
  loadCategories()
})
</script>

<style scoped>
.product-list {
  padding: 20px;
}

.filters {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.search-input,
.category-select {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.search-input {
  flex: 1;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.product-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  transition: box-shadow 0.3s;
}

.product-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.product-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.product-info {
  padding: 15px;
}

.product-info h3 {
  margin: 0 0 10px;
  font-size: 18px;
}

.product-price {
  font-size: 20px;
  font-weight: bold;
  color: #27ae60;
  margin: 5px 0;
}

.product-compare-price {
  font-size: 14px;
  color: #999;
  margin: 5px 0;
}

.product-stock {
  font-size: 14px;
  color: #666;
  margin: 5px 0;
}

.add-to-cart-btn {
  width: 100%;
  padding: 10px;
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  margin-top: 10px;
}

.add-to-cart-btn:hover:not(:disabled) {
  background-color: #2980b9;
}

.add-to-cart-btn:disabled {
  background-color: #bdc3c7;
  cursor: not-allowed;
}

.loading {
  text-align: center;
  padding: 20px;
  color: #666;
}
</style>
