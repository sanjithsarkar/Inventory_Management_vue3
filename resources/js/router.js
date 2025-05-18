import { createWebHashHistory, createRouter } from 'vue-router';

import login from './pages/auth/login.vue';
import register from './pages/auth/register.vue';
import dashboard from './pages/dashboard.vue';

import employeeIndex from './pages/employee/index.vue';
import employeeCreate from './pages/employee/create.vue';
import employeeEdit from './pages/employee/edit.vue';

import customerIndex from './pages/customer/index.vue';
import customerCreate from './pages/customer/create.vue';
import customerEdit from './pages/customer/edit.vue';

import categoryIndex from './pages/product/category/index.vue';

import productIndex from './pages/product/index.vue';
import productCreate from './pages/product/create1.vue';
import productEdit from './pages/product/edit.vue';

import pos from './pages/pos/pos.vue';
import order from './pages/order/orderPrintByModal.vue';
import invoice from './pages/invoice/invoice.vue';

const routes = [
  // { path: '/', component: Home, meta: { requiresAuth: false } },
  { path: '/register', component: register, name: register, meta: { requiresAuth: false }},
  { path: '/', component: login, name: login, meta: { requiresAuth: false }},
  { path: '/dashboard', component: dashboard, name: dashboard, meta: { requiresAuth: true } },

  { path: '/employee', component: employeeIndex, meta: { requiresAuth: true }},
  { path: '/employee/create', component: employeeCreate, meta: { requiresAuth: true }},
  { path: '/employee/edit/:id', component: employeeEdit, name: 'employee-edit', meta: { requiresAuth: true } },


  { path: '/customer', component: customerIndex, meta: { requiresAuth: true } },
  { path: '/customer/create', component: customerCreate, meta: { requiresAuth: true }},
  { path: '/customer/edit/:id', component: customerEdit, name: 'customer-edit', meta: { requiresAuth: true } },

  { path: '/category', component: categoryIndex, meta: { requiresAuth: true } },

  { path: '/product', component: productIndex, meta: { requiresAuth: true }},
  { path: '/product/create', component: productCreate, meta: { requiresAuth: true }},
  { path: '/product/edit/:id', component: productEdit, name: 'product-edit', meta: { requiresAuth: true } },

  { path: '/pos', component: pos, meta: { requiresAuth: true } },
  { path: '/order', component: order, meta: { requiresAuth: true } },

  { path: '/invoice', component: invoice, meta: { requiresAuth: true } },

  // Supplier routes
  {
    path: '/supplier',
    name: 'supplier',
    component: () => import('./pages/supplier/index.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/supplier/create',
    name: 'supplier.create',
    component: () => import('./pages/supplier/create.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/supplier/edit/:id',
    name: 'supplier.edit',
    component: () => import('./pages/supplier/edit.vue'),
    meta: { requiresAuth: true }
  },
  
]


const router = createRouter({
  history: createWebHashHistory(),
  routes,
})

router.beforeEach((to, from) => {  
  if(to.meta.requiresAuth == true && !localStorage.getItem('token')){
    return { path: '/'}
  } else if (to.meta.requiresAuth == false && localStorage.getItem('token')) {
    return { path: '/dashboard' }
  }
})

// 5. Create and mount the root instance.
export default router;
