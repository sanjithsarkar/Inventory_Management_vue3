<script setup>
import { ref, onMounted, watch, reactive, computed } from 'vue';
import { Bootstrap5Pagination } from 'laravel-vue-pagination';
import { debounce } from 'lodash';
import { useToastr } from '../../Helper/toaster';
import { useRouter } from 'vue-router';
import axios from 'axios';

const toastr = useToastr();

const productData = ref({ 'data': '' });

const searchQuery = ref(null);

const router = useRouter();

const getProducts = (page = 1) => {
    axios.get('/api/products?page=' + page, {
        params: {
            query: searchQuery.value
        }
    })
        .then(response => {
            productData.value = response.data;
        })
        .catch(res => {
            console.log(res.data);
        })
}


const deleteProduct = (id) => {
    if (window.confirm("Are you sure you want to delete this item?")) {
        axios.delete('/api/products/' + id)
            .then(() => {
                productData.value.data = productData.value.data.filter(product => product.id != id);
                router.push({ path: '/product' })
                toastr.error('Product Deleted Successfully!!');
            })
            .catch(error => {
                console.log(error);
            })
    }
}


// ----------------------- toggle Selection -----------------------

const selectedProductIds = ref([]);
const errors = ref(null);

// const toggleSelection = (productId) => {
//     // selectedProduct.value.push(product.id);
//     // console.log(selectedProduct.value);

//     const index = selectedProduct.value.indexOf(productId);
//     if (index > -1) {
//         selectedProduct.value.splice(index, 1);
//     } else {
//         selectedProduct.value.push(productId);
//     }
//     console.log(selectedProduct.value);
// }


// --------------------- bulkDelete ------------------

// const bulkDelete = () => {
//     axios.delete('/api/products', {
//         data: {
//             ids: selectedProductIds.value,
//         },
//     })
//         .then((res) => {
//             if (res.data.errors) {
//                 console.log(res.data.errors);
//                 errors.value = res.data.errors;
//                 toastr.error('Product did not selected!!');
//             }else{
//                 methodRefreshByDebounce();
//                 toastr.success('Product Deleted Successfully!!');
//             }
//         })
//         .catch((error) => {
//             // alert('Error deleting selected products');
//             errors.value = error.response.data.errors;
//             console.log(error.response.data.errors);
//         });

// }




const bulkDelete = () => {
    // Show a confirmation dialog using SweetAlert
    Swal.fire({
        title: 'Confirm Deletion',
        text: 'Are you sure you want to delete the selected products?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
        if (result.isConfirmed) {
            // User confirmed the deletion, proceed with axios delete request
            axios.delete('/api/products', {
                data: {
                    ids: selectedProductIds.value,
                },
            })
                .then((res) => {
                    if (res.data.errors) {
                        console.log(res.data.errors);
                        errors.value = res.data.errors;
                        toastr.error('Product was not selected!!');
                    } else {
                        methodRefreshByDebounce();
                        toastr.success('Product Deleted Successfully!!');
                    }
                })
                .catch((error) => {
                    // alert('Error deleting selected products');
                    errors.value = error.response.data.errors;
                    console.log(error.response.data.errors);
                });
        }
    });
};




// ----------------- bulk delete selected all -------------------------



watch([searchQuery], debounce(() => {
    getProducts();
}, 300));


const methodRefreshByDebounce = debounce(() => {
    getProducts();
}, 300)

onMounted(() => {
    getProducts();
})


</script>
<template>
    <section id="employee-index" class="p-4">
        <div class="add-link d-flex justify-content-between">
            <div>
                <button class="btn btn-primary"><router-link to="/product/create" class="text-white"
                        style="text-decoration: none;">Add Product</router-link></button>
                <button @click="bulkDelete" class="btn btn-danger ml-2">Delete Selected</button>

            </div>

            <div>
                <input type="text" v-model="searchQuery" placeholder="Search...">
            </div>
        </div>

        <div class="d-flex justify-content-center">
            <h4 class="">Product List</h4>
        </div>

        <div>
            <table id="table" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>Serial No</th>
                        <th>Name</th>
                        <th>category_id</th>
                        <th>Quantity</th>
                        <th>Image</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="tablecontents">
                    <tr v-for="(product, index) in productData.data" :key="product.id">
                        <td><input type="checkbox" :value="product.id" v-model="selectedProductIds"></td>
                        <td>{{ ++index }}</td>
                        <td>{{ product.name }}</td>
                        <td>{{ product.category_id }}</td>
                        <td>{{ product.quantity }}</td>
                        <td> <img v-if="product.image_url" :src="product.image_url" :alt="product.name"
                                class="img-thumbnail" style="height: 50px; width: auto;">
                            <span v-else>No image</span>
                        </td>
                        <td><router-link :to="`/product/edit/${product.id}`"
                                class="btn btn-success mr-2">Edit</router-link>

                            <a @click="deleteProduct(product.id)" class="btn btn-danger">Delete</a>
                        </td>
                        <!-- <td><router-link :to="{ name: 'product-edit', params: { id: emp.id } }">
                                Edit
                            </router-link></td> -->
                    </tr>
                </tbody>
            </table>
            <Bootstrap5Pagination :data="productData" @pagination-change-page="getProducts" />
        </div>
    </section>
</template>
