<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { ElMessageBox, ElNotification } from 'element-plus';
import {
    Search,
    Plus,
    Delete,
    Edit,
    View
} from '@element-plus/icons-vue';

const router = useRouter();
const loading = ref(false);
const searchQuery = ref('');
const currentPage = ref(1);
const pageSize = ref(10);
const customerData = ref({ 
    data: [],
    total: 0,
    current_page: 1,
    last_page: 1,
    per_page: 10
});
const selectedCustomer = ref(null);
const detailsVisible = ref(false);

// Fetch customers
const getCustomers = async (page = 1) => {
    try {
        loading.value = true;
        const response = await axios.get('/api/customers', {
            params: {
                page,
                per_page: pageSize.value,
                query: searchQuery.value
            }
        });
        customerData.value = response.data;
    } catch (error) {
        console.error('Error fetching customers:', error);
        ElNotification.error({
            title: 'Error',
            message: 'Failed to fetch customers'
        });
    } finally {
        loading.value = false;
    }
};

// Handle pagination
const handleCurrentChange = (page) => {
    currentPage.value = page;
    getCustomers(page);
};

const handleSizeChange = (size) => {
    pageSize.value = size;
    currentPage.value = 1;
    getCustomers(1);
};

// View customer details
const viewCustomerDetails = (customer) => {
    selectedCustomer.value = customer;
    detailsVisible.value = true;
};

// Delete customer
const confirmDeleteCustomer = (id) => {
    ElMessageBox.confirm(
        'Are you sure you want to delete this customer?',
        'Warning',
        {
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            type: 'warning',
        }
    ).then(() => {
        deleteCustomer(id);
    }).catch(() => {});
};

const deleteCustomer = async (id) => {
    try {
        await axios.delete(`/api/customers/${id}`);
        getCustomers(currentPage.value);
        ElNotification.success({
            title: 'Success',
            message: 'Customer deleted successfully'
        });
    } catch (error) {
        console.error('Error deleting customer:', error);
        ElNotification.error({
            title: 'Error',
            message: 'Failed to delete customer'
        });
    }
};

const deleteAndCloseDialog = async (id) => {
    await deleteCustomer(id);
    detailsVisible.value = false;
};

// Watch for search query changes
watch(searchQuery, () => {
    currentPage.value = 1;
    getCustomers(1);
});

onMounted(() => {
    getCustomers(1);
});
</script>

<template>
    <div class="customer-management-container p-4">
        <!-- Header with actions -->
        <div class="header-container mb-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold">Customer Management</h2>
            <div class="action-buttons flex gap-3">
                <el-input v-model="searchQuery" placeholder="Search customers..." clearable style="width: 300px"
                    @input="getCustomers">
                    <template #prefix>
                        <el-icon>
                            <Search />
                        </el-icon>
                    </template>
                </el-input>

                <el-button type="primary" @click="router.push('/customer/create')">
                    <el-icon class="mr-1">
                        <Plus />
                    </el-icon>
                    <span>Add Customer</span>
                </el-button>
            </div>
        </div>

        <!-- Customer Table -->
        <el-card shadow="hover" class="table-card">
            <el-table v-loading="loading" :data="customerData.data" style="width: 100%" border stripe>
                <el-table-column prop="id" label="ID" width="70" sortable />
                <el-table-column prop="name" label="Name" min-width="150" sortable>
                    <template #default="{ row }">
                        <router-link :to="`/customer/edit/${row.id}`" class="text-blue-600 hover:underline">
                            {{ row.name }}
                        </router-link>
                    </template>
                </el-table-column>
                <el-table-column prop="email" label="Email" min-width="180" sortable />
                <el-table-column prop="phone" label="Phone" min-width="150" sortable />
                <el-table-column prop="address" label="Address" min-width="200" sortable />
                <el-table-column label="Image" width="100">
                    <template #default="{ row }">
                        <el-image 
                            v-if="row.image_url" 
                            :src="row.image_url" 
                            fit="cover" 
                            style="width: 50px; height: 50px;"
                            :preview-src-list="[row.image_url]"
                        />
                        <span v-else>No image</span>
                    </template>
                </el-table-column>
                
                <el-table-column label="Actions" width="150" fixed="right">
                    <template #default="{ row }">
                        <el-tooltip content="Edit Customer" placement="top">
                            <el-button size="small" type="primary" @click="router.push(`/customer/edit/${row.id}`)" circle>
                                <el-icon>
                                    <Edit />
                                </el-icon>
                            </el-button>
                        </el-tooltip>

                        <el-tooltip content="View Details" placement="top">
                            <el-button type="info" size="small" circle @click="viewCustomerDetails(row)">
                                <el-icon>
                                    <View />
                                </el-icon>
                            </el-button>
                        </el-tooltip>

                        <el-tooltip content="Delete Customer" placement="top">
                            <el-button size="small" type="danger" @click="confirmDeleteCustomer(row.id)" circle>
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
                    :total="customerData.total || 0"
                    @size-change="handleSizeChange"
                    @current-change="handleCurrentChange"
                />
            </div>
        </el-card>

        <!-- Customer Details Dialog -->
        <el-dialog v-model="detailsVisible" title="Customer Details" width="600px">
            <div v-if="selectedCustomer" class="customer-details">
                <el-descriptions :column="1" border>
                    <el-descriptions-item label="ID">{{ selectedCustomer.id }}</el-descriptions-item>
                    <el-descriptions-item label="Name">{{ selectedCustomer.name }}</el-descriptions-item>
                    <el-descriptions-item label="Email">{{ selectedCustomer.email }}</el-descriptions-item>
                    <el-descriptions-item label="Phone">{{ selectedCustomer.phone }}</el-descriptions-item>
                    <el-descriptions-item label="Address">{{ selectedCustomer.address }}</el-descriptions-item>
                </el-descriptions>
                
                <div v-if="selectedCustomer.image_url" class="mt-4">
                    <h4 class="mb-2">Customer Image</h4>
                    <el-image 
                        :src="selectedCustomer.image_url" 
                        fit="cover" 
                        style="width: 150px; height: 150px;"
                        :preview-src-list="[selectedCustomer.image_url]"
                    />
                </div>
            </div>

            <template #footer>
                <div class="flex justify-between">
                    <el-button @click="detailsVisible = false">Close</el-button>
                    <div>
                        <el-button type="primary" @click="router.push(`/customer/edit/${selectedCustomer.id}`)">Edit</el-button>
                        <el-popconfirm title="Are you sure you want to delete this customer?"
                            @confirm="deleteAndCloseDialog(selectedCustomer.id)">
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

<style scoped>
.customer-management-container {
    max-width: 1400px;
    margin: 0 auto;
}

.table-card {
    margin-bottom: 20px;
}

.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.action-buttons {
    display: flex;
    gap: 10px;
}

.pagination-container {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
}
</style>
