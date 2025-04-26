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
import { useRouter } from 'vue-router';
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';

import {
  ShoppingCart,
  Money,
  CreditCard,
  PriceTag,
  CaretTop,
  CaretBottom
} from '@element-plus/icons-vue';

// Sample data - replace with your actual data
const todaySale = ref();
const todayIncome = ref();
const todayDue = ref();
const todayExpense = ref();

// Yesterday's data for comparison
const yesterdaySale = ref(11850.00);
const yesterdayIncome = ref(8950.00);
const yesterdayDue = ref(2935.75);
const yesterdayExpense = ref(2980.25);

// Calculate percentage changes
const saleChange = computed(() => 
  ((todaySale.value - yesterdaySale.value) / yesterdaySale.value * 100).toFixed(1)
);
const incomeChange = computed(() => 
  ((todayIncome.value - yesterdayIncome.value) / yesterdayIncome.value * 100).toFixed(1)
);
const dueChange = computed(() => 
  ((todayDue.value - yesterdayDue.value) / yesterdayDue.value * 100).toFixed(1)
);
const expenseChange = computed(() => 
  ((todayExpense.value - yesterdayExpense.value) / yesterdayExpense.value * 100).toFixed(1)
);

// Calculate net profit and profit margin
const netProfit = computed(() => todayIncome.value - todayExpense.value);
const profitMargin = computed(() => (netProfit.value / todayIncome.value) * 100);

// Current date
const currentDate = new Date().toLocaleDateString('en-US', {
  weekday: 'long',
  year: 'numeric',
  month: 'long',
  day: 'numeric'
});

// Helper function to format numbers
const formatNumber = (num) => {  
  if (num === undefined || num === null || isNaN(num)) return '-';  
  return Number(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');  
};  

// -------------- Today Sell ---------------------

const todaySellData = () => {
    axios.get('/api/today/sell')
    .then((res) =>{
      todaySale.value = res.data;
    })
    .catch
}


// ------------- Today INcome --------------

const todayIncomeData = () => {
    axios.get('/api/today/income')
    .then((res) => {
      todayIncome.value = res.data;
    })
}

// ----------- Today Due -------------

const todayDueData = () => {
    axios.get('api/today/due')
    .then((res) => {
        todayDue.value = res.data;
    })
}

// ----------- Today Expense -------------

const todayExpenseData = () => {
    axios.get('api/today/expense')
    .then((res) => {
        todayExpense.value = res.data;
    })
}

onMounted(()=>{
    todaySellData();
    todayIncomeData();
    todayDueData();
    todayExpenseData();
})
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
}

.metric-value {
  font-size: 1.5rem;
  font-weight: bold;
  margin: 5px 0;
}

.metric-change {
  margin: 0;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
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