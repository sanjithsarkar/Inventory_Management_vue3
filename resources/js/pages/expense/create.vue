<template>
  <div class="expense-create-container p-4">
    <el-card shadow="hover" class="max-w-3xl mx-auto">
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-medium">Add New Expense</h3>
          <el-button @click="router.push('/expense')" plain>
            <el-icon class="mr-1">
              <Back />
            </el-icon>
            Back to List
          </el-button>
        </div>
      </template>

      <el-form 
        ref="formRef" 
        :model="form" 
        :rules="rules" 
        label-position="top" 
        @submit.prevent="submitForm"
        v-loading="submitting"
      >
        <el-form-item label="Expense Details" prop="details">
          <el-input 
            v-model="form.details" 
            placeholder="Enter expense details"
            type="textarea"
            :rows="3"
          />
          <div v-if="errors.details" class="text-red-500 text-sm mt-1">{{ errors.details[0] }}</div>
        </el-form-item>

        <el-form-item label="Category" prop="category_id">
          <el-select 
            v-model="form.category_id" 
            placeholder="Select category"
            style="width: 100%"
          >
            <el-option 
              v-for="category in categories" 
              :key="category.category_id" 
              :label="category.name" 
              :value="category.category_id" 
            />
          </el-select>
          <div v-if="errors.category_id" class="text-red-500 text-sm mt-1">{{ errors.category_id[0] }}</div>
        </el-form-item>

        <el-form-item label="Payment Method" prop="method_id">
          <el-select 
            v-model="form.method_id" 
            placeholder="Select payment method"
            style="width: 100%"
          >
            <el-option 
              v-for="method in methods" 
              :key="method.method_id" 
              :label="method.name" 
              :value="method.method_id" 
            />
          </el-select>
          <div v-if="errors.method_id" class="text-red-500 text-sm mt-1">{{ errors.method_id[0] }}</div>
        </el-form-item>

        <el-form-item label="Amount" prop="amount">
          <el-input-number 
            v-model="form.amount" 
            :precision="2" 
            :step="0.01" 
            :min="0"
            style="width: 100%"
            placeholder="Enter expense amount"
          />
          <div v-if="errors.amount" class="text-red-500 text-sm mt-1">{{ errors.amount[0] }}</div>
        </el-form-item>

        <el-form-item label="Date" prop="date">
          <el-date-picker
            v-model="form.date"
            type="date"
            placeholder="Select date"
            format="DD/MM/YYYY"
            value-format="DD/MM/YYYY"
            style="width: 100%"
          />
          <div v-if="errors.date" class="text-red-500 text-sm mt-1">{{ errors.date[0] }}</div>
        </el-form-item>

        <el-form-item label="Location (Optional)" prop="location">
          <el-input 
            v-model="form.location" 
            placeholder="Enter location"
          />
        </el-form-item>

        <el-form-item label="Recurring Expense" prop="is_recurring">
          <el-switch v-model="form.is_recurring" />
        </el-form-item>

        <el-form-item v-if="form.is_recurring" label="Recurrence Pattern" prop="recurrence_pattern">
          <el-select 
            v-model="form.recurrence_pattern" 
            placeholder="Select recurrence pattern"
            style="width: 100%"
          >
            <el-option label="Daily" value="daily" />
            <el-option label="Weekly" value="weekly" />
            <el-option label="Monthly" value="monthly" />
            <el-option label="Yearly" value="yearly" />
          </el-select>
        </el-form-item>

        <el-form-item>
          <el-button type="primary" native-type="submit" :loading="submitting" class="w-full">
            Create Expense
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { Back } from '@element-plus/icons-vue';
import { useToastr } from '../../Helper/toaster';

const router = useRouter();
const toastr = useToastr();
const formRef = ref(null);
const submitting = ref(false);
const errors = ref({});
const categories = ref([]);
const methods = ref([]);

// Form data
const form = reactive({
  details: '',
  category_id: null,
  method_id: null,
  amount: null,
  date: formatDate(new Date()), // Default to today
  location: '',
  is_recurring: false,
  recurrence_pattern: null
});

// Form validation rules
const rules = {
  details: [
    { required: true, message: 'Please enter expense details', trigger: 'blur' },
    { min: 3, message: 'Details must be at least 3 characters', trigger: 'blur' }
  ],
  category_id: [
    { required: true, message: 'Please select a category', trigger: 'change' }
  ],
  method_id: [
    { required: true, message: 'Please select a payment method', trigger: 'change' }
  ],
  amount: [
    { required: true, message: 'Please enter expense amount', trigger: 'blur' },
    { type: 'number', min: 0.01, message: 'Amount must be greater than 0', trigger: 'blur' }
  ],
  date: [
    { required: true, message: 'Please select a date', trigger: 'change' }
  ],
  recurrence_pattern: [
    { required: true, message: 'Please select a recurrence pattern', trigger: 'change', 
      validator: (rule, value, callback) => {
        if (form.is_recurring && !value) {
          callback(new Error('Please select a recurrence pattern'));
        } else {
          callback();
        }
      }
    }
  ]
};

// Format date as DD/MM/YYYY
function formatDate(date) {
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}

// Fetch categories and methods
const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/expense-categories');
    categories.value = response.data;
  } catch (error) {
    console.error('Error fetching categories:', error);
    toastr.error('Failed to load categories');
  }
};

const fetchMethods = async () => {
  try {
    const response = await axios.get('/api/expense-methods');
    methods.value = response.data;
  } catch (error) {
    console.error('Error fetching payment methods:', error);
    toastr.error('Failed to load payment methods');
  }
};

// Submit form
const submitForm = async () => {
  if (!formRef.value) return;
  
  await formRef.value.validate(async (valid) => {
    if (!valid) return;
    
    try {
      submitting.value = true;
      errors.value = {};
      
      // Get token from localStorage
      const token = localStorage.getItem('token');
      
      // Set authorization header
      const config = {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      };
      
      const response = await axios.post('/api/expenses', form, config);
      
      toastr.success('Expense created successfully');
      router.push('/expense');
    } catch (error) {
      console.error('Error creating expense:', error);
      
      if (error.response?.status === 401) {
        toastr.error('You are not authenticated. Please log in again.');
        router.push('/');
      } else if (error.response?.data?.errors) {
        errors.value = error.response.data.errors;
        toastr.error('Please correct the errors in the form');
      } else {
        toastr.error('Failed to create expense');
      }
    } finally {
      submitting.value = false;
    }
  });
};

// Lifecycle hooks
onMounted(() => {
  fetchCategories();
  fetchMethods();
});
</script>

<style scoped>
.expense-create-container {
  background-color: #f5f7fa;
  min-height: calc(100vh - 64px);
}
</style>
