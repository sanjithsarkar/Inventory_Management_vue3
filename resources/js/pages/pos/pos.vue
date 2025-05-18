<template>
    <div class="pos-page">
        <el-row :gutter="20" class="pos-container">
            <!-- Left Column - Cart -->
            <el-col :span="12">
                <el-card class="cart-card" shadow="hover">
                    <template #header>
                        <div class="card-header">
                            <div class="header-title-section">
                                <el-icon class="header-icon"><ShoppingCart /></el-icon>
                                <span class="header-title">Order Summary</span>
                            </div>
                            <el-button type="primary" size="small" @click="showCustomerDialog">
                                <el-icon><User /></el-icon> Add Customer
                            </el-button>
                        </div>
                    </template>

                    <!-- Selected Customer Info -->
                    <div v-if="selectedCustomer" class="selected-customer-info">
                        <el-avatar :size="32" :src="selectedCustomer.image_url || ''" class="customer-avatar">
                            {{ selectedCustomer.name ? selectedCustomer.name.charAt(0).toUpperCase() : 'C' }}
                        </el-avatar>
                        <div class="customer-details">
                            <span class="customer-name">{{ selectedCustomer.name }}</span>
                            <span class="customer-contact">{{ selectedCustomer.phone || selectedCustomer.email }}</span>
                        </div>
                        <el-button 
                            type="danger" 
                            size="small" 
                            plain 
                            circle
                            @click="data.customer_id = null"
                        >
                            <el-icon><Close /></el-icon>
                        </el-button>
                    </div>

                    <!-- Cart Items Table -->
                    <div class="cart-table-container">
                        <el-empty v-if="!posData.length" description="No items in cart" />
                        <el-table 
                            v-else 
                            :data="posData" 
                            border 
                            style="width: 100%" 
                            size="small"
                            :header-cell-style="{ background: '#f5f7fa', color: '#606266' }"
                        >
                            <el-table-column type="index" width="50" label="#" />
                            <el-table-column prop="name" label="Name" show-overflow-tooltip />
                            <el-table-column label="Qty" width="150">
                                <template #default="{ row }">
                                    <div class="quantity-controls">
                                        <el-button size="small" :icon="Minus" circle
                                            @click="decreaseQuantity(row.id, row.quantity)" :disabled="row.quantity <= 1" />
                                        <el-input v-model="row.quantity" :min="1" :max="500" size="small"
                                            @keyup="() => increaseQuantity(row.id, 'dynamic', row.quantity)"
                                            controls-position="right" class="quantity-input" />
                                        <el-button size="small" :disabled="Number(row.quantity) >= Number(row.product.quantity)"
                                            :icon="Plus" circle @click="increaseQuantity(row.id)" />
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column prop="price" label="Price" width="100">
                                <template #default="{ row }">
                                    {{ formatCurrency(row.price) }}
                                </template>
                            </el-table-column>
                            <el-table-column label="Total" width="120">
                                <template #default="{ row }">
                                    <el-tag type="success">{{ formatCurrency(row.sub_total) }}</el-tag>
                                </template>
                            </el-table-column>
                            <el-table-column label="Action" width="70">
                                <template #default="{ row }">
                                    <el-button type="danger" size="small" :icon="Delete" circle
                                        @click="deleteItem(row.id)" />
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>

                    <!-- Order Summary -->
                    <div class="order-summary-section">
                        <el-divider content-position="center">Order Summary</el-divider>
                        <el-descriptions :column="1" border class="summary-section">
                            <el-descriptions-item label="Total Quantity">
                                <span class="summary-value">{{ totalQuantity }}</span>
                            </el-descriptions-item>
                            <el-descriptions-item label="Sub Total">
                                <span class="summary-value">{{ totalSubTotal }}</span>
                            </el-descriptions-item>
                            <el-descriptions-item label="Discount (%)">
                                <div class="discount-control">
                                    <el-input-number v-model="discount" :min="0" :max="100" size="default" controls-position="right" />
                                    <span class="discount-amount">({{ discountPayment }})</span>
                                </div>
                            </el-descriptions-item>
                            <el-descriptions-item label="Total Amount">
                                <span class="summary-value total-amount">{{ totalAmount }}</span>
                            </el-descriptions-item>
                        </el-descriptions>
                    </div>

                    <!-- Payment Form -->
                    <div class="payment-section">
                        <el-divider content-position="center">Payment Details</el-divider>
                        <el-form @submit.prevent="orderDone" class="payment-form">
                            <el-descriptions border :column="1">
                                <el-descriptions-item label="Select Customer">
                                    <el-select 
                                        v-model="data.customer_id" 
                                        placeholder="Select Customer" 
                                        clearable
                                        filterable
                                        class="customer-select"
                                    >
                                        <el-option 
                                            v-for="customer in customerData.data" 
                                            :key="customer.id"
                                            :label="customer.name" 
                                            :value="customer.id" 
                                        >
                                            <div class="customer-option">
                                                <el-avatar :size="24" :src="customer.image_url || ''" class="customer-option-avatar">
                                                    {{ customer.name ? customer.name.charAt(0).toUpperCase() : 'C' }}
                                                </el-avatar>
                                                <span>{{ customer.name }}</span>
                                            </div>
                                        </el-option>
                                    </el-select>
                                </el-descriptions-item>
                                <el-descriptions-item label="Payment Amount">
                                    <div class="payment-amount-container">
                                        <el-input 
                                            v-model="paymentReceive" 
                                            :min="0" 
                                            :max="totalAmount" 
                                            :precision="2"
                                            placeholder="Enter payment amount"
                                        >
                                            <template #prefix>$</template>
                                        </el-input>
                                        <el-button 
                                            type="primary" 
                                            size="small" 
                                            @click="setFullAmount" 
                                            class="full-amount-btn"
                                        >
                                            Full Amount
                                        </el-button>
                                    </div>
                                    <div class="payment-hint">
                                        <small>Payment amount automatically updates with the total</small>
                                    </div>
                                </el-descriptions-item>
                                <el-descriptions-item label="Due Amount">
                                    <el-tag :type="remainingPayment > 0 ? 'danger' : 'success'" size="large">
                                        {{ formatCurrency(remainingPayment) }}
                                    </el-tag>
                                </el-descriptions-item>
                                <input type="hidden" v-model="data.duePayment" />
                                <input type="hidden" v-model="data.quantity" />
                                <input type="hidden" v-model="data.subTotal" />
                                <input type="hidden" v-model="data.discount" />
                                <input type="hidden" v-model="data.discountPayment" />
                                <input type="hidden" v-model="data.totalAmount" />
                                <input type="hidden" v-model="data.paymentReceive" />
                                <input type="hidden" v-model="data.duePayment" />
                                <input type="hidden" v-model="data.price" />
                                <el-descriptions-item label="Payment Method">
                                    <el-select v-model="data.payby" placeholder="Select Payment Method" class="payment-method-select">
                                        <el-option label="Hand Cash" value="HandCash">
                                            <div class="payment-option">
                                                <el-icon><Money /></el-icon>
                                                <span>Hand Cash</span>
                                            </div>
                                        </el-option>
                                        <el-option label="Bkash" value="Bkash">
                                            <div class="payment-option">
                                                <el-icon><CreditCard /></el-icon>
                                                <span>Bkash</span>
                                            </div>
                                        </el-option>
                                        <el-option label="Cheque" value="Cheaque">
                                            <div class="payment-option">
                                                <el-icon><Tickets /></el-icon>
                                                <span>Cheque</span>
                                            </div>
                                        </el-option>
                                        <el-option label="Gift Card" value="GiftCard">
                                            <div class="payment-option">
                                                <el-icon><Present /></el-icon>
                                                <span>Gift Card</span>
                                            </div>
                                        </el-option>
                                        <el-option label="Stripe" value="stripe">
                                            <div class="payment-option">
                                                <el-icon><CreditCard /></el-icon>
                                                <span>Stripe</span>
                                            </div>
                                        </el-option>
                                        <el-option label="PayPal" value="paypal">
                                            <div class="payment-option">
                                                <el-icon><CreditCard /></el-icon>
                                                <span>PayPal</span>
                                            </div>
                                        </el-option>
                                    </el-select>
                                </el-descriptions-item>
                                <el-descriptions-item>
                                    <div class="payment-buttons">
                                        <el-button v-if="data.payby === 'stripe'" type="primary" @click="payByStripe" :disabled="!posData.length">
                                            <el-icon><CreditCard /></el-icon> Pay with Stripe
                                        </el-button>
                                        <el-button v-else-if="data.payby === 'paypal'" type="primary" @click="payByPaypal" :disabled="!posData.length">
                                            <el-icon><CreditCard /></el-icon> Pay with PayPal
                                        </el-button>
                                        <el-button v-else type="success" native-type="submit" :disabled="!posData.length">
                                            <el-icon><Check /></el-icon> Complete Order
                                        </el-button>
                                    </div>
                                </el-descriptions-item>
                            </el-descriptions>
                        </el-form>
                    </div>
                </el-card>
            </el-col>

            <!-- Right Column - Products -->
            <el-col :span="12">
                <el-card class="products-card" shadow="hover">
                    <template #header>
                        <div class="card-header">
                            <div class="header-title-section">
                                <el-icon class="header-icon"><Goods /></el-icon>
                                <span class="header-title">Products</span>
                            </div>
                            <div class="product-filters">
                                <el-select 
                                    v-model="selectedCategory" 
                                    placeholder="Select Category" 
                                    clearable 
                                    style="width: 160px;"
                                >
                                    <el-option 
                                        v-for="category in categoryData" 
                                        :key="category.id" 
                                        :label="category.name"
                                        :value="category.id" 
                                    />
                                </el-select>
                                <el-input 
                                    v-model="searchQuery" 
                                    placeholder="Search Products" 
                                    clearable
                                    style="width: 180px"
                                >
                                    <template #prefix>
                                        <el-icon><Search /></el-icon>
                                    </template>
                                </el-input>
                            </div>
                        </div>
                    </template>

                    <div class="products-container">
                        <el-empty v-if="!productData.length" description="No products found" />
                        <el-row v-else :gutter="15">
                            <el-col v-for="product in productData" :key="product.id" :xs="12" :sm="8" :md="6">
                                <el-card 
                                    shadow="hover" 
                                    class="product-card" 
                                    :class="{ 'out-of-stock': product.quantity < 1 }"
                                    @click="product.quantity >= 1 && addToCart(product.id)"
                                >
                                    <div class="product-image">
                                        <el-image :src="product.image_url" fit="cover" :preview-src-list="[product.image_url]">
                                            <template #error>
                                                <div class="image-error">
                                                    <el-icon><Picture /></el-icon>
                                                </div>
                                            </template>
                                        </el-image>
                                    </div>
                                    <div class="product-info">
                                        <h6 class="product-name">{{ product.name }}</h6>
                                        <div class="price-stock">
                                            <el-tag type="success">{{ formatCurrency(product.selling_price) }}</el-tag>
                                            <el-tag :type="product.quantity >= 1 ? 'success' : 'danger'" size="small">
                                                {{ product.quantity >= 1 ? `In Stock (${product.quantity})` : 'Out of Stock' }}
                                            </el-tag>
                                        </div>
                                    </div>
                                </el-card>
                            </el-col>
                        </el-row>
                    </div>
                    
                    <div class="pagination-container">
                        <el-pagination 
                            v-model:current-page="currentPage" 
                            :page-size="pageSize" 
                            :total="totalProducts"
                            layout="prev, pager, next" 
                            background 
                        />
                    </div>
                </el-card>
            </el-col>
        </el-row>

        <!-- Customer Dialog -->
        <el-dialog
            v-model="customerDialogVisible"
            title="Add New Customer"
            width="50%"
            :close-on-click-modal="false"
        >
            <el-form 
                :model="customerForm" 
                label-position="top" 
                @submit.prevent="addCustomer"
                class="grid grid-cols-1 md:grid-cols-2 gap-4"
            >
                <el-form-item label="Full Name" :error="customerErrors.name?.[0]">
                    <el-input v-model="customerForm.name" placeholder="Enter full name" />
                </el-form-item>

                <el-form-item label="Email" :error="customerErrors.email?.[0]">
                    <el-input v-model="customerForm.email" placeholder="Enter email address" type="email" />
                </el-form-item>

                <el-form-item label="Phone Number" :error="customerErrors.phone?.[0]">
                    <el-input v-model="customerForm.phone" placeholder="Enter phone number" />
                </el-form-item>

                <el-form-item label="Address" :error="customerErrors.address?.[0]">
                    <el-input v-model="customerForm.address" placeholder="Enter address" />
                </el-form-item>

                <el-form-item label="Profile Image" :error="customerErrors.image?.[0]" class="md:col-span-2">
                    <div class="image-upload-container">
                        <div class="upload-section">
                            <el-upload
                                class="image-uploader"
                                action="#"
                                :auto-upload="false"
                                :show-file-list="false"
                                :on-change="onCustomerImageSelected"
                            >
                                <el-button type="primary" plain>
                                    <el-icon class="mr-1"><Upload /></el-icon>
                                    {{ customerImageUrl ? 'Change Image' : 'Upload Image' }}
                                </el-button>
                                <template #tip>
                                    <div class="el-upload__tip text-xs text-gray-500">
                                        Supported formats: JPG, PNG, GIF. Max size: 2MB
                                    </div>
                                </template>
                            </el-upload>
                        </div>
                        
                        <div class="preview-section">
                            <div v-if="customerImageUrl" class="image-preview">
                                <el-image 
                                    :src="customerImageUrl" 
                                    fit="cover" 
                                    class="preview-image"
                                />
                                <el-button 
                                    type="danger" 
                                    size="small" 
                                    circle
                                    class="remove-btn"
                                    @click="() => {
                                        customerImageUrl = null;
                                        customerForm.image = null;
                                    }"
                                >
                                    <el-icon><Delete /></el-icon>
                                </el-button>
                            </div>
                            <div v-else class="empty-preview">
                                <el-icon :size="24" class="text-gray-400">
                                    <Picture />
                                </el-icon>
                            </div>
                        </div>
                    </div>
                </el-form-item>
            </el-form>

            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="customerDialogVisible = false">Cancel</el-button>
                    <el-button type="primary" @click="addCustomer" :loading="submittingCustomer">
                        {{ submittingCustomer ? 'Adding...' : 'Add Customer' }}
                    </el-button>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, watchEffect } from 'vue';
import { 
    User, Delete, Search, Picture, CloseBold, Minus, Plus, Upload, 
    ShoppingCart, Goods, CreditCard, Money, Tickets, Present, Check, Close 
} from '@element-plus/icons-vue';
import { debounce } from 'lodash';
import { useToastr } from '../../Helper/toaster';
import axios from 'axios';
import { loadStripe } from '@stripe/stripe-js';
import { useCurrency } from '../../composables/useCurrency';

const toastr = useToastr();
const { formatCurrency } = useCurrency();

// Data properties
const posData = ref([]);
const customerData = ref([]);
const productData = ref([]);
const categoryData = ref([]);
const selectedCategory = ref('');
const searchQuery = ref('');
const discount = ref(0);
const paymentReceive = ref(0);
const data = ref({
    quantity: 0,
    subTotal: 0,
    discount: 0,
    discountPayment: 0,
    totalAmount: 0,
    paymentReceive: 0,
    duePayment: 0,
    customer_id: null,
    payby: 'HandCash',
    price: 0
});

// Customer dialog
const customerDialogVisible = ref(false);
const customerForm = ref({
    name: '',
    email: '',
    phone: '',
    address: '',
    image: null
});
const customerErrors = ref({});
const customerImageUrl = ref(null);
const submittingCustomer = ref(false);

// Show customer dialog
const showCustomerDialog = () => {
    resetCustomerForm();
    customerDialogVisible.value = true;
};

// Reset customer form
const resetCustomerForm = () => {
    customerForm.value = {
        name: '',
        email: '',
        phone: '',
        address: '',
        image: null
    };
    customerImageUrl.value = null;
    customerErrors.value = {};
};

// Handle customer image selection
const onCustomerImageSelected = (file) => {
    customerForm.value.image = file.raw;
    customerImageUrl.value = URL.createObjectURL(file.raw);
};

// Add new customer
const addCustomer = async () => {
    try {
        submittingCustomer.value = true;
        const formData = new FormData();
        
        // Add image if selected
        if (customerForm.value.image) {
            formData.append('image', customerForm.value.image);
        }
        
        // Add other form fields
        Object.entries(customerForm.value).forEach(([key, value]) => {
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
            customerErrors.value = response.data.errors;
        } else {
            toastr.success('Customer added successfully!');
            customerDialogVisible.value = false;
            await getCustomers(); // Refresh customer list
            data.value.customer_id = response.data.id; // Select the newly added customer
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            customerErrors.value = error.response.data.errors;
        } else {
            toastr.error('Failed to add customer');
        }
    } finally {
        submittingCustomer.value = false;
    }
};

// Computed properties
const totalQuantity = computed(() => {
    return formatCurrency(posData.value.reduce((sum, item) => sum + parseFloat(item.quantity), 0));
});

const totalSubTotal = computed(() => {
    return formatCurrency(posData.value.reduce((sum, item) => sum + (parseFloat(item.quantity) * parseFloat(item.price)), 0));
});

const discountPayment = computed(() => {
    return formatCurrency(totalSubTotal.value * discount.value / 100);
});

const totalAmount = computed(() => {
    return formatCurrency(totalSubTotal.value - discountPayment.value);
});

const remainingPayment = computed(() => {
    return formatCurrency(paymentReceive.value ? totalAmount.value - parseFloat(paymentReceive.value) : totalAmount.value);
});

// Methods
const getCategories = async () => {
    try {
        const response = await axios.get('/api/categories');
        categoryData.value = response.data;
    } catch (error) {
        console.error('Error fetching categories:', error);
    }
};

const currentPage = ref(1)
const pageSize = ref(12)
const totalProducts = ref(0)

const getProducts = async () => {
    try {
        const response = await axios.get('/api/products', {
            params: {
                query: searchQuery.value,
                category: selectedCategory.value,
                page: currentPage.value,
                per_page: pageSize.value,
            }
        })
        productData.value = response.data.data // Assuming paginated result
        console.log('products', response.data)
        totalProducts.value = response.data.total // Backend should send this
    } catch (error) {
        console.error('Error fetching products:', error)
    }
}

watch([searchQuery, selectedCategory], () => {
    currentPage.value = 1 // Reset to first page on filter change
    getProducts()
})

watch(currentPage, () => {
    getProducts()
})


const getCustomers = async () => {
    try {
        const response = await axios.get('/api/customers');
        customerData.value = response.data;
    } catch (error) {
        console.error('Error fetching customers:', error);
    }
};

const getPOSData = async () => {
    try {
        const response = await axios.get('/api/get/cart');
        posData.value = response.data;
    } catch (error) {
        console.error('Error fetching cart data:', error);
    }
};

const methodRefreshByDebounce = debounce(() => {
    getPOSData();
}, 300);

const productsRefreshByDebounce = debounce(() => {
    getProducts();
}, 300);

const addToCart = async (id) => {
    try {
        const response = await axios.get(`/api/add/cart/${id}`);
        if (response.data.errors) {
            toastr.error(response.data.errors);
        } else {
            getPOSData();
            toastr.success('Product added to cart successfully!');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
    }
};

const increaseQuantity = (id, dynamicTest, quantity) => {
    console.log('test', dynamicTest);
    axios.get('/api/increase/cart/' + id, {
        params: {
            dynamic: dynamicTest,
            quantity: quantity || 1
        }
    })
        .then(() => {
            methodRefreshByDebounce();
            toastr.success('Item Inserted Successfully!!');
        })
        .catch()
}



//----------- Decrease Item --------------

const decreaseQuantity = (id, quantity) => {
    axios.get('/api/decrease/cart/' + id)
        .then(() => {
            if (quantity <= 1) {
                return toastr.error('Quantity cannot be less than 1');
            } else {
                methodRefreshByDebounce();
                toastr.success('Item Decreased Successfully!!');
            }
        })
        .catch()
}

// const updateQuantity = async (item) => {
//     try {
//         await axios.get(`/api/increase/cart/${item.id}`);
//         getPOSData();
//         toastr.success('Quantity updated successfully!');
//     } catch (error) {
//         console.error('Error updating quantity:', error);
//     }
// };

const deleteItem = async (id) => {
    try {
        await axios.delete(`/api/delete/cart/${id}`);
        getPOSData();
        toastr.success('Item removed from cart successfully!');
    } catch (error) {
        console.error('Error deleting item:', error);
    }
};

const payByStripe = async () => {
    try {
        const stripe = await loadStripe('pk_test_51MwQwJIqzT5sBDbDq2bKPnZUycLX9KLYAVUjVL6MyFh4xccFXjaC9vftaOIFQJGvoHdWcbfC7rDt6Y13OwzkDSyb00WRk2Iwpz');
        const response = await axios.post('/api/stripe/payment', data.value);
        window.location.href = response.data.url;
    } catch (error) {
        console.error('Error processing Stripe payment:', error);
        toastr.error('Failed to process Stripe payment');
    }
};

const payByPaypal = async () => {
    try {
        const response = await axios.post('/api/paypal/payment', data.value);
        if (response.data.paypalUrl) {
            window.location.href = response.data.paypalUrl;
        } else {
            toastr.error('PayPal URL not found');
        }
    } catch (error) {
        console.error('Error processing PayPal payment:', error);
        toastr.error('Failed to process PayPal payment');
    }
};

watchEffect(() => {
    data.value.quantity = totalQuantity.value;
    data.value.subTotal = totalSubTotal.value;
    data.value.discount = discount.value;
    data.value.discountPayment = discountPayment.value;
    data.value.totalAmount = totalAmount.value;
    data.value.paymentReceive = paymentReceive.value;
    data.value.duePayment = remainingPayment.value;
    data.value.price = productData.value.price;
});


const orderDone = async () => {
    try {
        await axios.post('/api/order/done', data.value);
        toastr.success('Order completed successfully!');
        // Reset form
        discount.value = 0;
        paymentReceive.value = 0;
        data.value.customer_id = null;
        data.value.payby = 'HandCash';
        methodRefreshByDebounce();
        productsRefreshByDebounce();
    } catch (error) {
        console.error('Error completing order:', error);
        toastr.error('Failed to complete order');
    }
};

// Watchers
watch([searchQuery, selectedCategory], debounce(() => {
    getProducts();
}, 300));

// Lifecycle hooks
onMounted(() => {
    getProducts();
    getCustomers();
    getPOSData();
    getCategories();
});

const selectedCustomer = computed(() => {
    if (!data.value.customer_id || !customerData.value.data) return null;
    return customerData.value.data.find(customer => customer.id === data.value.customer_id);
});

// Watch totalAmount and update paymentReceive automatically when it changes
watch(totalAmount, (newValue) => {
    paymentReceive.value = newValue;
});

// Add a button to set full amount
const setFullAmount = () => {
    paymentReceive.value = totalAmount.value;
};
</script>

<style scoped>
.pos-page {
    padding: 16px;
    background-color: #f5f7fa;
    min-height: calc(100vh - 64px);
}

.pos-container {
    margin: 0;
    height: calc(100vh - 96px);
}

.cart-card,
.products-card {
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 12px;
    border-bottom: 1px solid #ebeef5;
}

.header-title-section {
    display: flex;
    align-items: center;
    gap: 8px;
}

.header-icon {
    font-size: 20px;
    color: #409eff;
}

.header-title {
    font-weight: 600;
    font-size: 1.25rem;
    color: #303133;
}

.selected-customer-info {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background-color: #f5f7fa;
    border-radius: 4px;
    margin-bottom: 16px;
}

.customer-details {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.customer-name {
    font-weight: 500;
    color: #303133;
}

.customer-contact {
    font-size: 0.85rem;
    color: #606266;
}

.cart-table-container {
    flex: 1;
    overflow-y: auto;
    margin-bottom: 16px;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 4px;
}

.quantity-input {
    width: 70px !important;
    padding: 0 3px;
}

.order-summary-section {
    margin-top: 16px;
}

.summary-section {
    margin-top: 10px;
}

.summary-value {
    font-weight: 500;
}

.total-amount {
    font-size: 1.1rem;
    color: #409eff;
    font-weight: 600;
}

.discount-control {
    display: flex;
    align-items: center;
    gap: 10px;
}

.discount-amount {
    color: #f56c6c;
}

.payment-section {
    margin-top: 16px;
}

.payment-form {
    margin-top: 10px;
}

.customer-select,
.payment-method-select {
    width: 100%;
}

.customer-option,
.payment-option {
    display: flex;
    align-items: center;
    gap: 8px;
}

.customer-option-avatar {
    flex-shrink: 0;
}

.payment-buttons {
    display: flex;
    justify-content: flex-end;
}

.product-filters {
    display: flex;
    gap: 12px;
}

.products-container {
    flex: 1;
    overflow-y: auto;
    padding: 10px 0;
}

.product-card {
    margin-bottom: 15px;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    height: 100%;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.product-card.out-of-stock {
    opacity: 0.6;
    cursor: not-allowed;
}

.product-image {
    height: 80px;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f5f7fa;
    border-radius: 4px;
    overflow: hidden;
}

.product-info {
    padding-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.product-name {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 500;
    color: #303133;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.price-stock {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
}

.image-error {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    color: #c0c4cc;
}

.pagination-container {
    margin-top: 16px;
    display: flex;
    justify-content: center;
}

/* Customer dialog styles remain the same */
.image-upload-container {
    display: flex;
    align-items: center;
    gap: 20px;
}

.upload-section {
    flex: 1;
}

.preview-section {
    width: 80px;
}

.image-preview {
    position: relative;
    width: 60px;
    height: 60px;
    border: 1px solid #dcdfe6;
    border-radius: 4px;
    overflow: hidden;
}

.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.empty-preview {
    width: 60px;
    height: 60px;
    border: 1px dashed #dcdfe6;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f5f7fa;
}

.remove-btn {
    position: absolute;
    top: -8px;
    right: -8px;
    padding: 4px !important;
    z-index: 10;
}

.el-upload__tip {
    line-height: 1.2;
    margin-top: 5px;
}

.dialog-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* Fix the deep selector syntax */
:deep(.el-empty__image) {
    height: 75px !important;
}

.payment-amount-container {
    display: flex;
    gap: 10px;
    align-items: center;
}

.full-amount-btn {
    flex-shrink: 0;
}

.payment-hint {
    margin-top: 5px;
    color: #909399;
    font-size: 12px;
}
</style>
