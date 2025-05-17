
<template>
  <div class="dashboard-container">
    <h2>Today's Financial Summary</h2>
    <p class="date-display">{{ currentDate }}</p>
    
    <el-row :gutter="20">
      <!-- Today's Sale Amount Card -->
      <el-col :xs="24" :sm="12" :md="6" :lg="6">
        <el-card class="metric-card sale-card" shadow="hover">
          <div class="card-content">
            <div class="metric-icon">
              <el-icon><ShoppingCart /></el-icon>
            </div>
            <div class="metric-info">
              <h3>Today's Sales</h3>
              <p class="metric-value">${{ formatNumber(todaySale) }}</p>
              <p class="metric-change" :class="saleChange >= 0 ? 'positive' : 'negative'">
                <el-icon :class="saleChange >= 0 ? 'arrow-up' : 'arrow-down'">
                  <CaretTop v-if="saleChange >= 0" />
                  <CaretBottom v-else />
                </el-icon>
                {{ Math.abs(saleChange) }}% vs yesterday
              </p>
            </div>
          </div>
        </el-card>
      </el-col>

      <!-- Today's Income Card -->
      <el-col :xs="24" :sm="12" :md="6" :lg="6">
        <el-card class="metric-card income-card" shadow="hover">
          <div class="card-content">
            <div class="metric-icon">
              <el-icon><Money /></el-icon>
            </div>
            <div class="metric-info">
              <h3>Today's Income</h3>
              <p class="metric-value">${{ formatNumber(todayIncome) }}</p>
              <p class="metric-change" :class="incomeChange >= 0 ? 'positive' : 'negative'">
                <el-icon :class="incomeChange >= 0 ? 'arrow-up' : 'arrow-down'">
                  <CaretTop v-if="incomeChange >= 0" />
                  <CaretBottom v-else />
                </el-icon>
                {{ Math.abs(incomeChange) }}% vs yesterday
              </p>
            </div>
          </div>
        </el-card>
      </el-col>

      <!-- Today's Due Card -->
      <el-col :xs="24" :sm="12" :md="6" :lg="6">
        <el-card class="metric-card due-card" shadow="hover">
          <div class="card-content">
            <div class="metric-icon">
              <el-icon><CreditCard /></el-icon>
            </div>
            <div class="metric-info">
              <h3>Today's Due</h3>
              <p class="metric-value">${{ formatNumber(todayDue) }}</p>
              <p class="metric-change" :class="dueChange >= 0 ? 'positive' : 'negative'">
                <el-icon :class="dueChange >= 0 ? 'arrow-up' : 'arrow-down'">
                  <CaretTop v-if="dueChange >= 0" />
                  <CaretBottom v-else />
                </el-icon>
                {{ Math.abs(dueChange) }}% vs yesterday
              </p>
            </div>
          </div>
        </el-card>
      </el-col>

      <!-- Today's Expense Card -->
      <el-col :xs="24" :sm="12" :md="6" :lg="6">
        <el-card class="metric-card expense-card" shadow="hover">
          <div class="card-content">
            <div class="metric-icon">
              <el-icon><PriceTag /></el-icon>
            </div>
            <div class="metric-info">
              <h3>Today's Expense</h3>
              <p class="metric-value">${{ formatNumber(todayExpense) }}</p>
              <p class="metric-change" :class="expenseChange >= 0 ? 'positive' : 'negative'">
                <el-icon :class="expenseChange >= 0 ? 'arrow-up' : 'arrow-down'">
                  <CaretTop v-if="expenseChange >= 0" />
                  <CaretBottom v-else />
                </el-icon>
                {{ Math.abs(expenseChange) }}% vs yesterday
              </p>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- Summary Chart -->
    <el-card class="summary-chart" shadow="hover">
      <h3>Today's Financial Overview</h3>
      <div class="chart-container">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="Total Sales">{{ formatNumber(todaySale) }}</el-descriptions-item>
          <el-descriptions-item label="Total Income">{{ formatNumber(todayIncome) }}</el-descriptions-item>
          <el-descriptions-item label="Outstanding Due">{{ formatNumber(todayDue) }}</el-descriptions-item>
          <el-descriptions-item label="Total Expenses">{{ formatNumber(todayExpense) }}</el-descriptions-item>
          <el-descriptions-item label="Net Profit">
            <span :class="netProfit >= 0 ? 'positive' : 'negative'">
              ${{ formatNumber(netProfit) }}
            </span>
          </el-descriptions-item>
          <el-descriptions-item label="Profit Margin">
            <span :class="profitMargin >= 0 ? 'positive' : 'negative'">
              {{ profitMargin.toFixed(2) }}%
            </span>
          </el-descriptions-item>
        </el-descriptions>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

import {
  ShoppingCart,
  Money,
  CreditCard,
  PriceTag,
  CaretTop,
  CaretBottom
} from '@element-plus/icons-vue';

// Reactive state for today's data
const todaySale = ref(null);
const todayIncome = ref(null);
const todayDue = ref(null);
const todayExpense = ref(null);

// Yesterday's static data (could be fetched similarly)
const yesterdaySale = ref(0);
const yesterdayIncome = ref(0);
const yesterdayDue = ref(0);
const yesterdayExpense = ref(0);

// Defensive computed to prevent NaN or errors
const saleChange = computed(() => {  
  if (todaySale.value === 0 && yesterdaySale.value === 0) return 0;  

  if (yesterdaySale.value === 0 && todaySale.value > 0) return 100;  
  
  return (((todaySale.value - yesterdaySale.value) / yesterdaySale.value) * 100).toFixed(1);  
});  

const incomeChange = computed(() => {
  if (todayIncome.value === 0 && yesterdayIncome.value === 0) return 0;  

  if (yesterdayIncome.value === 0 && todayIncome.value > 0) return 100;  
  return (((todayIncome.value - yesterdayIncome.value) / yesterdayIncome.value) * 100).toFixed(1);
});
const dueChange = computed(() => {
  if (todayDue.value === 0 && yesterdayDue.value === 0) return 0;
  if (yesterdayDue.value === 0 && todayDue.value > 0) return 100;
  return (((todayDue.value - yesterdayDue.value) / yesterdayDue.value) * 100).toFixed(1);
});
const expenseChange = computed(() => {
  if (todayExpense.value === 0 && yesterdayExpense.value === 0) return 0;
  if (yesterdayExpense.value === 0 && todayExpense.value > 0) return 100;
  if (yesterdayExpense.value === 0 && todayExpense.value < 0) return -100;
  return (((todayExpense.value - yesterdayExpense.value) / yesterdayExpense.value) * 100).toFixed(1);
});

// Net profit & margin with safe division
const netProfit = computed(() => {
  if (todayIncome.value == null || todayExpense.value == null) return 0;
  return todayIncome.value - todayExpense.value;
});
const profitMargin = computed(() => {
  if (todayIncome.value == null || todayIncome.value === 0) return 0;
  return (netProfit.value / todayIncome.value) * 100;
});

// Current date formatted
const currentDate = new Date().toLocaleDateString('en-US', {
  weekday: 'long',
  year: 'numeric',
  month: 'long',
  day: 'numeric'
});

// Number formatter with fallback
const formatNumber = (num) => {  
  if (num === undefined || num === null || isNaN(num)) return '-';  
  return Number(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');  
};  

// Fetch functions with error handling and consistent API URL paths
const fetchTodaySale = async () => {
  try {
    const res = await axios.get('/api/today/sell');
    todaySale.value = res.data ?? 0;
  } catch (error) {
    todaySale.value = 0;
    console.error('Error fetching todaySale:', error);
  }
};

const fetchTodayIncome = async () => {
  try {
    const res = await axios.get('/api/today/income');
    todayIncome.value = res.data ?? 0;
  } catch (error) {
    todayIncome.value = 0;
    console.error('Error fetching todayIncome:', error);
  }
};

const fetchTodayDue = async () => {
  try {
    const res = await axios.get('/api/today/due');
    todayDue.value = res.data ?? 0;
  } catch (error) {
    todayDue.value = 0;
    console.error('Error fetching todayDue:', error);
  }
};

const fetchTodayExpense = async () => {
  try {
    const res = await axios.get('/api/today/expense');
    todayExpense.value = res.data ?? 0;
  } catch (error) {
    todayExpense.value = 0;
    console.error('Error fetching todayExpense:', error);
  }
};

// Loading states
const loading = ref({
  today: true,
  yesterday: true
})

// Fetch yesterday's data

const fetchYesterdaySale = async () => {
  try {
    const res = await axios.get('/api/yesterday/sales');
    console.log("yesterdaySale", res.data.amount);
    yesterdaySale.value = res.data.amount || 0;
  } catch (error) {
    console.error('Error fetching yesterdaySale:', error);
  }
};
const fetchYesterdayIncome = async () => {
  try {
    const res = await axios.get('/api/yesterday/income');
    yesterdayIncome.value = res.data.amount || 0;
  } catch (error) {
    console.error('Error fetching yesterdayIncome:', error);
  }
};
const fetchYesterdayDue = async () => {
  try {
    const res = await axios.get('/api/yesterday/due');
    yesterdayDue.value = res.data.amount || 0;
  } catch (error) {
    console.error('Error fetching yesterdayDue:', error);
  }
};
const fetchYesterdayExpense = async () => {
  try {
    const res = await axios.get('/api/yesterday/expense');
    yesterdayExpense.value = res.data.amount || 0;
  } catch (error) {
    console.error('Error fetching yesterdayExpense:', error);
  }
};


onMounted(() => {
  fetchTodaySale();
  fetchTodayIncome();
  fetchTodayDue();
  fetchTodayExpense();
  fetchYesterdaySale();
  fetchYesterdayIncome();
  fetchYesterdayDue();
  fetchYesterdayExpense();
  loading.value.today = false;
  loading.value.yesterday = false;
});
</script>

<style scoped>
.dashboard-container {
  padding: 20px;
  background-color: #f5f7fa;
  min-height: 100vh;
}

.date-display {
  color: #666;
  margin-bottom: 20px;
  font-weight: 500;
}

.metric-card {
  margin-bottom: 20px;
  border-radius: 8px;
}

.card-content {
  display: flex;
  align-items: center;
}

.metric-icon {
  font-size: 2.5rem;
  margin-right: 15px;
  padding: 10px;
  border-radius: 50%;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sale-card .metric-icon {
  background-color: #f0f9eb;
  color: #67c23a;
}

.income-card .metric-icon {
  background-color: #ecf5ff;
  color: #409eff;
}

.due-card .metric-icon {
  background-color: #fdf6ec;
  color: #e6a23c;
}

.expense-card .metric-icon {
  background-color: #fef0f0;
  color: #f56c6c;
}

.metric-info h3 {
  margin: 0;
  font-size: 1rem;
  color: #666;
  font-weight: 600;
}

.metric-value {
  font-size: 1.5rem;
  font-weight: bold;
  margin: 4px 0 8px 0;
}

.metric-change {
  margin: 0;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  font-weight: 500;
}

.positive {
  color: #67c23a;
}

.negative {
  color: #f56c6c;
}

.arrow-up {
  color: #67c23a;
}

.arrow-down {
  color: #f56c6c;
}

.summary-chart {
  margin-top: 20px;
}

.summary-chart h3 {
  margin-top: 0;
  font-weight: 600;
  color: #333;
}

.chart-container {
  margin-top: 20px;
}

@media (max-width: 768px) {
  .card-content {
    flex-direction: column;
    text-align: center;
  }
  
  .metric-icon {
    margin-right: 0;
    margin-bottom: 10px;
  }
}
</style>