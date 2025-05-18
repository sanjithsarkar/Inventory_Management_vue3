<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { ElNotification } from 'element-plus';
import { ArrowLeft, Upload, Picture } from '@element-plus/icons-vue';

const router = useRouter();
const loading = ref(false);
const submitting = ref(false);

const form = ref({
    name: '',
    email: '',
    phone: '',
    address: '',
    image: null
});

const errors = ref({});
const imageUrl = ref(null);

const onFileSelected = (event) => {
    form.value.image = event.target.files[0];
    imageUrl.value = URL.createObjectURL(form.value.image);
};

const customerInsert = async () => {
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

        const response = await axios.post('/api/customers', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        if (response.data.errors) {
            errors.value = response.data.errors;
        } else {
            ElNotification.success({
                title: 'Success',
                message: 'Customer created successfully'
            });
            router.push('/customer');
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            ElNotification.error({
                title: 'Error',
                message: 'Failed to create customer'
            });
        }
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <section id="customer-create" class="p-4">
        <div class="mb-4">
            <el-button type="primary" @click="router.push('/customer')">
                <el-icon class="mr-1">
                    <ArrowLeft />
                </el-icon>
                Back to Customer List
            </el-button>
        </div>

        <el-card class="box-card" shadow="hover">
            <template #header>
                <div class="text-center">
                    <h4 class="text-gray-900 m-0">Add New Customer</h4>
                </div>
            </template>

            <el-form 
                :model="form" 
                label-position="top" 
                @submit.prevent="customerInsert"
                class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4"
            >
                <el-form-item label="Full Name" :error="errors.name?.[0]">
                    <el-input v-model="form.name" placeholder="Enter full name" />
                </el-form-item>

                <el-form-item label="Email" :error="errors.email?.[0]">
                    <el-input v-model="form.email" placeholder="Enter email address" type="email" />
                </el-form-item>

                <el-form-item label="Phone Number" :error="errors.phone?.[0]">
                    <el-input v-model="form.phone" placeholder="Enter phone number" />
                </el-form-item>

                <el-form-item label="Address" :error="errors.address?.[0]">
                    <el-input v-model="form.address" placeholder="Enter address" />
                </el-form-item>

                <el-form-item label="Customer Image" :error="errors.image?.[0]" class="md:col-span-2">
                    <div class="flex items-start gap-4">
                        <div class="flex-1">
                            <input 
                                type="file" 
                                @change="onFileSelected" 
                                class="form-control" 
                                accept="image/*"
                            >
                            <div class="text-xs text-gray-500 mt-1">
                                Supported formats: JPG, PNG, GIF. Max size: 2MB
                            </div>
                        </div>
                        <div v-if="imageUrl" class="w-24 h-24 border rounded overflow-hidden">
                            <img :src="imageUrl" class="w-full h-full object-cover" />
                        </div>
                        <div v-else class="w-24 h-24 border rounded flex items-center justify-center bg-gray-100">
                            <el-icon :size="24" class="text-gray-400">
                                <Picture />
                            </el-icon>
                        </div>
                    </div>
                </el-form-item>

                <div class="md:col-span-2 flex justify-center mt-4">
                    <el-button 
                        type="primary" 
                        native-type="submit" 
                        :loading="submitting"
                        class="w-40"
                    >
                        Create Customer
                    </el-button>
                </div>
            </el-form>
        </el-card>
    </section>
</template>

<style scoped>
.box-card {
    max-width: 1000px;
    margin: 0 auto;
}
</style>
