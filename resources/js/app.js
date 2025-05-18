import './bootstrap';
import { createApp } from 'vue';
import router from './router';
import App from './components/layouts/AppLayout.vue';
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import { Icon } from '@iconify/vue';
import { ElNotification } from 'element-plus';
// Import the currency plugin
import CurrencyPlugin from './plugins/currency';

// Create the app instance
const app = createApp(App);

// Register plugins
app.use(router);
app.use(ElementPlus);
app.use(CurrencyPlugin); // Make sure this is registered before mounting

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

// Mount the app
app.mount('#app');
