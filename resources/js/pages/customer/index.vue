<script setup>
import { ref, onMounted, watch } from 'vue';
import { Bootstrap5Pagination } from 'laravel-vue-pagination';
import { debounce } from 'lodash';


const customerData = ref({ 'data': '' });
const searchQuery = ref(null);

const getCustomers = (page = 1) => {
    axios.get('api/customers?page=' + page, {
        params: {
            query: searchQuery.value
        }
    })
        .then(res => {
            customerData.value = res.data;
        })
        .catch()
}

const deleteCustomer = (id) => {

    if (window.confirm("Are you sure you want to delete this item?")) {
        axios.delete('api/customers/' + id)
            .then(() => {
                customerData.value.data = customerData.value.data.filter(customer => {
                    customer.id != id;
                })
            })
    }
}

watch(searchQuery, debounce(() => {
    getCustomers();
}, 300));

onMounted(() => {
    getCustomers();
})
</script>

<template>
    <section id="employee-index" class="p-4">
        <div class="add-link d-flex justify-content-between">
            <button class="btn btn-primary"><router-link to="/customer/create" class="text-white"
                    style="text-decoration: none;">Add Customer</router-link></button>

            <div>
                <input type="text" v-model="searchQuery" placeholder="Search...">
            </div>
        </div>

        <div class="d-flex justify-content-center">
            <h4 class="my-4">Customer List</h4>
        </div>

        <div>
            <table id="table" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Serial No</th>
                        <th>Name</th>
                        <th>email</th>
                        <th>address</th>
                        <th>salary</th>
                        <th>Image</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="tablecontents">
                    <tr v-for="(customer, index) in customerData.data" :key="customer.id">
                        <td>{{ ++index }}</td>
                        <td>{{ customer.name }}</td>
                        <td>{{ customer.email }}</td>
                        <td>{{ customer.address }}</td>
                        <td> <img :src="customer.image_url" alt="" :height="50"></td>
                        <td><router-link :to="`/customer/edit/${customer.id}`"
                                class="btn btn-success mr-2">Edit</router-link>

                            <a @click="deleteCustomer(customer.id)" class="btn btn-danger">Delete</a>
                        </td>
                        <!-- <td><router-link :to="{ name: 'employee-edit', params: { id: emp.id } }">
                                    Edit
                                </router-link></td> -->
                    </tr>
                </tbody>
            </table>
            <Bootstrap5Pagination :data="customerData" @pagination-change-page="getCustomers" />
        </div>
    </section>
</template>