/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';

// --------------- AdminLte js --------------
import 'admin-lte/dist/js/adminlte.min.js';
import 'admin-lte/plugins/bootstrap/js/bootstrap.bundle.min.js';
// import 'admin-lte/plugins/jquery/jquery.min.js';

import { createApp } from 'vue';
// import layout from './layouts/app.vue';
import router from './router.js';
// import jQuery from 'jquery';
// window.$ = window.jQuery = jQuery;


// window.Reload = new Vue();

const app = createApp({});

app.use(router).mount('#app');


// createApp(app).use(router).mount("#app")
