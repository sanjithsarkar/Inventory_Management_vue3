<template>
    <el-card class="order-container">
        <!-- Filter Section -->
        <div class="filter-section">
            <el-space>
                <el-date-picker v-model="startDate" type="date" placeholder="From date" format="YYYY-MM-DD"
                    value-format="YYYY-MM-DD" />
                <el-date-picker v-model="endDate" type="date" placeholder="To date" format="YYYY-MM-DD"
                    value-format="YYYY-MM-DD" />
            </el-space>

            <el-input v-model="searchQuery" placeholder="Search by Order ID" clearable style="width: 240px"
                @clear="getOrders">
                <template #prefix>
                    <el-icon>
                        <Search />
                    </el-icon>
                </template>
            </el-input>
        </div>

        <!-- Order Table -->
        <el-table :data="orderData" border stripe v-loading="loading" style="width: 100%"
            @sort-change="handleSortChange">
            <el-table-column type="index" width="60" label="No." />
            <el-table-column prop="order_number" label="Order No" sortable />
            <el-table-column label="Customer">
                <template #default="{ row }">
                    {{ row.customer?.name || 'N/A' }}
                </template>
            </el-table-column>
            <el-table-column prop="quantity" label="Qty" sortable />
            <el-table-column prop="subTotal" label="Subtotal" sortable>
                <template #default="{ row }">
                    {{ formatCurrency(row.subTotal) }}
                </template>
            </el-table-column>
            <el-table-column prop="discount" label="Discount" sortable>
                <template #default="{ row }">
                    {{ row.discount < 1 ? '0%' : `${row.discount}%` }} </template>
            </el-table-column>
            <el-table-column prop="total" label="Total" sortable>
                <template #default="{ row }">
                    {{ formatCurrency(row.total) }}
                </template>
            </el-table-column>
            <el-table-column prop="paid" label="Paid" sortable>
                <template #default="{ row }">
                    {{ formatCurrency(row.paid) }}
                </template>
            </el-table-column>
            <el-table-column prop="due" label="Due" sortable>
                <template #default="{ row }">
                    {{ formatCurrency(row.due) }}
                </template>
            </el-table-column>
            <el-table-column prop="date" label="Date" sortable />
            <el-table-column label="Actions" width="180">
                <template #default="{ row }">
                    <el-button size="small" @click="showModal(row.id)" type="primary" plain>
                        <el-icon>
                            <View />
                        </el-icon> View
                    </el-button>
                    <el-button size="small" type="info" plain>
                        <router-link :to="`/employee/edit/${row.id}`">
                            <el-icon>
                                <Edit />
                            </el-icon> Edit
                        </router-link>
                    </el-button>
                </template>
            </el-table-column>
        </el-table>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            <el-pagination  
        v-model:current-page="currentPage"  
        v-model:page-size="pageSize"  
        :total="pagination.total"  
        :page-sizes="pageSizes"  
        layout="total, sizes, prev, pager, next, jumper"  
        @size-change="handleSizeChange"  
        @current-change="handleCurrentChange"  
      />  
        </div>

        <!-- Order Details Modal -->
        <el-dialog v-model="isModalVisible" title="Order Details" width="80%" :close-on-click-modal="false">
            <el-row :gutter="20">
                <el-col :span="12">
                    <el-card>
                        <template #header>
                            <h4>Customer Information</h4>
                        </template>
                        <el-descriptions v-if="order[0]?.customer" border column={1}>
                            <el-descriptions-item label="Name">{{ order[0].customer.name }}</el-descriptions-item>
                            <el-descriptions-item label="Email">{{ order[0].customer.email }}</el-descriptions-item>
                            <el-descriptions-item label="Phone">{{ order[0].customer.phone }}</el-descriptions-item>
                            <el-descriptions-item label="Address">{{ order[0].customer.address }}</el-descriptions-item>
                        </el-descriptions>
                        <el-empty v-else description="No customer data" />
                    </el-card>
                </el-col>

                <el-col :span="12">
                    <el-card>
                        <template #header>
                            <h4>Order Summary</h4>
                        </template>
                        <el-descriptions border column={1}>
                            <el-descriptions-item label="Order Number">{{ order[0]?.order_number
                                }}</el-descriptions-item>
                            <el-descriptions-item label="Quantity">{{ order[0]?.quantity }}</el-descriptions-item>
                            <el-descriptions-item label="Subtotal">{{ formatCurrency(order[0]?.subTotal)
                                }}</el-descriptions-item>
                            <el-descriptions-item label="Discount">{{ order[0]?.discount }}%</el-descriptions-item>
                            <el-descriptions-item label="Total">{{ formatCurrency(order[0]?.total)
                                }}</el-descriptions-item>
                            <el-descriptions-item label="Paid">{{ formatCurrency(order[0]?.paid)
                                }}</el-descriptions-item>
                            <el-descriptions-item label="Due">{{ formatCurrency(order[0]?.due) }}</el-descriptions-item>
                            <el-descriptions-item label="Payment Method">{{ order[0]?.payby }}</el-descriptions-item>
                            <el-descriptions-item label="Date">{{ order[0]?.date }}</el-descriptions-item>
                        </el-descriptions>
                    </el-card>
                </el-col>
            </el-row>

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

            <template #footer>
                <el-button @click="hideModal" type="danger">Close</el-button>
            </template>
        </el-dialog>
    </el-card>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { Search, View, Edit } from '@element-plus/icons-vue'

const startDate = ref('')
const endDate = ref('')
const searchQuery = ref('')
const orderData = ref([])
const order = ref({})
const orderProduct = ref([])
const isModalVisible = ref(false)
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const timeout = ref(null)

const pagination = ref({  
  current_page: 1,  
  per_page: 10,  
  total: 0,  
  last_page: 0,  
})  
const pageSizes = ref([10, 20, 50, 100])

const debouncedGetOrders = () => {
  loading.value = true
  clearTimeout(timeout.value)
  timeout.value = setTimeout(() => {
    getOrders(currentPage.value)
  }, 500)
}

watch([startDate, endDate, searchQuery], debouncedGetOrders, { immediate: true })

onBeforeUnmount(() => clearTimeout(timeout.value))

const getOrders = async (page = 1) => {
  loading.value = true
  currentPage.value = page
  pagination.value.current_page = page

  try {
    const response = await axios.get('/api/orders', {
      params: {
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
        search: searchQuery.value || undefined,
        per_page: pageSize.value,
        page
      }
    })
    orderData.value = response.data.data
    pagination.value = { ...pagination.value, ...response.data.pagination }
  } catch (error) {
    console.error('Error fetching orders:', error)
  } finally {
    loading.value = false
  }
}

const showModal = async (orderId) => {
  try {
    const [orderRes, productsRes] = await Promise.all([
      axios.get(`/api/orders/${orderId}`),
      axios.get(`/api/orders/${orderId}/products`)
    ])
    order.value = orderRes.data
    orderProduct.value = productsRes.data
    isModalVisible.value = true
  } catch (error) {
    console.error('Error fetching order details:', error)
  }
}

const hideModal = () => {
  isModalVisible.value = false
}

const handleSizeChange = (val) => {
  pageSize.value = val
  getOrders(1) // reset to page 1
}

const handleCurrentChange = (val) => {
  getOrders(val)
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value || 0)
}

onMounted(() => getOrders())
</script>


<style scoped>
.order-container {
    margin: 20px;
}

.filter-section {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    align-items: center;
}

.pagination-wrapper {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
}

.el-table {
    margin-top: 20px;
}

.el-descriptions {
    margin-top: 20px;
}

.mt-4 {
    margin-top: 1rem;
}
</style>