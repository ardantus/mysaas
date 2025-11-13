<template>
  <div>
    <div class="md:flex md:items-center md:justify-between">
      <div class="flex-1 min-w-0">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
          {{ isEdit ? 'Edit Store' : 'Create New Store' }}
        </h2>
      </div>
    </div>

    <div class="mt-8">
      <form @submit.prevent="handleSubmit">
        <div class="shadow sm:rounded-md sm:overflow-hidden">
          <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
            <div v-if="error" class="rounded-md bg-red-50 p-4">
              <p class="text-sm text-red-800">{{ error }}</p>
            </div>

            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">
                Store Name <span class="text-red-500">*</span>
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
              />
            </div>

            <div v-if="!isEdit">
              <label for="subdomain" class="block text-sm font-medium text-gray-700">
                Subdomain <span class="text-red-500">*</span>
              </label>
              <div class="mt-1 flex rounded-md shadow-sm">
                <input
                  id="subdomain"
                  v-model="form.subdomain"
                  type="text"
                  required
                  class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300"
                  placeholder="mystore"
                />
                <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                  .mysaas.com
                </span>
              </div>
              <p class="mt-2 text-sm text-gray-500">Choose a unique subdomain for your store</p>
            </div>

            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700">
                Phone Number
              </label>
              <input
                id="phone"
                v-model="form.phone"
                type="tel"
                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
              />
            </div>

            <div>
              <label for="whatsapp" class="block text-sm font-medium text-gray-700">
                WhatsApp Number <span class="text-red-500">*</span>
              </label>
              <input
                id="whatsapp"
                v-model="form.whatsapp"
                type="tel"
                required
                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                placeholder="628123456789"
              />
              <p class="mt-2 text-sm text-gray-500">Enter WhatsApp number with country code (e.g., 628123456789)</p>
            </div>

            <div>
              <label for="address" class="block text-sm font-medium text-gray-700">
                Address
              </label>
              <textarea
                id="address"
                v-model="form.address"
                rows="3"
                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
              ></textarea>
            </div>

            <div>
              <label for="description" class="block text-sm font-medium text-gray-700">
                Description
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
              ></textarea>
            </div>

            <div v-if="isEdit" class="flex items-start">
              <div class="flex items-center h-5">
                <input
                  id="is_active"
                  v-model="form.is_active"
                  type="checkbox"
                  class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                />
              </div>
              <div class="ml-3 text-sm">
                <label for="is_active" class="font-medium text-gray-700">Active</label>
                <p class="text-gray-500">Store is active and accessible to customers</p>
              </div>
            </div>
          </div>
          <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 space-x-3">
            <router-link
              to="/dashboard/stores"
              class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Cancel
            </router-link>
            <button
              type="submit"
              :disabled="loading"
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
            >
              {{ loading ? 'Saving...' : (isEdit ? 'Update Store' : 'Create Store') }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { storeApi } from '@/services/api'

const router = useRouter()
const route = useRoute()

const isEdit = ref(false)
const loading = ref(false)
const error = ref<string | null>(null)

const form = reactive({
  name: '',
  subdomain: '',
  phone: '',
  whatsapp: '',
  address: '',
  description: '',
  is_active: true,
})

onMounted(async () => {
  if (route.params.id) {
    isEdit.value = true
    try {
      loading.value = true
      const response = await storeApi.get(Number(route.params.id))
      Object.assign(form, response.data)
    } catch (err: any) {
      error.value = 'Failed to load store data'
    } finally {
      loading.value = false
    }
  }
})

const handleSubmit = async () => {
  try {
    loading.value = true
    error.value = null

    if (isEdit.value) {
      await storeApi.update(Number(route.params.id), form)
    } else {
      await storeApi.create(form)
    }

    router.push('/dashboard/stores')
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to save store'
  } finally {
    loading.value = false
  }
}
</script>
