<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { Bootstrap5Pagination } from 'laravel-vue-pagination';
import { debounce } from 'lodash';
import { useToastr } from '../../Helper/toaster';
import { useRouter } from 'vue-router';
import axios from 'axios';

const toastr = useToastr();

const orderData = ref({ 'data': '' });

const searchQuery = ref('');
const startDate = ref(null);
const endDate = ref(null);
const router = useRouter();


const orderProduct = ref(null);
const order = ref(null);

// -------------- Modal -----------------

const isModalVisible = ref(false);
const showModal = (id) => {
    isModalVisible.value = true;


    // ------------- get order product by order id ----------------

    axios.get('/api/order/product/' + id)
        .then(res => {
            orderProduct.value = res.data;
        })

    // ------------------

    axios.get('/api/order/' + id)
        .then(res => {
            order.value = res.data;
        })
};

const hideModal = () => {
    isModalVisible.value = false;
};

const getOrders = (page = 1) => {

    axios.get('/api/orders?page=' + page, {
        params: {
            query: searchQuery.value.length == ''? '':searchQuery.value,
            startDate: searchQuery.value.length == '' ? startDate.value : '',
            endDate: searchQuery.value.length == '' ? endDate.value : ''
        }
    })
        .then(response => {
            orderData.value = response.data;
            console.log('orderData = ', orderData.value);
        })
        .catch(res => {
            console.log(res.data);
        })
}

// const getOrders = (page = 1) => {
//     axios.get('/api/orders?page=' + page, {
//         params: {
//             // query: searchQuery.value,
//             date: dateSearch.value
//         }
//     })
//         .then(response => {
//             orderData.value = response.data;
//             console.log('orderData = ', orderData.value);l
//         })
//         .catch(res => {
//             console.log(res.data);
//         })
// }

// ------------ Search By Date -----------

const searchByDate = () => {
    axios.get('/api/search/by/date', {
        params: {
            startDate: startDate.value || '',
            endDate: endDate.value || ''
        }
    })
        .then()
        .catch()
}


watch([searchQuery, startDate, endDate], debounce(() => {
    getOrders();
}, 300));


onMounted(() => {
    getOrders();
    searchByDate();

})


</script>

<template>
    <section id="employee-index" class="p-4">
        <div class="add-link d-flex justify-content-between">
            <div class="">
                From:<input type="date" v-model="startDate" class="mx-2 p-1">

                To:<input type="date" v-model="endDate" class="mx-2 p-1">
                <button @click.prevent="getOrders" class="btn btn-outline-success">Search</button>
            </div>

            <div>
                <input type="text" v-model="searchQuery" class="px-3" placeholder="Search By Order Id....">
            </div>
        </div>

        <div class="d-flex justify-content-center">
            <h4 class="my-4">Order List</h4>
        </div>

        <div>
            <table id="table" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Serial No</th>
                        <th>OrderNumber</th>
                        <th>Customer</th>
                        <th>quantity</th>
                        <th>subTotal</th>
                        <th>discount</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="tablecontents">
                    <tr v-for="(order, index) in orderData.data" :key="order.id">
                        <td>{{ ++index }}</td>
                        <td>{{ order.order_number }}</td>
                        <td v-if="order.customer_id != null">{{ order.customer.name }}</td>
                        <td v-else>Null</td>
                        <td>{{ order.quantity }}</td>
                        <td>{{ order.subTotal }}</td>
                        <td v-if="order.discount < 1">0</td>
                        <td v-else="">{{ order.discount }}%</td>
                        <td>{{ order.total }}</td>
                        <td>{{ order.paid }}</td>
                        <td>{{ order.due }}</td>
                        <td>{{ order.date }}</td>

                        <td><button @click="showModal(order.id)" class="badge badge-primary p-2 mx-2">Show</button>

                            <router-link :to="`/employee/edit/${order.id}`"
                                class="badge badge-info p-2 mr-2">Edit</router-link>
                        </td>
                        <!-- <td><router-link :to="{ name: 'employee-edit', params: { id: emp.id } }">
                                Edit
                            </router-link></td> -->
                    </tr>
                </tbody>
            </table>
            <Bootstrap5Pagination :data="orderData" @pagination-change-page="getOrders" />
        </div>

        <div v-if="isModalVisible" class="modal">
            <div class="modal-content">
                <!-- Modal content goes here -->
                <h4>Product Details</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                            </div>

                            <div class="card-body">
                                <h5>Customer:</h5>
                                <ul v-for="(order, index) in order" :key="order.id" class="list-group">
                                    <ul v-if="order.customer_id != null">
                                        <li class="list-group-item">Name: {{ order.customer.name }}</li>
                                        <li class="list-group-item">Email: {{ order.customer.email }}</li>
                                        <li class="list-group-item">Name: {{ order.customer.phone }}</li>
                                        <li class="list-group-item">Email: {{ order.customer.address }}</li>
                                    </ul>
                                    <ul v-else="">
                                        <span>No Available</span>
                                    </ul>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                            </div>

                            <div class="card-body">
                                <h5>Order Details:</h5>
                                <ul v-for="(order, index) in order" :key="order.id" class="list-group d-flex flex-wrap">
                                    <!-- <li class="list-group-item">Name: {{ order.order_number }}</li> -->

                                    <li>Order Number: {{ order.order_number }}</li>
                                    <li>Quantity: {{ order.quantity }}</li>
                                    <li>SubTotal: {{ order.subTotal }}</li>
                                    <li>Discount: {{ order.discount }}</li>
                                    <li>Discounted Payment: {{ order.discount_payment }}</li>
                                    <li>Total: {{ order.total }}</li>
                                    <li>Paid: {{ order.paid }}</li>
                                    <li>Due: {{ order.due }}</li>
                                    <li>PayBy: {{ order.payby }}</li>
                                    <li>Date: {{ order.date }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="d-flex justify-content-center">List of Order Product</p>
                <table>
                    <thead>
                        <tr>
                            <th>Serial</th>
                            <th>Product id</th>
                            <th>Name</th>
                            <th>quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(orderPro, index) in orderProduct" :key="orderPro.id">
                            <td>{{ ++index }}</td>
                            <td>{{ orderPro.pro_id }}</td>
                            <td>{{ orderPro.name }}</td>
                            <td>{{ orderPro.quantity }}</td>
                            <td>{{ orderPro.price }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="d-flex justify-content-center mt-4">
                    <button @click="hideModal" class="badge badge-danger p-2">Close Modal</button>
                </div>
            </div>
        </div>
    </section>
</template>

<style>
.modal {
    position: fixed;
    top: 3%;
    left: 25%;
    width: 50%;
    height: 95%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 4px;
    width: 100%;
    height: 100%;
}

ul li {
    list-style: none;
}
</style>