<script setup>
import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../Helper/toaster';

const toastr = useToastr();

const form = ref({});
const errors = ref({});
const router = useRouter();
const categoriesData = ref({});


const getCategories = () => {

    axios.get('/api/categories')
        .then((res) => {
            categoriesData.value = res.data;
        })
        .catch()
}

onMounted(() => {
    getCategories();
})

const imageUrl = ref(null);

// const onFileSelected = (event) => {
//   form.value.image = event.target.files[0];
//   imageUrl.value = URL.createObjectURL(form.value.image);
//   if (form.value.image) {
//     // Validate file size and type client-side
//     if (form.value.image.size > 2048 * 1024) {
//       errors.value.image = ['Image must be less than 2MB'];
//       return;
//     }
    
//     if (!['image/jpeg', 'image/png', 'image/jpg', 'image/gif'].includes(file.type)) {
//       errors.value.image = ['Invalid image format'];
//       return;
//     }
    
//     formData.value.image = file;
//     imagePreview.value = URL.createObjectURL(file);
//     errors.value.image = null;
//   }
// };

const validateImageFile = (file) => {
  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
  const maxSize = 2 * 1024 * 1024;

  if (!allowedTypes.includes(file.type)) {
    return 'Invalid image format. Please upload JPEG, PNG, or GIF';
  }
  if (file.size > maxSize) {
    return 'Image size must be less than 2MB';
  }
  return null;
};

const onFileSelected = (event) => {
  errors.value.image = null;
  if (imageUrl.value) URL.revokeObjectURL(imageUrl.value);

  const file = event.target.files[0];
  if (!file) return;

  const validationError = validateImageFile(file);
  if (validationError) {
    errors.value.image = [validationError];
    return;
  }

  form.value.image = file;
  imageUrl.value = URL.createObjectURL(file);
};

const productInsert = () => {

    let formData = new FormData();
    formData.append('image', form.value.image);
    console.log(form.value.image);

    axios.post('api/products', formData, {
        params: form.value
    })
        .then(res => {
            if (res.data.errors) {
                console.log(res.data.errors);
                errors.value = res.data.errors;
            } else {
                router.push({ path: '/product' });
                toastr.success('Product Inserted Successfully!!');
            }
        })
        .catch(res => {
            errors.value = res.response.data.errors;
        })
}


const onChange = (event) => {
    var optionValue = event.target.value;
    var optionText = event.target.options[event.target.options.selectedIndex].text;

    console.log(optionText);
    console.log(optionValue);
}

</script>

<template>
    <section id="Product-create" class="p-4">
        <div>
            <router-link to="/product" class="btn btn-primary">Product List</router-link>
        </div>

        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12">
                <div class="card shadow-sm my-4">
                    <div class="card-header d-flex justify-content-center">
                        <h4 class="text-gray-900">Add Product</h4>
                    </div>

                    <div class="card-body">
                        <form class="user" @submit.prevent="productInsert" enctype="multipart/form-data">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control m-2 py-2" id="exampleInputFirstName"
                                            placeholder="Enter Product Name" v-model="form.name">
                                        <small class="text-danger" v-if="errors.name"> {{ errors.name[0] }} </small>
                                    </div>

                                    <div class="col-md-6">
                                        <select class="form-control m-2 py-2" v-model="form.category_id"
                                            @change="onChange($event)" aria-label="Default select example">
                                            <option selected>Open this select menu</option>
                                            <option :value="cat.id" v-for="cat in categoriesData">{{ cat.name }}</option>
                                        </select>
                                        <small class="text-danger" v-if="errors.code"> {{ errors.code[0] }} </small>
                                    </div>

                                </div>
                            </div>


                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="number" class="form-control m-2 py-2" id="exampleInputFirstName"
                                            placeholder="Enter Selling Price" v-model="form.selling_price">
                                        <small class="text-danger" v-if="errors.selling_price"> {{ errors.selling_price[0] }} </small>
                                    </div>


                                    <div class="col-md-6">
                                        <input type="text" class="form-control m-2 py-2" id="exampleInputFirstName"
                                            placeholder="Enter Product Code" v-model="form.code">
                                        <small class="text-danger" v-if="errors.code"> {{ errors.code[0] }} </small>
                                    </div>

                                </div>
                            </div>



                            <div class="form-group">

                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="number" class="form-control m-2 py-2" id="exampleInputFirstName"
                                            placeholder="Enter Product Quantity" v-model="form.quantity">
                                        <small class="text-danger" v-if="errors.quantity"> {{ errors.quantity[0] }} </small>
                                    </div>


                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-7">
                                                <input type="file" class="m-2 py-2" style="display: block;" id="customFile"
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