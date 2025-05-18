<template>
  <div class="expense-category-container p-4">
    <el-card shadow="hover" class="max-w-4xl mx-auto">
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-medium">Expense Categories</h3>
          <div class="flex gap-2">
            <el-button @click="router.push('/expense')" plain>
              <el-icon class="mr-1">
                <Back />
              </el-icon>
              Back to Expenses
            </el-button>
            <el-button type="primary" @click="openDialog()">
              <el-icon class="mr-1">
                <Plus />
              </el-icon>
              Add Category
            </el-button>
          </div>
        </div>
      </template>

      <el-table v-loading="loading" :data="categories" style="width: 100%" border>
        <el-table-column label="ID" prop="category_id" width="80" />
        <el-table-column label="Name" prop="name" min-width="150" />
        <el-table-column label="Description" prop="description" min-width="250" />
        <el-table-column label="Created At" min-width="180">
          <template #default="scope">
            {{ new Date(scope.row.created_at).toLocaleString() }}
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="150" align="center">
          <template #default="scope">
            <el-button-group>
              <el-button type="primary" size="small" @click="openDialog(scope.row)">
                <el-icon>
                  <Edit />
                </el-icon>
              </el-button>
              <el-button type="danger" size="small" @click="confirmDelete(scope.row.category_id)">
                <el-icon>
                  <Delete />
                </el-icon>
              </el-button>
            </el-button-group>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- Add/Edit Dialog -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEditing ? 'Edit Category' : 'Add Category'"
      width="500px"
      destroy-on-close
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-position="top"
        @submit.prevent="submitForm"
      >
        <el-form-item label="Category Name" prop="name">
          <el-input v-model="form.name" placeholder="Enter category name" />
          <div v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</div>
        </el-form-item>

        <el-form-item label="Description" prop="description">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="3"
            placeholder="Enter category description"
          />
          <div v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description[0] }}</div>
        </el-form-item>

        <el-form-item>
          <div class="flex justify-end gap-2">
            <el-button @click="dialogVisible = false">Cancel</el-button>
            <el-button type="primary" native-type="submit" :loading="submitting">
              {{ isEditing ? 'Update' : 'Create' }}
            </el-button>
          </div>
        </el-form-item>
      </el-form>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { ElMessageBox } from 'element-plus';
import { Back, Plus, Edit, Delete } from '@element-plus/icons-vue';
import { useToastr } from '../../Helper/toaster';

const router = useRouter();
const toastr = useToastr();
const loading = ref(false);
const submitting = ref(false);
const dialogVisible = ref(false);
const categories = ref([]);
const errors = ref({});
const formRef = ref(null);

// Form data
const form = reactive({
  category_id: null,
  name: '',
  description: ''
});

// Form validation rules
const rules = {
  name: [
    { required: true, message: 'Please enter category name', trigger: 'blur' },
    { min: 2, message: 'Name must be at least 2 characters', trigger: 'blur' }
  ]
};

// Computed properties
const isEditing = computed(() => !!form.category_id);

// Methods
const fetchCategories = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/expense-categories');
    categories.value = response.data;
  } catch (error) {
    console.error('Error fetching categories:', error);
    toastr.error('Failed to load categories');
  } finally {
    loading.value = false;
  }
};

const openDialog = (category = null) => {
  resetForm();
  
  if (category) {
    form.category_id = category.category_id;
    form.name = category.name;
    form.description = category.description || '';
  }
  
  dialogVisible.value = true;
};

const resetForm = () => {
  form.category_id = null;
  form.name = '';
  form.description = '';
  errors.value = {};
  
  if (formRef.value) {
    formRef.value.resetFields();
  }
};

const submitForm = async () => {
  if (!formRef.value) return;
  
  await formRef.value.validate(async (valid) => {
    if (!valid) return;
    
    try {
      submitting.value = true;
      errors.value = {};
      
      if (isEditing.value) {
        await axios.put(`/api/expense-categories/${form.category_id}`, form);
        toastr.success('Category updated successfully');
      } else {
        await axios.post('/api/expense-categories', form);
        toastr.success('Category created successfully');
      }
      
      dialogVisible.value = false;
      fetchCategories();
    } catch (error) {
      console.error('Error saving category:', error);
      
      if (error.response?.data?.errors) {
        errors.value = error.response.data.errors;
        toastr.error('Please correct the errors in the form');
      } else {
        toastr.error('Failed to save category');
      }
    } finally {
      submitting.value = false;
    }
  });
};

const confirmDelete = (id) => {
  ElMessageBox.confirm(
    'Are you sure you want to delete this category?',
    'Warning',
    {
      confirmButtonText: 'Delete',
      cancelButtonText: 'Cancel',
      type: 'warning',
    }
  )
    .then(() => {
      deleteCategory(id);
    })
    .catch(() => {
      // User canceled
    });
};

const deleteCategory = async (id) => {
  try {
    await axios.delete(`/api/expense-categories/${id}`);
    toastr.success('Category deleted successfully');
    fetchCategories();
  } catch (error) {
    console.error('Error deleting category:', error);
    toastr.error('Failed to delete category');
  }
};

// Lifecycle hooks
onMounted(() => {
  fetchCategories();
});
</script>

<style scoped>
.expense-category-container {
  background-color: #f5f7fa;
  min-height: calc(100vh - 64px);
}
</style>