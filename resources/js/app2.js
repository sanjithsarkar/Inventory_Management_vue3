/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap.js';

// Remove AdminLTE imports

import { createApp } from 'vue';
import router from './router.js';
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'

const app = createApp({});

app.use(ElementPlus)

app.use(router).mount('#app');
