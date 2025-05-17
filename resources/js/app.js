import './bootstrap';
import { createApp } from 'vue';
import router from './router';
import App from './components/layouts/AppLayout.vue';
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import { Icon } from '@iconify/vue';


const app = createApp(App);
app.use(router);
app.component('Icon', Icon);
app.use(ElementPlus)
app.mount('#app');