<script setup>
import { ref, onMounted, watch, computed, reactive } from 'vue';
// import { Bootstrap5Pagination } from 'laravel-vue-pagination';
import { debounce } from 'lodash';
import { useToastr } from '../../Helper/toaster';
import { useRouter } from 'vue-router';
import axios from 'axios';
import router from '../../router';
import { loadStripe } from '@stripe/stripe-js';

const toastr = useToastr();
const productData = ref({});
const categoryData = ref({});
const selectedCategory = ref(null);
const searchQuery = ref(null);
const customerData = ref({});
const errors = ref({});
const form = ref({});
const posData = ref({});
const routerPath = useRouter();


console.log('selected category', selectedCategory.value);

const getCategories = () => {
    axios.get('/api/categories')
        .then(response => {
            console.log('category = ', response);
            categoryData.value = response;
        })
        .catch(res => {
            console.log(res.data);
        })
}


//----------  get all product -----------

const getProducts = () => {
    axios.get('/api/products', {
        params: {
            query: searchQuery.value,
            category: selectedCategory.value,
        }
    })
        .then(response => {
            productData.value = response.data;
        })
        .catch(res => {
            console.log(res.data);
        })
}

watch([searchQuery, selectedCategory], debounce(() => {
    getProducts();
    getCategories();
}, 300));


// -------------- getCustomer ------------------

const getCustomers = () => {
    axios.get('api/customers')
        .then(res => {
            customerData.value = res.data;
        })
        .catch()
}


//--------- Method Refresh by Debounce -----------

let methodRefreshByDebounce = debounce(() => {
    getPOSData();
}, 100)


// ------------ addToCart ----------------

const addToCart = (id) => {
    axios.get('api/add/cart/' + id)
        .then(res => {
            if (res.data.errors) {
                errors.value = res.data.errors;
            } else {
                // getPOSData();
                methodRefreshByDebounce();
                toastr.success('POS Inserted Successfully!!');
            }
        })
        .catch(console.log(errors))
}

// -------------- getPOSData ---------

const getPOSData = () => {
    axios.get('/api/get/cart')
        .then((res) => {
            posData.value = res.data;
        })
        .catch()
}

//------------  Increase Item -------------

const increaseIteam = (id) => {
    axios.get('/api/increase/cart/' + id)
        .then(() => {
            methodRefreshByDebounce();
            toastr.success('Item Inserted Successfully!!');
        })
        .catch()
}



//----------- Decrease Item --------------

const decreaseItem = (id) => {
    axios.get('/api/decrease/cart/' + id)
        .then(() => {
            methodRefreshByDebounce();
            toastr.success('Item Decreased Successfully!!');
        })
        .catch()
}

//------------ Delete Item --------------

const deleteItem = (id) => {
    axios.delete('/api/delete/cart/' + id)
        .then(() => {
            methodRefreshByDebounce();
            toastr.success('Item Deleted Successfully!!');
        })
}


// ----------- Total Quantity ------------

const totalQuantity = computed(() => {

    let sum = 0;
    const posDataValue = posData.value;
    const posDataLength = posDataValue.length;

    console.log('posdata =', posDataValue);

    for (let i = 0; i < posDataLength; i++) {
        sum += parseFloat(posDataValue[i].quantity);
    }

    console.log('sum =', sum);

    return sum;

})


// ----------- Total Quantity ------------

const totalSubTotal = computed(() => {

    let subTotal = 0;
    const posDataValue = posData.value;
    const posDataLength = posDataValue.length;

    console.log('posdata =', posDataValue);

    for (let i = 0; i < posDataLength; i++) {
        subTotal += parseFloat(posDataValue[i].quantity) * parseFloat(posDataValue[i].price);
    }

    console.log('subTotal =', subTotal);
    return subTotal;

})


//------------ Discount ----------

const discount = ref('');

const discountPayment = computed(() => {

    if (discount === '') {
        return 'No Discount';
    }

    let discountPayment = totalSubTotal.value * discount.value / 100;

    return discountPayment;
})


// ----------- Total Amount -----------

const totalAmount = computed(() => {

    let totalAmount = totalSubTotal.value - discountPayment.value;

    return totalAmount;
})


// ----------- Payment Receive --------------

const paymentReceive = ref('');

const remainingPayment = computed(() => {

    if (paymentReceive.value === "") {
        return 'Unpaid';
    }

    let payment = totalAmount.value - parseFloat(paymentReceive.value);

    console.log('payment = ', payment);

    return payment;
});



// ---------- orderDone -------------

const data = reactive({
    quantity: totalQuantity,
    subTotal: totalSubTotal,
    discount: discount,
    discountPayment: discountPayment,
    totalAmount: totalAmount,
    paymentReceive: paymentReceive,
    duePayment: remainingPayment,
    payby: '',
    customer_id: '',
});

const payByStripe = async () => {
    const stripe = await loadStripe('pk_test_51MwQwJIqzT5sBDbDq2bKPnZUycLX9KLYAVUjVL6MyFh4xccFXjaC9vftaOIFQJGvoHdWcbfC7rDt6Y13OwzkDSyb00WRk2Iwpz');
    try {
        const response = await axios.post('/api/stripe/payment', data, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        });
        const stripeUrl = response.data.url; // Extract session ID from response data

        // Redirect to the Stripe checkout page using the retrieved session ID
        window.location.href = `${stripeUrl}`;
    } catch (error) {
        console.error('Error creating Stripe Checkout session:', error);
        // Handle error
    }
}

const payByPaypal = async () => {
    try {
        const response = await axios.post('/api/paypal/payment', data, {
            headers: {
                'Content-Type': 'application/json',
            },
        });

        const paypalUrl = response.data.paypalUrl;
        // const { paypalUrl, user_id: userId, order_id: orderId } = response.data;

        if (paypalUrl) {
            window.location.href = paypalUrl;
            // window.location.href = `${paypalUrl}?user_id=${userId}&order_id=${orderId}`;
        } else {
            console.error('PayPal URL not found in the response.');
        }
    } catch (error) {
        console.error('Error creating PayPal transaction:', error);
    }
}



const orderDone = () => {
    axios.post('/api/order/done', data)
        .then(() => {
            // Success logic

            methodRefreshByDebounce();
            getProducts();
            toastr.success('Order Completed Successfully!!');

            data.discount = '';
            data.paymentReceive = '';
            data.payby = '';
            data.customer_id = '';

            // routerPath.push({ path: '/invoice' });


            // Clear the input fields
            //   Object.keys(data).forEach((key) => {
            //     data[key] = '';
            //   });

            // for (const key in data) {
            //     data[key] = '';
            // }
        })
        .catch(() => {
            // Error handling
        });
};





//---------- OnMounted ------------

onMounted(() => {
    getProducts();
    getCustomers();
    getPOSData();
    getCategories();
})
</script>

<template>
    <template>
        <section id="pos" style="margin-top: 10px;">
            <div class="row">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Monthly Recap Report</h6>
                            <a class="btn btn-sm btn-info">
                                <font color="FFFFFF">Add Customer</font>
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive" style="font-size:15px;">
                                <table class="table align-items-center table-flush">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>id</th>
                                            <th>Name</th>
                                            <th>Qty</th>
                                            <th>Unit</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(cart, index) in posData" :key="cart.id">
                                            <td>{{ ++index }}</td>
                                            <td>{{ cart.name }}</td>
                                            <td><input type="text" :value="cart.quantity" readonly
                                                    style="width: 60px;"><br>
                                                <button class="badge badge-sm badge-success"
                                                    @click.prevent="increaseIteam(cart.id)">+</button>
                                                <button class="badge badge-sm badge-danger"
                                                    @click.prevent="decreaseItem(cart.id)"
                                                    v-if="cart.quantity >= 2">-</button>
                                                <button class="badge badge-sm badge-danger" v-else
                                                    disabled="">-</button>
                                            </td>
                                            <td>{{ cart.price }}</td>
                                            <td><span class="badge badge-success">{{ cart.sub_total }}</span></td>
                                            <td><a href="#" class="btn btn-sm btn-primary">Detail</a></td>
                                            <td><button class="badge badge-sm badge-danger"
                                                    @click.prevent="deleteItem(cart.id)">X</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer"></div>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">Total
                                    Quantity:
                                    <strong>{{ totalQuantity }}</strong>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-center">Sub Total:
                                    <strong>${{ totalSubTotal }}</strong>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Discount(%):
                                    <input type="number" v-model="discount" class="form-control">
                                    <strong>(${{ discountPayment }})</strong>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-center">Total:
                                    <strong>${{ totalAmount }}</strong>
                                </li>
                            </ul>
                            <br>
                            <form @submit.prevent="orderDone">
                                <label>Customer Name</label>
                                <select class="form-control" v-model="data.customer_id">
                                    <option selected>Select Customer</option>
                                    <option :value="customer.id" v-for="customer in customerData.data">{{ customer.name
                                        }}
                                    </option>
                                </select>

                                <label>Payment Receive</label>
                                <input type="number" v-model="paymentReceive" class="form-control">

                                <label for="due">Due</label>
                                <li class="list-group-item">
                                    <strong>${{ remainingPayment }}</strong>
                                </li>


                                <label for="pay by">Pay By</label>
                                <select id="" class="form-control" v-model="data.payby">
                                    <option value="HandCash">Hand Cash</option>
                                    <option value="Bkash">Bkash</option>
                                    <option value="Cheaque">Cheaque</option>
                                    <option value="GiftCard">GiftCard</option>
                                    <option value="stripe">Stripe</option>
                                    <option value="paypal">Paypal</option>
                                </select>
                                <br>
                                <button v-if="data.payby === 'stripe'" @click="payByStripe">Pay Now</button>
                                <button v-else-if="data.payby === 'paypal'" @click="payByPaypal">Pay By Paypal</button>
                                <button v-else="" type="submit" class="btn btn-success">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            Products Sold
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <select v-model="selectedCategory">
                                        <option value="" disabled selected>Select category</option>
                                        <option v-for="category in categoryData.data" :key="category.id"
                                            :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" v-model="searchQuery" placeholder="Search Product">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3" v-for="product in productData.data">
                            <button class="btn btn-sm" @click.prevent="addToCart(product.id)">
                                <div class="card" style="margin-bottom: 10px; width: 11rem;">
                                    <div class="image d-flex justify-content-center mt-2">
                                        <img class="" :src="product.image_url" alt="no" :height="50" :width="60">
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">{{ product.name }}</h5><br>
                                        <div class="">
                                            Price: <span class="badge badge-success">{{ product.selling_price
                                                }}</span><br>
                                            Quantity: <span class="badge badge-success"
                                                v-if="product.quantity >= 1">Available
                                                {{
                                                    product.quantity
                                                }}</span>
                                            <span class="badge badge-danger" v-else>Stock Out</span>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </template>
</template>