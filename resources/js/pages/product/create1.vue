<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../Helper/toaster';
import axios from 'axios';
import { Close } from '@element-plus/icons-vue';

const toastr = useToastr();
const router = useRouter();

const form = ref({
    name: '',
    category_id: '',
    selling_price: '',
    code: '',
    quantity: '',
    supplier_id: '',
    buying_date: '',
    image: null
});

const errors = ref({});
const categoriesData = ref([]);
const suppliersData = ref([]);
const imageUrl = ref('');

// Get categories and suppliers on mount
onMounted(() => {
    getCategories();
    getSuppliers();
});

const getCategories = async () => {
    try {
        const res = await axios.get('/api/categories');
        categoriesData.value = res.data;
    } catch (error) {
        console.error('Error fetching categories:', error);
    }
};

const getSuppliers = async () => {
    try {
        const res = await axios.get('/api/suppliers');
        suppliersData.value = res.data;
    } catch (error) {
        console.error('Error fetching suppliers:', error);
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

const handleRemoveImage = () => {
    form.value.image = null;
    imageUrl.value = '';
};
</script>

<template>
    <el-main class="product-create p-6">
        <el-page-header @back="router.push('/product')" content="Create Product" class="mb-8" />

        <el-card shadow="hover" class="max-w-4xl mx-auto">
            <template #header>
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900">Add New Product</h3>
                </div>
            </template>

            <el-form @submit.prevent="productInsert" label-position="top" :model="form" class="p-4">
                <el-row :gutter="24">
                    <el-col :span="12">
                        <el-form-item label="Product Name" :error="errors.name?.[0]">
                            <el-input v-model="form.name" placeholder="Enter product name" clearable />
                        </el-form-item>
                    </el-col>

                    <el-col :span="12">
                        <el-form-item label="Category" :error="errors.category_id?.[0]">
                            <el-select v-model="form.category_id" placeholder="Select category" class="w-full"
                                clearable>
                                <el-option v-for="cat in categoriesData" :key="cat.id" :label="cat.name"
                                    :value="cat.id" />
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>

                <el-row :gutter="24">
                    <el-col :span="12">
                        <el-form-item label="Selling Price" :error="errors.selling_price?.[0]">
                            <el-input v-model="form.selling_price" placeholder="Enter selling price" type="number"
                                min="0" step="0.01">
                                <template #prefix>
                                    <span>$</span>
                                </template>
                            </el-input>
                        </el-form-item>
                    </el-col>

                    <el-col :span="12">
                        <el-form-item label="Product Code" :error="errors.code?.[0]">
                            <el-input v-model="form.code" placeholder="Enter product code" clearable />
                        </el-form-item>
                    </el-col>
                </el-row>

                <el-row :gutter="24">
                    <el-col :span="12">
                        <el-form-item label="Quantity" :error="errors.quantity?.[0]">
                            <el-input v-model="form.quantity" placeholder="Enter quantity" :min="0"
                                class="w-full" />
                        </el-form-item>
                    </el-col>

                    <el-col :span="12">
                        <el-form-item label="Supplier" :error="errors.supplier_id?.[0]">
                            <el-select v-model="form.supplier_id" placeholder="Select supplier" class="w-full"
                                clearable>
                                <el-option v-for="supplier in suppliersData.data" :key="supplier.id" :label="supplier.name"
                                    :value="supplier.id" />
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>


                <el-row :gutter="24">
                    <el-col :span="12">
                        <el-form-item label="Buying Date" :error="errors.buying_date?.[0]">
                            <el-input v-model="form.buying_date" placeholder="Enter buying date" type="date"
                                min="0" step="0.01">
                            </el-input>
                        </el-form-item>
                    </el-col>

                    <el-col :span="12">
                        <el-form-item label="Product Image" :error="errors.image">
                            <el-col :span="7">
                                <el-upload class="image-uploader" action="#" :auto-upload="false"
                                    :show-file-list="false" :on-change="handleImageUpload"
                                    :on-remove="handleRemoveImage">
                                    <el-button type="primary" plain>
                                        <i class="el-icon-upload"></i> Click to upload
                                    </el-button>
                                    <template #tip>
                                        <div class="el-upload__tip">
                                            JPEG/PNG/GIF, max 2MB
                                        </div>
                                    </template>
                                </el-upload>
                            </el-col>

                            <el-col :span="17" v-if="imageUrl">
                                <div class="image-preview-wrapper">
                                    <el-image :src="imageUrl" class="uploaded-image"
                                        :style="{ width: '70px', height: '70px' }" fit="cover" lazy />
                                    <el-button type="danger" size="small" circle class="remove-image-btn"
                                        @click="handleRemoveImage">
                                        <el-icon>
                                            <Close />
                                        </el-icon>
                                    </el-button>
                                </div>
                            </el-col>


                        </el-form-item>
                    </el-col>
                </el-row>

                <el-form-item class="text-center mt-6">
                    <el-button type="primary" native-type="submit" :loading="isLoading" class="px-8">
                        {{ isLoading ? 'Submitting...' : 'Submit' }}
                    </el-button>
                </el-form-item>
            </el-form>
        </el-card>
    </el-main>
</template>

<style scoped>
.product-create {
    background-color: #f5f7fa;
}

.el-card {
    border-radius: 8px;
}

.el-form-item__label {
    font-weight: 500;
}

.el-upload__tip {
    font-size: 12px;
    color: #909399;
    margin-top: 4px;
}

.image-preview-wrapper {
    position: relative;
    display: inline-block;
}

.uploaded-image {
    border-radius: 4px;
    object-fit: cover;
}

.remove-image-btn {
    position: absolute;
    top: -8px;
    right: -8px;
    z-index: 10;
    padding: 0;
}
</style>
