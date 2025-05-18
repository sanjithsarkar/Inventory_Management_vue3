import './bootstrap';
import { createApp } from 'vue';
import router from './router';
import App from './components/layouts/AppLayout.vue';
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import { Icon } from '@iconify/vue';
import { ElNotification } from 'element-plus';

// Global notification handler for Laravel session messages
window.showNotification = (message, type = 'info') => {
    ElNotification({
        title: type.charAt(0).toUpperCase() + type.slice(1),
        message: message,
        type: type,
        position: 'bottom-right',
        duration: 3000
    });
};

const app = createApp(App);
app.use(router);
app.component('Icon', Icon);
app.use(ElementPlus)
app.mount('#app');
