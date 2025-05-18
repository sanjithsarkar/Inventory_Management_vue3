<template>
    <div class="product-management-container">
        <!-- Header with actions -->
        <div class="header-container">
            <h2 class="page-title">Product Management</h2>
            <div class="action-buttons">
                <el-input v-model="searchQuery" placeholder="Search products..." clearable style="width: 300px"
                    @clear="handleSearchClear">
                    <template #prefix>
                        <el-icon>
                            <Search />
                        </el-icon>
                    </template>
                </el-input>

                <el-button type="primary" @click="goToCreateProduct">
                    <el-icon>
                        <Plus />
                    </el-icon>
                    <span>Add Product</span>
                </el-button>

                <el-button type="danger" :disabled="selectedProductIds.length === 0" @click="confirmBulkDelete">
                    <el-icon>
                        <Delete />
                    </el-icon>
                    <span>Delete Selected</span>
                </el-button>
            </div>
        </div>

        <!-- Product Table -->
        <el-card shadow="never" class="table-card">
            <el-table v-loading="loading" :data="productData.data" style="width: 100%"
                @selection-change="handleSelectionChange" border stripe>
                <el-table-column type="selection" width="50" />

                <el-table-column prop="id" label="ID" width="65" sortable />

                <el-table-column prop="name" label="Name" width="200" sortable>
                    <template #default="{ row }">
                        <router-link :to="`/product/edit/${row.id}`" class="product-link">
                            {{ row.name }}
                        </router-link>
                    </template>
                </el-table-column>
                <el-table-column prop="sku" label="SKU" width="135" sortable />
                <el-table-column prop="category.name" label="Category" width="130" sortable />
                <el-table-column prop="supplier.name" label="Supplier" width="130" sortable>
                    <template #default="{ row }">
                        <span>{{ row.supplier ? row.supplier.name : 'N/A' }}</span>
                    </template>
                </el-table-column>

                <el-table-column prop="quantity" label="Quantity" width="110" sortable>
                    <template #default="{ row }">
                        <el-tag :type="row.quantity > 0 ? 'success' : 'danger'">
                            {{ row.quantity }}
                        </el-tag>
                    </template>
                </el-table-column>

                <el-table-column prop="selling_price" label="Sale Price" width="120" sortable>
                    <template #default="{ row }">
                        {{ formatCurrency(row.selling_price) }}
                    </template>
                </el-table-column>
                <el-table-column prop="buying_date" label="Buying Date" width="125" sortable />

                <el-table-column label="Image" width="100">
                    <template #default="{ row }">
                        <el-image v-if="row.image_url" :src="row.image_url" :alt="row.name" fit="cover"
                            style="width: 50px; height: 50px" :preview-src-list="[row.image_url]" hide-on-click-modal>
                            <template #error>
                                <div class="image-error">
                                    <el-icon>
                                        <Picture />
                                    </el-icon>
                                </div>
                            </template>
                        </el-image>
                        <el-tag v-else type="info">No Image</el-tag>
                    </template>
                </el-table-column>

                <el-table-column label="Actions" width="145" fixed="right">
                    <template #default="{ row }">
                        <el-tooltip content="Edit Product" placement="top">
                            <el-button size="small" type="primary" @click="goToEditProduct(row.id)" circle>
                                <el-icon>
                                    <Edit />
                                </el-icon>
                            </el-button>
                        </el-tooltip>

                        <el-tooltip content="View Details" placement="top">
                            <el-button type="info" size="small" circle @click="viewProductDetails(row)">
                                <el-icon>
                                    <View />
                                </el-icon>
                            </el-button>
                        </el-tooltip>

                        <el-tooltip content="Delete Product" placement="top">
                            <el-button size="small" type="danger" @click="confirmDeleteProduct(row.id)" circle>
                                <el-icon>
                                    <Delete />
                                </el-icon>
                            </el-button>
                        </el-tooltip>
                    </template>
                </el-table-column>
            </el-table>

            <!-- Pagination -->
            <div class="pagination-container">
                <el-pagination v-model:current-page="currentPage" v-model:page-size="pageSize"
                    :total="productData.total || 0" :page-sizes="[10, 20, 50, 100]"
                    layout="total, sizes, prev, pager, next, jumper" background @size-change="handleSizeChange"
                    @current-change="handlePageChange" />
            </div>
        </el-card>
    </div>


    <el-dialog v-model="detailsVisible" title="Product Details" width="700px" destroy-on-close>
        <div v-if="selectedProduct" class="flex flex-col md:flex-row gap-6">
            <div class="md:w-1/3">
                <el-image v-if="selectedProduct.image_url" :src="selectedProduct.image_url" fit="cover"
                    class="h-3/12 object-cover rounded-lg border border-gray-200">
                    <template #error>
                        <div class="flex items-center justify-center bg-gray-100 text-gray-400 rounded-lg">
                            <el-icon :size="40">
                                <PictureFilled />
                            </el-icon>
                        </div>
                    </template>
                </el-image>
                <div v-else class="flex items-center justify-center h-48 bg-gray-100 text-gray-400 rounded-lg">
                    <el-icon :size="40">
                        <PictureFilled />
                    </el-icon>
                </div>
            </div>

            <div class="md:w-2/3">
                <h3 class="text-xl font-bold mb-4">{{ selectedProduct.name }}</h3>

                <el-descriptions :column="1" border>
                    <el-descriptions-item label="Category">{{ selectedProduct.category_id }}</el-descriptions-item>
                    <el-descriptions-item label="SKU">{{ selectedProduct.sku || 'N/A' }}</el-descriptions-item>
                    <el-descriptions-item label="Price">${{ formatPrice(selectedProduct.price) }}</el-descriptions-item>
                    <el-descriptions-item label="Quantity">{{ selectedProduct.quantity }}</el-descriptions-item>
                    <el-descriptions-item label="Status">
                        <el-tag :type="selectedProduct.status === 'active' ? 'success' : 'danger'" effect="dark">
                            {{ selectedProduct.status || 'Active' }}
                        </el-tag>
                    </el-descriptions-item>
                    <el-descriptions-item label="Description">
                        {{ selectedProduct.description || 'No description available' }}
                    </el-descriptions-item>
                    <el-descriptions-item label="Supplier">
                        {{ selectedProduct.supplier ? selectedProduct.supplier.name : 'N/A' }}
                    </el-descriptions-item>
                </el-descriptions>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-between">
                <el-button @click="detailsVisible = false" style="margin-bottom: 10px;">Close</el-button>
                <div>
                    <el-button type="primary" @click="goToEditProduct(selectedProduct)">Edit</el-button>
                    <el-popconfirm title="Are you sure you want to delete this product?"
                        @confirm="deleteAndCloseDialog(selectedProduct.id)">
                        <template #reference>
                            <el-button type="danger">Delete</el-button>
                        </template>
                    </el-popconfirm>
                </div>
            </div>
        </template>
    </el-dialog>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { debounce } from 'lodash-es'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { ElMessageBox, ElNotification } from 'element-plus'
import { useCurrency } from '../../composables/useCurrency'
import {
    Search,
    Plus,
    Delete,
    Edit,
    Picture,
    Refresh,
    PictureFilled,
    View,
    ArrowDown,
    Check,
    CloseBold,
    Download,
    List,
    Grid,
    Goods
} from '@element-plus/icons-vue'

const router = useRouter()
const loading = ref(false)
const searchQuery = ref('')
const currentPage = ref(1)
const pageSize = ref(10)
const productData = ref({ data: [] })
const selectedProductIds = ref([])
const { formatCurrency } = useCurrency()

// Fetch products with debounce
const getProducts = debounce(async (page = 1) => {
    try {
        loading.value = true
        const response = await axios.get('/api/products', {
            params: {
                page,
                per_page: pageSize.value,
                query: searchQuery.value
            }
        })
        productData.value = response.data
    } catch (error) {
        console.error('Error fetching products:', error)
        ElNotification.error({
            title: 'Error',
            message: 'Failed to fetch products'
        })
    } finally {
        loading.value = false
    }
}, 300)

// Handle selection change
const handleSelectionChange = (selection) => {
    selectedProductIds.value = selection.map(item => item.id)
}

// Handle page change
const handlePageChange = (page) => {
    currentPage.value = page
    getProducts(page)
}

// Handle page size change
const handleSizeChange = (size) => {
    pageSize.value = size
    getProducts(currentPage.value)
}

// Handle search clear
const handleSearchClear = () => {
    searchQuery.value = ''
    getProducts(1)
}

// Navigation
const goToCreateProduct = () => {
    router.push('/product/create')
}

// Replace the formatPrice function with formatCurrency from the composable
const formatPrice = (price) => {  
  return formatCurrency(price);  
};  

const selectedProduct = ref(null);  

const detailsVisible = ref(false);  
const goToEditProduct = (id) => {
    router.push(`/product/edit/${id}`)
}
const viewProductDetails = (product) => {  
  selectedProduct.value = product;  
  detailsVisible.value = true;  
};  

// Delete confirmation
const confirmDeleteProduct = (id) => {
    ElMessageBox.confirm(
        'Are you sure you want to delete this product?',
        'Warning',
        {
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            type: 'warning',
        }
    ).then(() => {
        deleteProduct(id)
    }).catch(() => { })
}

const confirmBulkDelete = () => {
    if (selectedProductIds.value.length === 0) return

    ElMessageBox.confirm(
        `Are you sure you want to delete ${selectedProductIds.value.length} selected products?`,
        'Warning',
        {
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            type: 'warning',
        }
    ).then(() => {
        bulkDelete()
    }).catch(() => { })
}

// Delete operations
const deleteProduct = async (id) => {
    try {
        await axios.delete(`/api/products/${id}`)
        productData.value.data = productData.value.data.filter(
            product => product.id !== id
        )
        ElNotification.success({
            title: 'Success',
            message: 'Product deleted successfully'
        })
    } catch (error) {
        console.error('Error deleting product:', error)
        ElNotification.error({
            title: 'Error',
            message: 'Failed to delete product'
        })
    }
}

const bulkDelete = async () => {
    try {
        await axios.delete('/api/products', {
            data: { ids: selectedProductIds.value }
        })
        getProducts(currentPage.value)
        selectedProductIds.value = []
        ElNotification.success({
            title: 'Success',
            message: 'Selected products deleted successfully'
        })
    } catch (error) {
        console.error('Error bulk deleting products:', error)
        ElNotification.error({
            title: 'Error',
            message: 'Failed to delete selected products'
        })
    }
}

// Delete and close dialog
const deleteAndCloseDialog = async (id) => {
    try {
        await deleteProduct(id)
        detailsVisible.value = false
    } catch (error) {
        console.error('Error deleting product:', error)
    }
}

// Watchers and lifecycle hooks
watch(searchQuery, () => {
    currentPage.value = 1
    getProducts(1)
})

onMounted(() => {
    getProducts(1)
})
</script>

<style scoped>
.product-management-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.page-title {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 10px;
    align-items: center;
}

.table-card {
    border-radius: 8px;
    overflow: hidden;
}

.pagination-container {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
}

.product-link {
    color: var(--el-color-primary);
    text-decoration: none;
    transition: color 0.2s;
}

.product-link:hover {
    color: var(--el-color-primary-light-3);
    text-decoration: underline;
}

.image-error {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 50px;
    height: 50px;
    background-color: #f5f5f5;
    color: #999;
}

@media (max-width: 768px) {
    .header-container {
        flex-direction: column;
        align-items: flex-start;
    }

    .action-buttons {
        width: 100%;
        flex-wrap: wrap;
    }

    .el-input {
        width: 100% !important;
    }
}
</style>
