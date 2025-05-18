<template>
  <div class="expense-method-container p-4">
    <el-card shadow="hover" class="max-w-4xl mx-auto">
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-medium">Expense Payment Methods</h3>
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
              Add Method
            </el-button>
          </div>
        </div>
      </template>

      <el-table v-loading="loading" :data="methods" style="width: 100%" border>
        <el-table-column label="ID" prop="method_id" width="80" />
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
              <el-button type="danger" size="small" @click="confirmDelete(scope.row.method_id)">
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
      :title="isEditing ? 'Edit Payment Method' : 'Add Payment Method'"
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
        <el-form-item label="Method Name" prop="name">
          <el-input v-model="form.name" placeholder="Enter method name" />
          <div v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</div>
        </el-form-item>

        <el-form-item label="Description" prop="description">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="3"
            placeholder="Enter method description"
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
const methods = ref([]);
const errors = ref({});
const formRef = ref(null);

// Form data
const form = reactive({
  method_id: null,
  name: '',
  description: ''
});

// Form validation rules
const rules = {
  name: [
    { required: true, message: 'Please enter method name', trigger: 'blur' },
    { min: 2, message: 'Name must be at least 2 characters', trigger: 'blur' }
  ]
};

// Computed properties
const isEditing = computed(() => !!form.method_id);

// Methods
const fetchMethods = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/expense-methods');
    methods.value = response.data;
  } catch (error) {
    console.error('Error fetching methods:', error);
    toastr.error('Failed to load payment methods');
  } finally {
    loading.value = false;
  }
};

const openDialog = (method = null) => {
  resetForm();
  
  if (method) {
    form.method_id = method.method_id;
    form.name = method.name;
    form.description = method.description || '';
  }
  
  dialogVisible.value = true;
};

const resetForm = () => {
  form.method_id = null;
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
        await axios.put(`/api/expense-methods/${form.method_id}`, form);
        toastr.success('Payment method updated successfully');
      } else {
        await axios.post('/api/expense-methods', form);
        toastr.success('Payment method created successfully');
      }
      
      dialogVisible.value = false;
      fetchMethods();
    } catch (error) {
      console.error('Error saving payment method:', error);
      
      if (error.response?.data?.errors) {
        errors.value = error.response.data.errors;
        toastr.error('Please correct the errors in the form');
      } else {
        toastr.error('Failed to save payment method');
      }
    } finally {
      submitting.value = false;
    }
  });
};

const confirmDelete = (id) => {
  ElMessageBox.confirm(
    'Are you sure you want to delete this payment method?',
    'Warning',
    {
      confirmButtonText: 'Delete',
      cancelButtonText: 'Cancel',
      type: 'warning',
    }
  )
    .then(() => {
      deleteMethod(id);
    })
    .catch(() => {
      // User canceled
    });
};

const deleteMethod = async (id) => {
  try {
    await axios.delete(`/api/expense-methods/${id}`);
    toastr.success('Payment method deleted successfully');
    fetchMethods();
  } catch (error) {
    console.error('Error deleting payment method:', error);
    toastr.error('Failed to delete payment method');
  }
};

// Lifecycle hooks
onMounted(() => {
  fetchMethods();
});
</script>

<style scoped>
.expense-method-container {
  background-color: #f5f7fa;
  min-height: calc(100vh - 64px);
}
</style>