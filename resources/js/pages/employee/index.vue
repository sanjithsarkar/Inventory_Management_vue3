<script setup>
import { ref, onMounted, watch } from 'vue';
import { Bootstrap5Pagination } from 'laravel-vue-pagination';
import { debounce } from 'lodash';
import { useToastr } from '../../Helper/toaster';
import { useRouter } from 'vue-router';

const toastr = useToastr();

const employeeData = ref({ 'data': '' });

const searchQuery = ref(null);

const router = useRouter();

const getEmployees = (page = 1) => {
    axios.get('/api/employees?page=' + page, {
        params: {
            query: searchQuery.value
        }
    })
        .then(response => {
            employeeData.value = response.data;
        })
        .catch(res => {
            console.log(res.data);
        })
}

function deleteEmployee(id) {
    if (window.confirm("Are you sure you want to delete this item?")) {
    axios.delete('/api/employees/' + id)
        .then(() => {
            employeeData.value.data = employeeData.value.data.filter(employee => employee.id != id);
            router.push({ path: '/employee' })
            toastr.error('Employee Deleted Successfully!!');
        })
        .catch(error => {
            console.log(error);
        })
    }
}


// const searchEmployee = () => {
//     axios.get('/api/search', {
//         params: {
//             query: searchQuery.value
//         }
//     })
//         .then(response => {
//             console.log(response.data);
//             employeeData.value = response.data;
//         })
//         .catch(error => {
//             console.log(error);
//         })

// }

watch(searchQuery, debounce(() => {
    getEmployees();
}, 300));


onMounted(() => {
    getEmployees();
})


</script>
<template>
    <section id="employee-index" class="p-4">
        <div class="add-link d-flex justify-content-between">
            <button class="btn btn-primary"><router-link to="/employee/create" class="text-white"
                    style="text-decoration: none;">Add Employee</router-link></button>

            <div>
                <input type="text" v-model="searchQuery" placeholder="Search...">
            </div>
        </div>

        <div class="d-flex justify-content-center">
            <h4 class="my-4">Employee List</h4>
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
                    <tr v-for="(emp, index) in employeeData.data" :key="emp.id">
                        <td>{{ ++index }}</td>
                        <td>{{ emp.name }}</td>
                        <td>{{ emp.email }}</td>
                        <td>{{ emp.address }}</td>
                        <td>{{ emp.salary }}</td>
                        <td> <img :src="emp.image_url" alt="" :height="50"></td>
                        <td><router-link :to="`/employee/edit/${emp.id}`" class="btn btn-success mr-2">Edit</router-link>

                            <a @click="deleteEmployee(emp.id)" class="btn btn-danger">Delete</a>
                        </td>
                        <td><router-link :to="{ name: 'employee-edit', params: { id: emp.id } }">
                                Edit
                            </router-link></td>
                        <!-- <button @click="deleteUser">Delete</button> -->
                    </tr>
                </tbody>
            </table>
            <Bootstrap5Pagination :data="employeeData" @pagination-change-page="getEmployees" />
        </div>
    </section>
</template>