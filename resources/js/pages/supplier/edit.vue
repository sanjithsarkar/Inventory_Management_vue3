<template>
    <section id="supplier-edit" class="p-4">
        <div class="mb-4">
            <el-button type="primary" @click="router.push('/supplier')">
                <i class="el-icon-arrow-left"></i> Back to Supplier List
            </el-button>
        </div>

        <el-card class="box-card" shadow="hover" v-loading="loading">
            <template #header>
                <div class="text-center">
                    <h4 class="text-gray-900 m-0">Edit Supplier</h4>
                </div>
            </template>

            <el-form :model="form" label-position="top" class="max-w-3xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <el-form-item label="Name" :error="errors.name?.[0]">
                        <el-input v-model="form.name" placeholder="Enter supplier name" />
                    </el-form-item>

                    <el-form-item label="Email" :error="errors.email?.[0]">
                        <el-input v-model="form.email" placeholder="Enter email address" />
                    </el-form-item>

                    <el-form-item label="Phone" :error="errors.phone?.[0]">
                        <el-input v-model="form.phone" placeholder="Enter phone number" />
                    </el-form-item>

                    <el-form-item label="Shop Name" :error="errors.shopname?.[0]">
                        <el-input v-model="form.shopname" placeholder="Enter shop name" />
                    </el-form-item>

                    <el-form-item label="Address" :error="errors.address?.[0]" class="md:col-span-2">
                        <el-input v-model="form.address" type="textarea" rows="3" placeholder="Enter address" />
                    </el-form-item>

                    <el-form-item label="Image" :error="errors.image?.[0]" class="md:col-span-2">
                        <input type="file" @change="onFileSelected" class="form-control" accept="image/*">
                        <div v-if="imageUrl" class="mt-2">
                            <img :src="imageUrl" class="w-32 h-32 object-cover border rounded" />
                        </div>
                    </el-form-item>
                </div>

                <div class="flex justify-center mt-6">
                    <el-button type="primary" @click="supplierUpdate" :loading="submitting">
                        Update Supplier
                    </el-button>
                </div>
            </el-form>
        </el-card>
    </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { ElNotification } from 'element-plus';

const router = useRouter();
const route = useRoute();
const id = route.params.id;

const form = ref({
    name: '',
    email: '',
    phone: '',
    address: '',
    shopname: '',
    image: null
});
const errors = ref({});
const loading = ref(false);
const submitting = ref(false);
const imageUrl = ref(null);

const getSupplier = async () => {
    try {
        loading.value = true;
        const response = await axios.get(`/api/suppliers/${id}`);
        const supplier = response.data;
        
        form.value = {
            name: supplier.name,
            email: supplier.email,
            phone: supplier.phone,
            address: supplier.address,
            shopname: supplier.shopname || '',
            image: null
        };
        
        if (supplier.image) {
            imageUrl.value = `/storage/${supplier.image}`;
        }
    } catch (error) {
        console.error('Error fetching supplier:', error);
        ElNotification.error({
            title: 'Error',
            message: 'Failed to fetch supplier details'
        });
    } finally {
        loading.value = false;
    }
};

const onFileSelected = (event) => {
    form.value.image = event.target.files[0];
    imageUrl.value = URL.createObjectURL(form.value.image);
};

const supplierUpdate = async () => {
    try {
        submitting.value = true;
        const formData = new FormData();
        
        // Add image if selected
        if (form.value.image) {
            formData.append('image', form.value.image);
        }
        
        // Add other form fields
        Object.entries(form.value).forEach(([key, value]) => {
            if (key !== 'image' && value !== null && value !== undefined) {
                formData.append(key, value);
            }
        });
        
        formData.append('_method', 'PUT');

        const response = await axios.post(`/api/suppliers/${id}`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        if (response.data.errors) {
            errors.value = response.data.errors;
        } else {
            ElNotification.success({
                title: 'Success',
                message: 'Supplier updated successfully'
            });
            router.push('/supplier');
        }
    } catch (error) {
        if (error.response && error.response.data.errors) {
            errors.value = error.response.data.errors;
        } else {
            ElNotification.error({
                title: 'Error',
                message: 'Failed to update supplier'
            });
            console.error('Error:', error);
        }
    } finally {
        submitting.value = false;
    }
};

onMounted(() => {
    getSupplier();
});
</script>

<style scoped>
.box-card {
    margin-bottom: 20px;
}
</style>