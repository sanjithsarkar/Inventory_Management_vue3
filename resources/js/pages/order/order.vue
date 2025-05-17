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
                    <el-button @click="showModal(row.id)" type="primary" plain size="small">
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
            <el-pagination v-model:current-page="currentPage" v-model:page-size="pageSize" :total="pagination.total"
                :page-sizes="pageSizes" layout="total, sizes, prev, pager, next, jumper" @size-change="handleSizeChange"
                @current-change="handleCurrentChange" />
        </div>

        <!-- Order Details Modal -->
        <el-dialog v-model="isModalVisible" title="Order Details" width="80%" center :style="{ marginTop: '10vh' }">
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
                        <el-empty image-size="56" v-else description="No customer data" />
                    </el-card>
                </el-col>

                <!-- Order Summary -->
                <el-col :span="12">
                    <el-card>
                        <template #header>
                            <h4>Order Summary</h4>
                        </template>
                        <el-descriptions border :column="1">
                            <el-descriptions-item label="Product Id">{{ orderProduct[0]?.pro_id
                                }}</el-descriptions-item>
                            <el-descriptions-item label="Product Name">{{ orderProduct[0]?.name
                                }}</el-descriptions-item>
                            <el-descriptions-item label="quantity">{{ orderProduct[0]?.quantity
                                }}</el-descriptions-item>
                            <el-descriptions-item label="price">{{ formatCurrency(orderProduct[0]?.price) }}</el-descriptions-item>
                        </el-descriptions>
                    </el-card>
                </el-col>
            </el-row>

            <!-- Products -->
            <el-card class="mt-4">
                <template #header>
                    <h4>Order Products</h4>
                </template>
                <el-table :data="order" border>
                    <el-table-column type="index" width="60" />
                    <el-table-column prop="order_number" label="Product ID" />
                    <el-table-column prop="quantity" label="Product Name" />
                    <el-table-column prop="subTotal" label="SubTotal" />
                    <el-table-column prop="discount" label="Discount" />
                    <el-table-column prop="total" label="Total" />
                    <el-table-column prop="paid" label="Paid" />
                    <el-table-column prop="due" label="Due" />
                    <el-table-column prop="payby" label="PayBy" />
                    <el-table-column prop="date" label="date" />
                </el-table>
            </el-card>
            <template #footer>
                <el-button @click="handleDialogClose" type="danger">Close</el-button>
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

const dialogVisible = ref(false)

const debouncedGetOrders = () => {
    loading.value = true
    clearTimeout(timeout.value)
    timeout.value = setTimeout(() => {
        getOrders(currentPage.value)
    }, 500)
}

watch([startDate, endDate, searchQuery], debouncedGetOrders, { immediate: true })

onBeforeUnmount(() => clearTimeout(timeout.value))

// const dialogVisible = ref(false)
const selectedOrderId = ref(null)

const openDialog = (id) => {
    console.log('Order ID:', id)
    selectedOrderId.value = id
    dialogVisible.value = true
}

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
        loading.value = true

        const [orderRes, productsRes] = await Promise.all([
            axios.get(`/api/order/${orderId}`),
            axios.get(`/api/order/product/${orderId}`)
        ])

        order.value = orderRes.data
        orderProduct.value = productsRes.data
        console.log('Order:', order.value)
        isModalVisible.value = true
    } catch (error) {
        console.error('Error fetching order details:', error)
    } finally {
        loading.value = false
    }
}
const handleDialogClose = () => {
    isModalVisible.value = false
    order.value = {}
    orderProduct.value = []
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