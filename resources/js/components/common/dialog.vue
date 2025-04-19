<template>
    <el-dialog v-model="visible" title="Order Details" width="80%">
        <template #default>
      <el-skeleton v-if="loading" rows="8" animated />
      <div v-else>
        <el-row :gutter="20">
        <!-- Customer Info -->
        <el-col :span="12">
          <el-card>
            <template #header>
              <h4>Customer Information</h4>
            </template>
            <el-descriptions v-if="order[0]?.customer" border :column="1">
              <el-descriptions-item label="Name">{{ order[0].customer.name }}</el-descriptions-item>
              <el-descriptions-item label="Email">{{ order[0].customer.email }}</el-descriptions-item>
              <el-descriptions-item label="Phone">{{ order[0].customer.phone }}</el-descriptions-item>
              <el-descriptions-item label="Address">{{ order[0].customer.address }}</el-descriptions-item>
            </el-descriptions>
            <el-empty v-else description="No customer data" />
          </el-card>
        </el-col>
  
        <!-- Order Summary -->
        <el-col :span="12">
          <el-card>
            <template #header>
              <h4>Order Summary</h4>
            </template>
            <el-descriptions border :column="1">
              <el-descriptions-item label="Order Number">{{ order[0]?.order_number }}</el-descriptions-item>
              <el-descriptions-item label="Quantity">{{ order[0]?.quantity }}</el-descriptions-item>
              <el-descriptions-item label="Subtotal">{{ formatCurrency(order[0]?.subTotal) }}</el-descriptions-item>
              <el-descriptions-item label="Discount">{{ order[0]?.discount }}%</el-descriptions-item>
              <el-descriptions-item label="Total">{{ formatCurrency(order[0]?.total) }}</el-descriptions-item>
              <el-descriptions-item label="Paid">{{ formatCurrency(order[0]?.paid) }}</el-descriptions-item>
              <el-descriptions-item label="Due">{{ formatCurrency(order[0]?.due) }}</el-descriptions-item>
              <el-descriptions-item label="Payment Method">{{ order[0]?.payby }}</el-descriptions-item>
              <el-descriptions-item label="Date">{{ order[0]?.date }}</el-descriptions-item>
            </el-descriptions>
          </el-card>
        </el-col>
      </el-row>
  
      <!-- Products -->
      <el-card class="mt-4">
        <template #header>
          <h4>Order Products</h4>
        </template>
        <el-table :data="orderProduct" border>
          <el-table-column type="index" width="60" />
          <el-table-column prop="pro_id" label="Product ID" />
          <el-table-column prop="name" label="Product Name" />
          <el-table-column prop="quantity" label="Quantity" />
          <el-table-column prop="price" label="Price">
            <template #default="{ row }">
              {{ formatCurrency(row.price) }}
            </template>
          </el-table-column>
        </el-table>
      </el-card>
      </div>
    </template>
    </el-dialog>
  </template>
  
  <script setup lang="ts">
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps<{
  visible: boolean
  id: number | null
}>()

const visible = defineModel('visible')

const order = ref<any[]>([])
const orderProduct = ref<any[]>([])
const loading = ref(false)

const fetchOrderDetails = async () => {
  if (!props.id) return
  loading.value = true
  try {
    const [orderRes, productsRes] = await Promise.all([
      axios.get(`/api/orders/${props.id}`),
      axios.get(`/api/orders/${props.id}/products`)
    ])
    order.value = [orderRes.data] // Wrap in array to keep format
    orderProduct.value = productsRes.data
  } catch (error) {
    console.error('Error fetching order details:', error)
  } finally {
    loading.value = false
  }
}

// Watch when modal opens and orderId is set
watch(
  () => props.visible,
  (val) => {
    if (val && props.id) {
      fetchOrderDetails()
    }
  }
)

function formatCurrency(value: number | string) {
  const num = Number(value) || 0
  return `$${num.toFixed(2)}`
}
</script>

  