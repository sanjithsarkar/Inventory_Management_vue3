import { loadCurrencySettings, formatCurrency, formatCurrencySync } from '../utils/currency';

export default {
  install: (app) => {
    console.log('Currency plugin installed'); // Debug log
    
    // Load currency settings when the app starts
    loadCurrencySettings();
    
    // Add global properties
    app.config.globalProperties.$formatCurrency = formatCurrencySync;
    
    // Add global directive for currency formatting
    app.directive('currency', {
      mounted(el, binding) {
        const value = binding.value || 0;
        el.textContent = formatCurrencySync(value);
      },
      updated(el, binding) {
        const value = binding.value || 0;
        el.textContent = formatCurrencySync(value);
      }
    });
  }
};
