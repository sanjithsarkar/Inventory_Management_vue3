<template>
    <div class="supplier-management-container p-4">
        <!-- Header with actions -->
        <div class="header-container mb-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold">Supplier Management</h2>
            <div class="action-buttons flex gap-3">
                <el-input v-model="searchQuery" placeholder="Search suppliers..." clearable style="width: 300px"
                    @input="getSuppliers">
                    <template #prefix>
                        <el-icon>
                            <Search />
                        </el-icon>
                    </template>
                </el-input>

                <el-button type="primary" @click="router.push('/supplier/create')">
                    <el-icon class="mr-1">
                        <Plus />
                    </el-icon>
                    <span>Add Supplier</span>
                </el-button>
            </div>
        </div>

        <!-- Supplier Table -->
        <el-card shadow="hover" class="table-card">
            <el-table v-loading="loading" :data="supplierData.data" style="width: 100%" border stripe>
                <el-table-column prop="id" label="ID" width="70" sortable />
                <el-table-column prop="name" label="Name" min-width="150" sortable>
                    <template #default="{ row }">
                        <router-link :to="`/supplier/edit/${row.id}`" class="text-blue-600 hover:underline">
                            {{ row.name }}
                        </router-link>
                    </template>
                </el-table-column>
                <el-table-column prop="email" label="Email" min-width="180" sortable />
                <el-table-column prop="phone" label="Phone" min-width="150" sortable />
                <el-table-column prop="shopname" label="Shop Name" min-width="150" sortable />
                <el-table-column prop="address" label="Address" min-width="200" sortable />
                
                <el-table-column label="Actions" width="150" fixed="right">
                    <template #default="{ row }">
                        <el-tooltip content="Edit Supplier" placement="top">
                            <el-button size="small" type="primary" @click="router.push(`/supplier/edit/${row.id}`)" circle>
                                <el-icon>
                                    <Edit />
                                </el-icon>
                            </el-button>
                        </el-tooltip>

                        <el-tooltip content="View Details" placement="top">
                            <el-button type="info" size="small" circle @click="viewSupplierDetails(row)">
                                <el-icon>
                                    <View />
                                </el-icon>
                            </el-button>
                        </el-tooltip>

                        <el-tooltip content="Delete Supplier" placement="top">
                            <el-button size="small" type="danger" @click="confirmDeleteSupplier(row.id)" circle>
                                <el-icon>
                                    <Delete />
                                </el-icon>
                            </el-button>
                        </el-tooltip>
                    </template>
                </el-table-column>
            </el-table>

            <!-- Pagination -->
            <div class="pagination-container mt-4 flex justify-end">
                <el-pagination 
                    v-model:current-page="currentPage"
                    v-model:page-size="pageSize"
                    :page-sizes="[10, 20, 50, 100]"
                    layout="total, sizes, prev, pager, next"
                    :total="supplierData.total || 0"
                    @size-change="handleSizeChange"
                    @current-change="handleCurrentChange"
                />
            </div>
        </el-card>

        <!-- Supplier Details Dialog -->
        <el-dialog v-model="detailsVisible" title="Supplier Details" width="600px">
            <div v-if="selectedSupplier" class="supplier-details">
                <el-descriptions :column="1" border>
                    <el-descriptions-item label="ID">{{ selectedSupplier.id }}</el-descriptions-item>
                    <el-descriptions-item label="Name">{{ selectedSupplier.name }}</el-descriptions-item>
                    <el-descriptions-item label="Email">{{ selectedSupplier.email }}</el-descriptions-item>
                    <el-descriptions-item label="Phone">{{ selectedSupplier.phone }}</el-descriptions-item>
                    <el-descriptions-item label="Shop Name">{{ selectedSupplier.shopname || 'N/A' }}</el-descriptions-item>
                    <el-descriptions-item label="Address">{{ selectedSupplier.address }}</el-descriptions-item>
                </el-descriptions>
            </div>

            <template #footer>
                <div class="flex justify-between">
                    <el-button @click="detailsVisible = false">Close</el-button>
                    <div>
                        <el-button type="primary" @click="router.push(`/supplier/edit/${selectedSupplier.id}`)">Edit</el-button>
                        <el-popconfirm title="Are you sure you want to delete this supplier?"
                            @confirm="deleteAndCloseDialog(selectedSupplier.id)">
                            <template #reference>
                                <el-button type="danger">Delete</el-button>
                            </template>
                        </el-popconfirm>
                    </div>
                </div>
            </template>
        </el-dialog>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { ElMessageBox, ElNotification } from 'element-plus'
import {
    Search,
    Plus,
    Delete,
    Edit,
    View
} from '@element-plus/icons-vue'

const router = useRouter()
const loading = ref(false)
const searchQuery = ref('')
const currentPage = ref(1)
const pageSize = ref(10)
const supplierData = ref({ 
    data: [],
    total: 0,
    current_page: 1,
    last_page: 1,
    per_page: 10
})
const selectedSupplier = ref(null)
const detailsVisible = ref(false)

// Fetch suppliers
const getSuppliers = async (page = 1) => {
    try {
        loading.value = true
        const response = await axios.get('/api/suppliers', {
            params: {
                page,
                per_page: pageSize.value,
                query: searchQuery.value
            }
        })
        supplierData.value = response.data
    } catch (error) {
        console.error('Error fetching suppliers:', error)
        ElNotification.error({
            title: 'Error',
            message: 'Failed to fetch suppliers'
        })
    } finally {
        loading.value = false
    }
}

// Handle pagination
const handleCurrentChange = (page) => {
    currentPage.value = page
    getSuppliers(page)
}

const handleSizeChange = (size) => {
    pageSize.value = size
    currentPage.value = 1
    getSuppliers(1)
}

// View supplier details
const viewSupplierDetails = (supplier) => {
    selectedSupplier.value = supplier
    detailsVisible.value = true
}

// Delete supplier
const confirmDeleteSupplier = (id) => {
    ElMessageBox.confirm(
        'Are you sure you want to delete this supplier?',
        'Warning',
        {
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            type: 'warning',
        }
    ).then(() => {
        deleteSupplier(id)
    }).catch(() => { })
}

const deleteSupplier = async (id) => {
    try {
        await axios.delete(`/api/suppliers/${id}`)
        getSuppliers(currentPage.value)
        ElNotification.success({
            title: 'Success',
            message: 'Supplier deleted successfully'
        })
    } catch (error) {
        console.error('Error deleting supplier:', error)
        ElNotification.error({
            title: 'Error',
            message: 'Failed to delete supplier'
        })
    }
}

const deleteAndCloseDialog = async (id) => {
    await deleteSupplier(id)
    detailsVisible.value = false
}

// Watch for search query changes
watch(searchQuery, () => {
    currentPage.value = 1
    getSuppliers(1)
})

onMounted(() => {
    getSuppliers(1)
})
</script>

<style scoped>
.supplier-management-container {
    max-width: 1400px;
    margin: 0 auto;
}

.table-card {
    margin-bottom: 20px;
}

.pagination-container {
    margin-top: 20px;
}
</style>
