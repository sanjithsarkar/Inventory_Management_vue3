<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../Helper/toaster';
import axios from 'axios';

// Element Plus components
import {
    ElCard,
    ElForm,
    ElFormItem,
    ElInput,
    ElSelect,
    ElOption,
    ElUpload,
    ElButton,
    ElImage
} from 'element-plus';

const toastr = useToastr();
const router = useRouter();

const form = ref({
    name: '',
    category_id: '',
    selling_price: '',
    code: '',
    quantity: '',
    image: null
});

const errors = ref({});
const categoriesData = ref([]);
const imageUrl = ref('');

// Get categories on mount
onMounted(() => {
    getCategories();
});

const getCategories = async () => {
    try {
        const res = await axios.get('/api/categories');
        categoriesData.value = res.data;
    } catch (error) {
        console.error('Error fetching categories:', error);
    }
};

const validateImageFile = (file) => {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    const maxSize = 2 * 1024 * 1024; // 2MB

    if (!allowedTypes.includes(file.type)) {
        return 'Invalid image format. Please upload JPEG, PNG, or GIF';
    }
    if (file.size > maxSize) {
        return 'Image size must be less than 2MB';
    }
    return null;
};

const handleImageUpload = (file) => {
    errors.value.image = null;

    const validationError = validateImageFile(file.raw);
    if (validationError) {
        errors.value.image = validationError;
        return false; // Prevent upload
    }

    form.value.image = file.raw;
    imageUrl.value = URL.createObjectURL(file.raw);
    return false; // We handle the upload manually
};

const productInsert = async () => {
    try {
        const formData = new FormData();
        Object.entries(form.value).forEach(([key, value]) => {
            if (value !== null && value !== undefined) {
                formData.append(key, value);
            }
        });

        const res = await axios.post('api/products', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        if (res.data.errors) {
            errors.value = res.data.errors;
        } else {
            router.push({ path: '/product' });
            toastr.success('Product Inserted Successfully!');
        }
    } catch (error) {
        if (error.response && error.response.data.errors) {
            errors.value = error.response.data.errors;
        } else {
            toastr.error('An error occurred while creating the product');
            console.error('Error:', error);
        }
    }
};
</script>

<template>
    <section id="Product-create" class="p-4">
        <div class="mb-4">
            <el-button type="primary" @click="router.push('/product')">
                <i class="el-icon-arrow-left"></i> Back to Product List
            </el-button>
        </div>

        <el-card class="box-card" shadow="hover">
            <template #header>
                <div class="text-center">
                    <h4 class="text-gray-900 m-0">Add New Product</h4>
                </div>
            </template>

            <el-form :model="form" label-position="top" @submit.prevent="productInsert" class="product-form">
                <div class="row">
                    <div class="col-md-6">
                        <el-form-item label="Product Name" :error="errors.name?.[0]">
                            <el-input v-model="form.name" placeholder="Enter product name" clearable />
                        </el-form-item>
                    </div>

                    <div class="col-md-6">
                        <el-form-item label="Category" :error="errors.category_id?.[0]">
                            <el-select v-model="form.category_id" placeholder="Select category" class="w-100" clearable>
                                <el-option v-for="cat in categoriesData" :key="cat.id" :label="cat.name"
                                    :value="cat.id" />
                            </el-select>
                        </el-form-item>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <el-form-item label="Selling Price" :error="errors.selling_price?.[0]">
                            <el-input v-model="form.selling_price" placeholder="Enter selling price" type="number"
                                min="0" clearable>
                                <template #prefix>$</template>
                            </el-input>
                        </el-form-item>
                    </div>

                    <div class="col-md-6">
                        <el-form-item label="Product Code" :error="errors.code?.[0]">
                            <el-input v-model="form.code" placeholder="Enter product code" clearable />
                        </el-form-item>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <el-form-item label="Supplier" :error="errors.supplier_id?.[0]">
                            <el-input v-model="form.supplier_id" placeholder="Enter supplier" type="number"
                                min="0" clearable>
                            </el-input>
                        </el-form-item>
                    </div>

                    <div class="col-md-6">
                        <el-form-item label="Product Code" :error="errors.code?.[0]">
                            <el-input v-model="form.code" placeholder="Enter product code" clearable />
                        </el-form-item>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <el-form-item label="Quantity" :error="errors.quantity?.[0]">
                            <el-input v-model="form.quantity" placeholder="Enter quantity" type="number" min="0"
                                clearable />
                        </el-form-item>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-4">
                                <el-form-item label="Product Image" :error="errors.image">
                                    <el-upload class="image-uploader" action="#" :auto-upload="false"
                                        :show-file-list="false" :on-change="handleImageUpload">

                                        <el-button type="primary" plain>
                                            <i class="el-icon-upload"></i> Click to upload
                                        </el-button>
                                        <template #tip>
                                            <div class="el-upload__tip">
                                                JPEG/PNG/GIF, max 2MB
                                            </div>
                                        </template>
                                    </el-upload>
                                </el-form-item>
                            </div>
                            <div class="col-md-8">
                                <el-form-item v-if="imageUrl">
                                    <el-image :src="imageUrl" class="uploaded-image" fit="cover" />
                                </el-form-item>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <el-button type="primary" native-type="submit" class="submit-btn">
                        Create Product
                    </el-button>
                </div>
            </el-form>
        </el-card>
    </section>
</template>

<style scoped>
.box-card {
    max-width: 1200px;
    margin: 0 auto;
}

.product-form {
    padding: 20px;
}

.uploaded-image {
    width: 120px;
    height: 120px;
    display: block;
    border: 1px dashed #d9d9d9;
    border-radius: 6px;
}

.image-uploader {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.submit-btn {
    width: 200px;
    padding: 12px;
}

.el-form-item {
    margin-bottom: 22px;
}

.el-select {
    width: 100%;
}

@media (max-width: 768px) {
    .row>div {
        width: 100%;
    }
}
</style>