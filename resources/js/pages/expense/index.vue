<template>
  <div class="expense-management-container p-4">
    <!-- Header with actions -->
    <div class="header-container mb-4 flex justify-between items-center">
      <h2 class="text-xl font-semibold">Expense Management</h2>
      <div class="action-buttons flex gap-3">
        <el-input v-model="searchQuery" placeholder="Search expenses..." clearable style="width: 300px"
          @input="getExpenses">
          <template #prefix>
            <el-icon>
              <Search />
            </el-icon>
          </template>
        </el-input>

        <el-dropdown>
          <el-button type="primary">
            <el-icon class="mr-1">
              <Setting />
            </el-icon>
            <span>Settings</span>
            <el-icon class="el-icon--right">
              <arrow-down />
            </el-icon>
          </el-button>
          <template #dropdown>
            <el-dropdown-menu>
              <el-dropdown-item @click="navigateToCategories">
                <el-icon><Folder /></el-icon>
                <span>Manage Categories</span>
              </el-dropdown-item>
              <el-dropdown-item @click="navigateToMethods">
                <el-icon><CreditCard /></el-icon>
                <span>Manage Payment Methods</span>
              </el-dropdown-item>
            </el-dropdown-menu>
          </template>
        </el-dropdown>

        <el-button type="primary" @click="navigateToCreate">
          <el-icon class="mr-1">
            <Plus />
          </el-icon>
          <span>Add Expense</span>
        </el-button>
      </div>
    </div>

    <!-- Stats cards -->
    <el-row :gutter="20" class="mb-4">
      <el-col :span="8">
        <el-card shadow="hover" class="stats-card">
          <div class="flex items-center">
            <el-icon class="text-red-500 text-2xl mr-3">
              <Money />
            </el-icon>
            <div>
              <div class="text-gray-500 text-sm">Today's Expense</div>
              <div class="text-xl font-bold">{{ formatCurrencyValue(todayExpense) }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card shadow="hover" class="stats-card">
          <div class="flex items-center">
            <el-icon class="text-orange-500 text-2xl mr-3">
              <Calendar />
            </el-icon>
            <div>
              <div class="text-gray-500 text-sm">Yesterday's Expense</div>
              <div class="text-xl font-bold">{{ formatCurrencyValue(yesterdayExpense) }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card shadow="hover" class="stats-card">
          <div class="flex items-center">
            <el-icon class="text-blue-500 text-2xl mr-3">
              <TrendCharts />
            </el-icon>
            <div>
              <div class="text-gray-500 text-sm">This Month's Expense</div>
              <div class="text-xl font-bold">{{ formatCurrencyValue(monthlyExpense) }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- Expense Table -->
    <el-card shadow="hover">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium">Expense List</h3>
        <el-date-picker v-model="dateFilter" type="daterange" range-separator="To" start-placeholder="Start date"
          end-placeholder="End date" @change="handleDateChange" />
      </div>

      <el-table v-loading="loading" :data="expenses" style="width: 100%" border>
        <el-table-column label="ID" prop="expense_id" width="70" />
        <el-table-column label="Description" prop="description" min-width="180" />
        <el-table-column label="Amount" min-width="100">
          <template #default="scope">
            {{ formatCurrencyValue(scope.row.amount) }}
          </template>
        </el-table-column>
        <el-table-column label="Date" prop="expense_date" min-width="100">
          <template #default="scope">
            {{ formatDate(scope.row.expense_date) }}
          </template>
        </el-table-column>
        <el-table-column label="Category" min-width="120">
          <template #default="scope">
            {{ scope.row.category ? scope.row.category.name : 'N/A' }}
          </template>
        </el-table-column>
        <el-table-column label="Payment Method" min-width="140">
          <template #default="scope">
            {{ scope.row.method ? scope.row.method.name : 'N/A' }}
          </template>
        </el-table-column>
        <el-table-column label="User" min-width="120">
          <template #default="scope">
            {{ scope.row.user ? scope.row.user.name : 'N/A' }}
          </template>
        </el-table-column>
        <el-table-column label="Created At" min-width="160">
          <template #default="scope">
            {{ formatDateTime(scope.row.created_at) }}
          </template>
        </el-table-column>
        <el-table-column label="Updated At" min-width="160">
          <template #default="scope">
            {{ formatDateTime(scope.row.updated_at) }}
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="150" fixed="right">
          <template #default="scope">
            <el-button-group>
              <el-button size="small" type="primary" @click="editExpense(scope.row.expense_id)">
                <el-icon>
                  <Edit />
                </el-icon>
              </el-button>
              <el-button size="small" type="danger" @click="confirmDelete(scope.row.expense_id)">
                <el-icon>
                  <Delete />
                </el-icon>
              </el-button>
            </el-button-group>
          </template>
        </el-table-column>
      </el-table>

      <!-- Pagination -->
      <div class="flex justify-end mt-4">
        <el-pagination background layout="prev, pager, next" :total="totalExpenses" :page-size="perPage"
          @current-change="handlePageChange" />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { ElMessageBox, ElMessage } from 'element-plus';
import { Search, Plus, Edit, Delete, Money, Calendar, TrendCharts, Setting, Folder, CreditCard, ArrowDown } from '@element-plus/icons-vue';
import { useToastr } from '../../Helper/toaster';
import { useCurrency } from '../../composables/useCurrency';

const router = useRouter();
const toastr = useToastr();
const { formatCurrency } = useCurrency();

// Data
const expenses = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const dateFilter = ref(null);
const currentPage = ref(1);
const perPage = ref(10);
const totalExpenses = ref(0);
const todayExpense = ref(0);
const yesterdayExpense = ref(0);
const monthlyExpense = ref(0);

// Add a local formatCurrencyValue function as a fallback
const formatCurrencyValue = (value) => {
  // Try to use the global $formatCurrency if available, otherwise use the local formatCurrency
  try {
    return formatCurrency(value);
  } catch (error) {
    console.warn('Using fallback currency formatter');
    return formatCurrency(value);
  }
};

// Navigation methods
const navigateToCreate = () => {
  router.push('/expense/create');
};

const navigateToCategories = () => {
  router.push('/expense-category');
};

const navigateToMethods = () => {
  router.push('/expense-method');
};

const editExpense = (id) => {
  router.push(`/expense/edit/${id}`);
};

// Methods
const getExpenses = async () => {
  try {
    loading.value = true;
    
    let params = {
      page: currentPage.value,
      per_page: perPage.value,
      search: searchQuery.value
    };
    
    if (dateFilter.value && dateFilter.value[0] && dateFilter.value[1]) {
      params.start_date = dateFilter.value[0].toISOString().split('T')[0];
      params.end_date = dateFilter.value[1].toISOString().split('T')[0];
    }
    
    const response = await axios.get('/api/expenses', { params });
    expenses.value = response.data.data;
    totalExpenses.value = response.data.total;
  } catch (error) {
    console.error('Error fetching expenses:', error);
    toastr.error('Failed to load expenses');
  } finally {
    loading.value = false;
  }
};

const fetchExpenseStats = async () => {
  try {
    const [todayRes, yesterdayRes, monthlyRes] = await Promise.all([
      axios.get('/api/today/expense'),
      axios.get('/api/yesterday/expense'),
      axios.get('/api/monthly/expense')
    ]);
    
    todayExpense.value = todayRes.data || 0;
    yesterdayExpense.value = yesterdayRes.data || 0;
    monthlyExpense.value = monthlyRes.data || 0;
  } catch (error) {
    console.error('Error fetching expense stats:', error);
  }
};

const handlePageChange = (page) => {
  currentPage.value = page;
  getExpenses();
};

const handleDateChange = () => {
  getExpenses();
};

const confirmDelete = (id) => {
  ElMessageBox.confirm(
    'Are you sure you want to delete this expense?',
    'Warning',
    {
      confirmButtonText: 'Delete',
      cancelButtonText: 'Cancel',
      type: 'warning',
    }
  )
    .then(() => {
      deleteExpense(id);
    })
    .catch(() => {
      // User canceled
    });
};

const deleteExpense = async (id) => {
  try {
    await axios.delete(`/api/expenses/${id}`);
    toastr.success('Expense deleted successfully');
    getExpenses();
    fetchExpenseStats(); // Refresh stats after deletion
  } catch (error) {
    console.error('Error deleting expense:', error);
    toastr.error('Failed to delete expense');
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString();
};

const formatDateTime = (dateTimeString) => {
  if (!dateTimeString) return 'N/A';
  const date = new Date(dateTimeString);
  return date.toLocaleString();
};

// Lifecycle hooks
onMounted(() => {
  getExpenses();
  fetchExpenseStats();
});
</script>

<style scoped>
.expense-management-container {
  background-color: #f5f7fa;
  min-height: calc(100vh - 64px);
}

.stats-card {
  height: 100px;
  display: flex;
  align-items: center;
}

.el-table {
  --el-table-border-color: #ebeef5;
  --el-table-header-background-color: #f5f7fa;
}
.action-buttons {
	display: flex;
}
</style>
