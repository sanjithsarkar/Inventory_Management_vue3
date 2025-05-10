<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useToastr } from '../../Helper/toaster';
import axios from 'axios';

// Element Plus components
import {
  ElCard,
  ElForm,
  ElFormItem,
  ElInput,
  ElInputNumber,
  ElSelect,
  ElOption,
  ElUpload,
  ElButton,
  ElImage,
  ElMessageBox
} from 'element-plus';

const toastr = useToastr();
const router = useRouter();
const route = useRoute();

const form = ref({
  name: '',
  category_id: '',
  quantity: '',
  image: null
});

const errors = ref({});
const categoriesData = ref([]);
const imageUrl = ref('');
const id = route.params.id;

const getCategories = async () => {
  try {
    const res = await axios.get('/api/categories');
    categoriesData.value = res.data;
  } catch (error) {
    console.error('Error fetching categories:', error);
  }
};

const getProduct = async () => {
  try {
    const response = await axios.get(`/api/products/${id}`);
    form.value = response.data;
    // Set existing image URL if available
    if (response.data.image_url) {
      imageUrl.value = response.data.image_url;
    }
  } catch (error) {
    console.error('Error fetching product:', error);
  }
};

onMounted(() => {
  getCategories();
  getProduct();
});

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
    return false;
  }

  form.value.image = file.raw;
  imageUrl.value = URL.createObjectURL(file.raw);
  return false;
};

const handleRemoveImage = () => {
  ElMessageBox.confirm('Are you sure to remove this image?', 'Warning', {
    confirmButtonText: 'OK',
    cancelButtonText: 'Cancel',
    type: 'warning'
  }).then(() => {
    if (imageUrl.value) {
      URL.revokeObjectURL(imageUrl.value);
    }
    imageUrl.value = '';
    form.value.image = null;
    if (errors.value.image) delete errors.value.image;
  }).catch(() => {});
};

const productUpdate = async () => {
  try {
    const formData = new FormData();
    Object.entries(form.value).forEach(([key, value]) => {
      if (value !== null && value !== undefined) {
        formData.append(key, value);
      }
    });
    formData.append('_method', 'PUT');

    const res = await axios.post(`api/products/${id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    if (res.data.errors) {
      errors.value = res.data.errors;
    } else {
      router.push({ path: '/product' });
      toastr.success('Product Updated Successfully!');
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      toastr.error('An error occurred while updating the product');
      console.error('Error:', error);
    }
  }
};
</script>

<template>
  <section id="Product-update" class="p-4">
    <div class="mb-4">
      <el-button type="primary" @click="router.push('/product')">
        <i class="el-icon-arrow-left"></i> Back to Product List
      </el-button>
    </div>

    <el-card class="box-card" shadow="hover">
      <template #header>
        <div class="text-center">
          <h4 class="text-gray-900 m-0">Update Product</h4>
        </div>
      </template>

      <el-form
        :model="form"
        label-position="top"
        @submit.prevent="productUpdate"
        class="product-form"
      >
        <div class="row">
          <div class="col-md-6">
            <el-form-item label="Product Name" :error="errors.name?.[0]">
              <el-input
                v-model="form.name"
                placeholder="Enter product name"
                clearable
              />
            </el-form-item>
          </div>

          <div class="col-md-6">
            <el-form-item label="Category" :error="errors.category_id?.[0]">
              <el-select
                v-model="form.category_id"
                placeholder="Select category"
                class="w-100"
                clearable
              >
                <el-option
                  v-for="cat in categoriesData"
                  :key="cat.id"
                  :label="cat.name"
                  :value="cat.id"
                />
              </el-select>
            </el-form-item>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <el-form-item label="Quantity" :error="errors.quantity?.[0]">
              <el-input-number
                v-model="form.quantity"
                placeholder="Enter quantity"
                :min="0"
                class="w-100"
              />
            </el-form-item>
          </div>

          <div class="col-md-6">
            <el-form-item label="Product Image" :error="errors.image">
              <div class="image-upload-container">
                <div class="upload-column">
                  <el-upload
                    class="image-uploader"
                    action="#"
                    :auto-upload="false"
                    :show-file-list="false"
                    :on-change="handleImageUpload"
                  >
                    <el-button type="primary" plain>
                      <i class="el-icon-upload"></i> Change Image
                    </el-button>
                    <template #tip>
                      <div class="el-upload__tip">
                        JPEG/PNG/GIF, max 2MB
                      </div>
                    </template>
                  </el-upload>
                </div>
                <div class="preview-column" v-if="imageUrl">
                  <div class="image-preview-wrapper">
                    <el-image :src="imageUrl" class="uploaded-image" fit="cover" />
                    <el-button
                      class="remove-image-btn"
                      type="danger"
                      circle
                      size="small"
                      @click="handleRemoveImage"
                    >
                      <i class="el-icon-close"></i>
                    </el-button>
                  </div>
                </div>
              </div>
            </el-form-item>
          </div>
        </div>

        <div class="text-center mt-4">
          <el-button
            type="primary"
            native-type="submit"
            class="submit-btn"
          >
            Update Product
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

.image-upload-container {
  display: flex;
  align-items: center;
}

.upload-column {
  flex: 1;
  padding-right: 15px;
}

.preview-column {
  flex: 1;
}

.uploaded-image {
  width: 120px;
  height: 120px;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  object-fit: contain;
}

.image-preview-wrapper {
  position: relative;
  display: inline-block;
}

.remove-image-btn {
  position: absolute;
  top: -10px;
  right: -10px;
  padding: 6px !important;
  z-index: 10;
}

.submit-btn {
  width: 200px;
  padding: 12px;
}

.el-form-item {
  margin-bottom: 22px;
}

.el-select, .el-input-number {
  width: 100%;
}

@media (max-width: 768px) {
  .image-upload-container {
    flex-direction: column;
  }
  
  .upload-column {
    padding-right: 0;
    margin-bottom: 15px;
  }
  
  .row > div {
    width: 100%;
  }
}
</style>