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
            <el-table-column label="Actions" width="250">
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
                    <el-button @click="printOrder(row.id)" type="success" plain size="small">
                        <el-icon>
                            <Printer />
                        </el-icon> Print
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
                            <el-descriptions-item label="price">{{ formatCurrency(orderProduct[0]?.price)
                            }}</el-descriptions-item>
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
                <div class="dialog-footer">
                    <el-button @click="printOrderDetails" type="primary">
                        <el-icon>
                            <Printer />
                        </el-icon> Print Details
                    </el-button>
                    <el-button @click="handleDialogClose" type="danger">Close</el-button>
                </div>
            </template>
        </el-dialog>

        <div id="printable-order" class="printable-content">
            <div v-if="printableOrder" class="modern-invoice">
                <!-- Invoice Header -->
                <header class="invoice-header">
                    <div class="company-info">
                        <h1 class="company-name">BuyBuddy</h1>
                        <p class="company-details">
                            123 Business Street, City, State<br>
                            Phone: (123) 456-7890 | Email: info@yourbusiness.com<br>
                            www.yourbusiness.com
                        </p>
                    </div>
                    <div class="invoice-title">
                        <h2>INVOICE</h2>
                        <div class="invoice-meta">
                            <p><strong>Order #:</strong> {{ printableOrder.order_number }}</p>
                            <p><strong>Date:</strong> {{ printableOrder.date }}</p>
                        </div>
                    </div>
                </header>

                <!-- Customer Information -->
                <section class="customer-section" v-if="printableOrder.customer">
                    <div class="section-title">BILL TO</div>
                    <div class="customer-details">
                        <p class="customer-name">{{ printableOrder.customer.name }}</p>
                        <p v-if="printableOrder.customer.email" >{{ printableOrder.customer.email }}</p>
                        <p v-if="printableOrder.customer.phone">{{ printableOrder.customer.phone }}</p>
                        <p v-if="printableOrder.customer.address">{{ printableOrder.customer.address }}</p>
                    </div>
                </section>

                <!-- Order Products -->
                <section class="products-section" v-if="printableProducts.length">
                    <div class="section-title">ORDER ITEMS</div>
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th class="text-left">ITEM</th>
                                <th class="text-center">SKU</th>
                                <th class="text-center">QTY</th>
                                <th class="text-right">UNIT PRICE</th>
                                <th class="text-right">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in printableProducts" :key="product.id">
                                <td class="text-left">{{ product.name }}</td>
                                <td class="text-center">{{ product.pro_id }}</td>
                                <td class="text-center">{{ product.quantity }}</td>
                                <td class="text-right">{{ formatCurrency(product.price) }}</td>
                                <td class="text-right">{{ formatCurrency(product.price * product.quantity) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- Order Summary -->
                <section class="summary-section">
                    <div class="summary-grid">
                        <div class="summary-label">Subtotal:</div>
                        <div class="summary-value">{{ formatCurrency(printableOrder.subTotal) }}</div>

                        <div class="summary-label">Discount ({{ printableOrder.discount }}%):</div>
                        <div class="summary-value discount">-{{ formatCurrency(printableOrder.subTotal *
                            (printableOrder.discount/100)) }}</div>

                        <div class="summary-label">Tax:</div>
                        <div class="summary-value">{{ formatCurrency(printableOrder.total - printableOrder.subTotal +
                            (printableOrder.subTotal * (printableOrder.discount/100))) }}</div>

                        <div class="summary-label grand-total">Total:</div>
                        <div class="summary-value grand-total">{{ formatCurrency(printableOrder.total) }}</div>

                        <div class="summary-label">Amount Paid:</div>
                        <div class="summary-value">{{ formatCurrency(printableOrder.paid) }}</div>

                        <div class="summary-label">Balance Due:</div>
                        <div class="summary-value">{{ formatCurrency(printableOrder.due) }}</div>
                    </div>

                    <div class="payment-method">
                        <p><strong>Payment Method:</strong> {{ printableOrder.payby }}</p>
                    </div>
                </section>

                <!-- Footer -->
                <!-- <footer class="invoice-footer">
      <p>Thank you for your business!</p>
      <p class="terms">Payment terms: Net 30 days. Late payments subject to 1.5% monthly interest.</p>
    </footer> -->
            </div>
        </div>


    </el-card>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { Search, View, Edit, Printer } from '@element-plus/icons-vue'


const startDate = ref('')
const endDate = ref('')
const searchQuery = ref('')
const orderData = ref([])
const order = ref([])
const orderProduct = ref([])
const isModalVisible = ref(false)
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const timeout = ref(null)
const printableOrder = ref(null)
const printableProducts = ref([])

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

        // Make sure we're handling the response data correctly
        order.value = Array.isArray(orderRes.data) ? orderRes.data : [orderRes.data]
        orderProduct.value = Array.isArray(productsRes.data) ? productsRes.data : [productsRes.data]

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
    // Reset to empty arrays instead of empty objects
    order.value = []
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

const printOrder = async (orderId) => {
    try {
        loading.value = true

        // Fetch order and product data
        const [orderRes, productsRes] = await Promise.all([
            axios.get(`/api/order/${orderId}`),
            axios.get(`/api/order/product/${orderId}`)
        ])

        // Make sure we're handling the response data correctly
        printableOrder.value = Array.isArray(orderRes.data) ? orderRes.data[0] : orderRes.data
        printableProducts.value = Array.isArray(productsRes.data) ? productsRes.data : [productsRes.data]
        
        // Show the printable content
        setTimeout(() => {
            window.print()
        }, 300)
    } catch (error) {
        console.error('Error preparing order for print:', error)
    } finally {
        loading.value = false
    }
}

const printOrderDetails = () => {
    // Use the current modal data for printing
    printableOrder.value = order.value.length > 0 ? order.value[0] : {}
    printableProducts.value = orderProduct.value
    
    // Show the printable content and trigger print
    setTimeout(() => {
        window.print()
    }, 300)
}

// Function to handle sorting
const handleSortChange = ({ prop, order }) => {
    // Implement sorting logic here if needed
    console.log('Sort changed:', prop, order)
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

.dialog-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.print-header {
    padding: 20px 0;
    margin-bottom: 30px;
    border-bottom: 2px solid #f0f2f5;
}

.header-container {
    display: flex;
    justify-content: space-between;
    gap: 30px;
}

.store-info {
    flex: 1;
    padding-right: 20px;
}

.store-brand {
    margin-bottom: 15px;
}

.store-name {
    color: #409EFF;
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 5px 0;
    letter-spacing: 0.5px;
}

.store-tagline {
    color: #666;
    font-size: 14px;
    font-weight: 500;
}

.store-contact {
    margin-top: 15px;
}

.contact-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    color: #555;
    font-size: 14px;
}

.contact-item .el-icon {
    margin-right: 10px;
    color: #409EFF;
    font-size: 16px;
}

.invoice-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.invoice-card {
    background: #f8fafc;
    padding: 15px 20px;
    border-radius: 8px;
    border-left: 4px solid #409EFF;
    align-self: flex-end;
}

.invoice-title {
    color: #409EFF;
    font-size: 24px;
    margin: 0 0 10px 0;
    text-align: right;
}

.invoice-meta {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.meta-row {
    display: flex;
    justify-content: space-between;
}

.meta-label {
    font-weight: 500;
    color: #666;
}

.meta-value {
    font-weight: 600;
    color: #333;
}

.customer-card {
    background: #f8fafc;
    padding: 15px 20px;
    border-radius: 8px;
    border-left: 4px solid #67C23A;
}

.section-title {
    color: #67C23A;
    font-size: 16px;
    margin: 0 0 10px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.customer-details {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.detail-row {
    display: flex;
}

.detail-label {
    font-weight: 500;
    color: #666;
    min-width: 80px;
}

.detail-value {
    font-weight: 500;
    color: #333;
}

@media print {
    .print-header {
        padding-top: 0;
        border-bottom: 2px solid #ddd;
    }

    .store-name {
        color: #0066cc !important;
    }

    .invoice-card,
    .customer-card {
        background: none !important;
        border-left: 4px solid #0066cc !important;
    }

    .invoice-title {
        color: #0066cc !important;
    }
}
</style>
