<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import axios from 'axios'
import { Edit, Delete, Plus } from '@element-plus/icons-vue'

// Reactive data
const form = ref({ id: null, name: '' })
const categories = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const dialogTitle = ref('Add Category')
const formRules = ref({
    name: [{ required: true, message: 'Please input category name', trigger: 'blur' }]
})

// Fetch categories
const fetchCategories = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/categories')
        categories.value = response.data
    } catch (error) {
        ElMessage.error('Failed to fetch categories')
    } finally {
        loading.value = false
    }
}

// Handle form submission
const handleSubmit = async () => {
    try {
        if (form.value.id) {
            await axios.put(`/api/categories/${form.value.id}`, form.value)
            const index = categories.value.findIndex(cat => cat.id === form.value.id)
            categories.value[index] = form.value;
            ElMessage.success('Category updated successfully')
        } else {
            const response = await axios.post('/api/categories', form.value)
            categories.value.unshift(response.data)
            ElMessage.success('Category added successfully')
        }
        dialogVisible.value = false
        resetForm()
    } catch (error) {
        if (error.response?.data?.errors) {
            ElMessage.error(error.response.data.errors.name?.[0] || 'Validation error')
        } else {
            ElMessage.error('An error occurred')
        }
    }
}

// Open dialog for adding
const openAddDialog = () => {
    resetForm()
    dialogTitle.value = 'Add Category'
    dialogVisible.value = true
}

// Open dialog for editing
const openEditDialog = (category) => {
    form.value = { ...category }
    dialogTitle.value = 'Edit Category'
    dialogVisible.value = true
}

// Delete category
const deleteCategory = async (id) => {
    try {
        await ElMessageBox.confirm(
            'Are you sure to delete this category?',
            'Warning',
            {
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel',
                type: 'warning',
            }
        )
        await axios.delete(`/api/categories/${id}`)
        categories.value = categories.value.filter(cat => cat.id !== id)
        ElMessage.success('Category deleted successfully')
    } catch (error) {
        if (error !== 'cancel') {
            ElMessage.error('Failed to delete category')
        }
    }
}

// Reset form
const resetForm = () => {
    form.value = { id: null, name: '' }
}

// Fetch categories on mount
onMounted(() => {
    fetchCategories()
})
</script>

<template>
    <div class="category-management p-6">
        <el-card class="box-card">
            <template #header>
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold">Category Management</h2>
                    <el-button type="primary" @click="openAddDialog">
                        <el-icon class="mr-1">
                            <plus />
                        </el-icon>
                        Add Category
                    </el-button>
                </div>
            </template>

            <el-table :data="categories" v-loading="loading" style="width: 100%">
                <el-table-column prop="id" label="ID" width="80" />
                <el-table-column prop="name" label="Name" />
                <el-table-column label="Actions" width="150">
                    <template #default="scope">
                        <el-tooltip content="Edit Product" placement="top">
                            <el-button size="small" type="primary" @click="openEditDialog(scope.row)" circle>
                                <el-icon>
                                    <Edit />
                                </el-icon>
                            </el-button>
                        </el-tooltip>
                        <el-tooltip content="Delete Product" placement="top">
                            <el-button size="small" type="danger" @click="deleteCategory(scope.row.id)" circle>
                                <el-icon>
                                    <Delete />
                                </el-icon>
                            </el-button>
                        </el-tooltip>
                    </template>
                </el-table-column>
            </el-table>
        </el-card>

        <!-- Add/Edit Dialog -->
        <el-dialog v-model="dialogVisible" :title="dialogTitle" width="30%">
            <el-form :model="form" :rules="formRules" label-width="120px">
                <el-form-item label="Category Name" prop="name">
                    <el-input v-model="form.name" placeholder="Enter category name" />
                </el-form-item>
            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="dialogVisible = false">Cancel</el-button>
                    <el-button type="primary" @click="handleSubmit">
                        {{ form.id ? 'Update' : 'Create' }}
                    </el-button>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<style scoped>
.category-management {
    max-width: 1200px;
    margin: 0 auto;
}

.box-card {
    margin-bottom: 20px;
}

.el-table {
    margin-top: 20px;
}

.dialog-footer button:first-child {
    margin-right: 10px;
}
</style>