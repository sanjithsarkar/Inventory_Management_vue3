<script setup>
import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const form = ref({});
const errors = ref({});
const router = useRouter();
const categoriesData = ref({});
const editing = ref(false);
const currntEditingId = ref();
const isModalAvailable = ref(false);


// ---------- Modal -----------

const showModal = () => {
    isModalAvailable.value = true;
};

const hideModal = () => {
    isModalAvailable.value = false;
};

// ------------ Get All Categories ---------------

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


// ----------------- Submit method ---------------

const handleSubmit = () => {
    if (editing.value) {
        updateCategory();
    } else {
        createCategory();
    }
}


// -------------- Show Add Category modal ------------------

const addCategory = () => {
    editing.value = false;
    showModal();
}


// -------------- Create Category Data --------------

const createCategory = () => {
    currntEditingId.value = null;

    axios.post('/api/categories', form.value)
        .then((response) => {
            if (response.data.errors) {
                errors.value = response.data.errors;
            } else {
                hideModal();
                categoriesData.value.unshift(response.data)
                form.value.name = '';
            }
        })
        .catch((res) => {
            errors.value = res.response.data.errors;
        })
}

// -------------- Show Edit Modal -------------

const editCategory = (category) => {
    currntEditingId.value = category.id;
    editing.value = true;
    showModal();
    form.value = category;
}

//---------- update Category --------------

const updateCategory = () => {
    axios.put('api/categories/' + currntEditingId.value, form.value)
        .then(() => {
            hideModal();
            getCategories();
            form.value.name = '';
        })
}


</script>

<template>
    <section id="employee-index" class="p-4">
        <div class="row d-flex justify-content-center">
            <div class="col-md-8">
                <div class="add-link d-flex justify-content-between">
                    <button @click="addCategory" class="btn btn-primary">Add
                        Category</button>

                    <div v-if="isModalAvailable" class="category-modal">
                        <div class="category-modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" v-if="editing" id="exampleModalLabel">Edit Category</h1>
                                <h1 class="modal-title fs-5" v-else id="exampleModalLabel">Add Category</h1>
                                <button type="button" @click="hideModal" class="badge badge-primary" data-bs-dismiss="modal"
                                    aria-label="Close">x</button>

                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Email address</label>
                                    <input type="text" class="form-control" id="name" v-model="form.name"
                                        placeholder="name">
                                    <small class="text-danger" v-if="errors.name"> {{ errors.name[0] }} </small>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" @click="hideModal" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Close</button>
                                <button @click="handleSubmit" type="button" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <!-- <input type="text" v-model="searchQuery" placeholder="Search..."> -->
                    </div>
                </div>

                <div class="d-flex justify-content-center">
                    <h4 class="my-4">Category List</h4>
                </div>

                <div>
                    <table id="table" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Serial No</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tablecontents">
                            <tr v-for="(cat, index) in categoriesData" :key="cat.id">
                                <td>{{ ++index }}</td>
                                <td>{{ cat.name }}</td>
                                <td>
                                    <a href="#" @click.prevent="editCategory(cat)"> <i class="fa fa-edit"></i></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</template>

<style>
.category-modal {
    position: fixed;
    top: 20%;
    left: 35%;
    width: 40%;
    height: 50%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.category-modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 4px;
    width: 95%;
    height: 95%;
}
</style>