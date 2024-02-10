<script setup>
import axios from 'axios';
import { ref, toDisplayString } from 'vue';
import { useRouter } from 'vue-router';

import { useToastr } from '../../Helper/toaster';

const toastr = useToastr();

const form = ref({});
const errors = ref({});
const router = useRouter();

const imageUrl = ref(null);

const onFileSelected = (event) => {
  form.value.image = event.target.files[0];
  imageUrl.value = URL.createObjectURL(form.value.image);
};

const employeeInsert = () => {

    let formData = new FormData();
    formData.append('image', form.value.image);
    console.log( form.value.image);

    axios.post('api/employees', formData, {
        params: form.value
    })
        .then(res => {
            if (res.data.errors) {
                 console.log(res.data.errors);
                errors.value = res.data.errors;
            } else {
                router.push({ path: '/employee' });
                toastr.success('Employee Inserted Successfully!!');
           }
        })
        .catch(res => {
            errors.value = res.response.data.errors;
        })
}

</script>

<template>
    <section id="employee-create" class="p-4">
        <div>
            <router-link to="/employee" class="btn btn-primary">Employee List</router-link>
        </div>

        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12">
                <div class="card shadow-sm my-4">
                    <div class="card-header d-flex justify-content-center">
                        <h4 class="text-gray-900">Add Employee</h4>
                    </div>

                    <div class="card-body">
                        <form class="user" @submit.prevent="employeeInsert" enctype="multipart/form-data">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control m-2 py-2" id="exampleInputFirstName"
                                            placeholder="Enter Your Full Name" v-model="form.name">
                                        <small class="text-danger" v-if="errors.name"> {{ errors.name[0] }} </small>
                                    </div>

                                    <div class="col-md-6">
                                        <input type="email" class="form-control m-2 py-2" id="exampleInputFirstName"
                                            placeholder="Enter Your Email" v-model="form.email">
                                        <small class="text-danger" v-if="errors.email"> {{ errors.email[0] }} </small>
                                    </div>

                                </div>
                            </div>


                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control m-2 py-2" id="exampleInputFirstName"
                                            placeholder="Enter Your Address" v-model="form.address">
                                        <small class="text-danger" v-if="errors.address"> {{ errors.address[0] }} </small>
                                    </div>


                                    <div class="col-md-6">
                                        <input type="number" class="form-control m-2 py-2" id="exampleInputFirstName"
                                            placeholder="Enter Your Salary" v-model="form.salary">
                                        <small class="text-danger" v-if="errors.salary"> {{ errors.salary[0] }} </small>
                                    </div>

                                </div>
                            </div>

                            
                            <div class="form-group">

                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="date" class="form-control m-2 py-2" id="exampleInputFirstName"
                                            placeholder="Enter Your Joining Date" v-model="form.joining_date">
                                        <small class="text-danger" v-if="errors.joining_date"> {{ errors.joining_date[0] }}
                                        </small>
                                    </div>


                                    <div class="col-md-6">
                                        <input type="number" class="form-control m-2 py-2" id="exampleInputFirstName"
                                            placeholder="Enter Your Nid" v-model="form.nid">
                                        <small class="text-danger" v-if="errors.nid"> {{ errors.nid[0] }} </small>
                                    </div>

                                </div>
                            </div>



                            <div class="form-group">

                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="number" class="form-control m-2 py-2" id="exampleInputFirstName"
                                            placeholder="Enter Your phone Number" v-model="form.phone">
                                        <small class="text-danger" v-if="errors.phone"> {{ errors.phone[0] }} </small>
                                    </div>


                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-7">
                                                <input type="file" class="m-2 py-2" id="customFile"
                                                    @change="onFileSelected">

                                                <small class="text-danger" v-if="errors.image"> {{ errors.image[0] }}
                                                </small>
                                            </div>


                                            <div class="col-md-5">
                                                <img :src="imageUrl" v-if="imageUrl" style="height: 50px; width: 60px;">
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group d-flex justify-content-center mt-4">
                                <button type="submit" class="btn btn-primary btn-block px-4 py-2">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>