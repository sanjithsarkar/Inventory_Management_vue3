
<script setup>

import axios from 'axios';
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';

const form = reactive({
    email: '',
    password: '',
});

let error = ref('');
const router = useRouter();

const loginUser = () => {

  axios.post('http://127.0.0.1:8000/api/login', form)
    .then(res => {
      if (res.data.success) {
        localStorage.setItem('token', res.data.data.token);
        localStorage.setItem('user', res.data.data.name);
        router.push({ path: '/dashboard' });
        // window.location.reload();
      } else {
        error.value = res.data.message;
      }
    })
    .catch(error => {
     error.value = error.response.data.message;
    });
};


</script>

<template>
    <div>
        <div class="container">
            <div class="row d-flex justify-content-center">
                <div class="col-md-6">
                    <div class="card mt-5">
                        <div class="card-header">
                            <h3 class="d-flex justify-content-center">User Login</h3>
                        </div>

                        <span class="d-flex justify-content-center mt-4 text-danger" v-if="error">{{ error }}</span>
                        <div class="card-body">
                            <form @submit.prevent="loginUser">

                                <div class="form-group mt-2">
                                    <label for="">Email</label>
                                    <input type="text" v-model="form.email" id="email" class="form-control"
                                        placeholder="Enter Your Email" required>

                                </div>

                                <div class="form-group mt-2">
                                    <label for="">Password:</label>
                                    <input type="password" v-model="form.password" class="form-control"
                                        placeholder="Enter Your Password" required>
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