
<script setup>

import axios from 'axios';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const form = ref({});

let errors = ref([]);

const router = useRouter();

const registerUser = () => {
    axios.post('api/register', form.value)
        .then(res => {
            if (res.data.success) {
                localStorage.setItem('token', res.data.data.token);
                router.push({ path: '/dashboard' })
                window.location.reload();
            } else {
                errors.value = res.data.message;
            }
        })
        .catch(
            res => {
                errors.value = res.response.data.message;
            }
        )
}

</script>
<template>
    <div>
        <div class="container">
            <div class="row d-flex justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="d-flex justify-content-center">User Register</h3>
                        </div>

                        <p v-for="error in errors" :key="error" class="d-flex justify-content-center text-danger">
                            <span v-for="err in error" :key="err">{{ err }}</span>
                        </p>

                        <div class="card-body">
                            <form @submit.prevent="registerUser">
                                <div class="form-group mt-2">
                                    <label for="">Name:</label>
                                    <input type="text" v-model="form.name" class="form-control"
                                        placeholder="Enter Your Name">
                                    <small class="text-danger" v-if="errors.name">{{ errors.name[0] }}</small>
                                </div>

                                <div class="form-group mt-2">
                                    <label for="">Email</label>
                                    <input type="email" v-model="form.email" class="form-control"
                                        placeholder="Enter Your Email">

                                </div>

                                <div class="form-group mt-2">
                                    <label for="">Password:</label>
                                    <input type="password" v-model="form.password" class="form-control"
                                        placeholder="Enter Your Password">

                                </div>

                                <div class="form-group mt-2">
                                    <label for="">Confirm Password:</label>
                                    <input type="password" v-model="form.c_password" class="form-control"
                                        placeholder="Confirm Password">

                                </div>

                                <div class="d-flex justify-content-center mt-4">
                                    <button type="submit" class="btn btn-success">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>