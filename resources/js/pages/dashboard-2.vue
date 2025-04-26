<script setup>
import axios from 'axios';
import { useRouter } from 'vue-router';
import { computed, onMounted, ref } from 'vue';

const router = useRouter();
const todaySellData = ref(null);
const todayIncomeData = ref(null);
const todayDueData = ref(null);
const todayExpenseData = ref(null);

const logout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    router.push({ path: '/' })
}


// -------------- Today Sell ---------------------

const todaySell = () => {
    axios.get('/api/today/sell')
    .then((res) =>{
        todaySellData.value = res.data;
    })
    .catch
}


// ------------- Today INcome --------------

const todayIncome = () => {
    axios.get('/api/today/income')
    .then((res) => {
        todayIncomeData.value = res.data;
    })
}

// ----------- Today Due -------------

const todayDue = () => {
    axios.get('api/today/due')
    .then((res) => {
        todayDueData.value = res.data;
    })
}

// ----------- Today Expense -------------

const todayExpense = () => {
    axios.get('api/today/expense')
    .then((res) => {
        todayExpenseData.value = res.data;
    })
}

onMounted(()=>{
    todaySell();
    todayIncome();
    todayDue();
    todayExpense();
})

</script>

<template>
    <div>
        <h3>this is dashboard</h3>
        <button type="button" @click="logout()" class="btn btn-dark mt-4">Logout</button>
    </div>

    <section>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-6">

                    <div class="small-box bg-info">
                        <div class="inner">
                            <p>Today Sells Amount</p>
                            <h3>${{ todaySellData }}</h3>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-success">
                        <div class="inner">
                            <p>Today Income</p>
                            <h3>${{ todayIncomeData }}<sup style="font-size: 20px">%</sup></h3>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-warning">
                        <div class="inner">
                            <p>Today Due</p>
                            <h3>${{ todayDueData }}</h3>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person-add"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-danger">
                        <div class="inner">
                            <p>Today Expense</p>
                            <h3>${{ todayExpenseData }}</h3>
                        </div>
                        <div class="icon">
                            <i class="ion ion-pie-graph"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

            </div>
        </div>
    </section>
</template>