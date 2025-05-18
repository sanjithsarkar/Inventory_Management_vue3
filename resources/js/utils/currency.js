import axios from 'axios';

// Cache for currency settings
let currencySettings = null;
let loadingPromise = null;

/**
 * Load currency settings from the server
 * @returns {Promise} Promise that resolves with currency settings
 */
export const loadCurrencySettings = async () => {
  if (currencySettings) {
    return currencySettings;
  }
  
  if (!loadingPromise) {
    loadingPromise = axios.get('/api/settings/currency')
      .then(response => {
        currencySettings = response.data;
        console.log('Currency settings loaded:', currencySettings); // Debug log
        return currencySettings;
      })
      .catch(error => {
        console.error('Failed to load currency settings:', error);
        // Default fallback settings
        currencySettings = {
          currency_symbol: '$',
          currency_position: 'before',
          decimal_separator: '.',
          thousand_separator: ',',
          decimal_places: 2
        };
        return currencySettings;
      });
  }
  
  return loadingPromise;
};

/**
 * Format a number as currency according to settings (with currency symbol)
 * @param {number} amount - The amount to format
 * @param {boolean} useCache - Whether to use cached settings (default: true)
 * @returns {Promise<string>} Formatted currency string with symbol
 */
export const formatCurrency = async (amount, useCache = true) => {
  // Handle null/undefined values
  if (amount === null || amount === undefined) {
    amount = 0;
  }
  
  // Convert to number if it's a string
  if (typeof amount === 'string') {
    amount = parseFloat(amount.replace(/[^0-9.-]+/g, ''));
  }
  
  // Handle NaN
  if (isNaN(amount)) {
    amount = 0;
  }
  
  // Get settings
  const settings = useCache && currencySettings 
    ? currencySettings 
    : await loadCurrencySettings();
  
  const {
    currency_symbol = '$',
    currency_position = 'before',
    decimal_separator = '.',
    thousand_separator = ',',
    decimal_places = 2
  } = settings;
  
  // Format the number
  const absAmount = Math.abs(amount);
  let formattedNumber = absAmount.toFixed(decimal_places);
  
  // Handle decimal and thousand separators
  const parts = formattedNumber.split('.');
  const integerPart = parts[0];
  const decimalPart = parts.length > 1 ? parts[1] : '';
  
  // Format integer part with thousand separator
  let formattedInteger = '';
  for (let i = 0; i < integerPart.length; i++) {
    if (i > 0 && (integerPart.length - i) % 3 === 0 && thousand_separator) {
      formattedInteger += thousand_separator;
    }
    formattedInteger += integerPart[i];
  }
  
  // Combine parts with decimal separator
  formattedNumber = formattedInteger + (decimalPart ? decimal_separator + decimalPart : '');
  
  // Add negative sign if needed
  const sign = amount < 0 ? '-' : '';
  
  // Add currency symbol based on position
  if (currency_position === 'before') {
    return sign + currency_symbol + formattedNumber;
  } else {
    return sign + formattedNumber + currency_symbol;
  }
};

/**
 * Format a number according to currency settings (without currency symbol)
 * @param {number} amount - The amount to format
 * @param {boolean} useCache - Whether to use cached settings (default: true)
 * @returns {Promise<string>} Formatted number string without currency symbol
 */
export const formatAmount = async (amount, useCache = true) => {
  // Handle null/undefined values
  if (amount === null || amount === undefined) {
    amount = 0;
  }
  
  // Convert to number if it's a string
  if (typeof amount === 'string') {
    amount = parseFloat(amount.replace(/[^0-9.-]+/g, ''));
  }
  
  // Handle NaN
  if (isNaN(amount)) {
    amount = 0;
  }
  
  // Get settings
  const settings = useCache && currencySettings 
    ? currencySettings 
    : await loadCurrencySettings();
  
  const {
    decimal_separator = '.',
    thousand_separator = ',',
    decimal_places = 2
  } = settings;
  
  // Format the number
  const absAmount = Math.abs(amount);
  let formattedNumber = absAmount.toFixed(decimal_places);
  
  // Handle decimal and thousand separators
  const parts = formattedNumber.split('.');
  const integerPart = parts[0];
  const decimalPart = parts.length > 1 ? parts[1] : '';
  
  // Format integer part with thousand separator
  let formattedInteger = '';
  for (let i = 0; i < integerPart.length; i++) {
    if (i > 0 && (integerPart.length - i) % 3 === 0 && thousand_separator) {
      formattedInteger += thousand_separator;
    }
    formattedInteger += integerPart[i];
  }
  
  // Combine parts with decimal separator
  formattedNumber = formattedInteger + (decimalPart ? decimal_separator + decimalPart : '');
  
  // Add negative sign if needed
  const sign = amount < 0 ? '-' : '';
  
  return sign + formattedNumber;
};

/**
 * Synchronous version of formatCurrency that uses cached settings (with currency symbol)
 * @param {number} amount - The amount to format
 * @returns {string} Formatted currency string with symbol
 */
export const formatCurrencySync = (amount) => {
  // Handle null/undefined values
  if (amount === null || amount === undefined) {
    amount = 0;
  }
  
  // Convert to number if it's a string
  if (typeof amount === 'string') {
    amount = parseFloat(amount.replace(/[^0-9.-]+/g, ''));
  }
  
  // Handle NaN
  if (isNaN(amount)) {
    amount = 0;
  }
  
  // Use cached settings or fallback to defaults
  const settings = currencySettings || {
    currency_symbol: '$',
    currency_position: 'before',
    decimal_separator: '.',
    thousand_separator: ',',
    decimal_places: 2
  };
  
  const {
    currency_symbol = '$',
    currency_position = 'before',
    decimal_separator = '.',
    thousand_separator = ',',
    decimal_places = 2
  } = settings;
  
  // Format the number
  const absAmount = Math.abs(amount);
  let formattedNumber = absAmount.toFixed(decimal_places);
  
  // Handle decimal and thousand separators
  const parts = formattedNumber.split('.');
  const integerPart = parts[0];
  const decimalPart = parts.length > 1 ? parts[1] : '';
  
  // Format integer part with thousand separator
  let formattedInteger = '';
  for (let i = 0; i < integerPart.length; i++) {
    if (i > 0 && (integerPart.length - i) % 3 === 0 && thousand_separator) {
      formattedInteger += thousand_separator;
    }
    formattedInteger += integerPart[i];
  }
  
  // Combine parts with decimal separator
  formattedNumber = formattedInteger + (decimalPart ? decimal_separator + decimalPart : '');
  
  // Add negative sign if needed
  const sign = amount < 0 ? '-' : '';
  
  // Add currency symbol based on position
  if (currency_position === 'before') {
    return sign + currency_symbol + formattedNumber;
  } else {
    return sign + formattedNumber + currency_symbol;
  }
};

/**
 * Synchronous version of formatAmount that uses cached settings (without currency symbol)
 * @param {number} amount - The amount to format
 * @returns {string} Formatted number string without currency symbol
 */
export const formatAmountSync = (amount) => {
  // Handle null/undefined values
  if (amount === null || amount === undefined) {
    amount = 0;
  }
  
  // Convert to number if it's a string
  if (typeof amount === 'string') {
    amount = parseFloat(amount.replace(/[^0-9.-]+/g, ''));
  }
  
  // Handle NaN
  if (isNaN(amount)) {
    amount = 0;
  }
  
  // Use cached settings or fallback to defaults
  const settings = currencySettings || {
    decimal_separator: '.',
    thousand_separator: ',',
    decimal_places: 2
  };
  
  const {
    decimal_separator = '.',
    thousand_separator = ',',
    decimal_places = 2
  } = settings;
  
  // Format the number
  const absAmount = Math.abs(amount);
  let formattedNumber = absAmount.toFixed(decimal_places);
  
  // Handle decimal and thousand separators
  const parts = formattedNumber.split('.');
  const integerPart = parts[0];
  const decimalPart = parts.length > 1 ? parts[1] : '';
  
  // Format integer part with thousand separator
  let formattedInteger = '';
  for (let i = 0; i < integerPart.length; i++) {
    if (i > 0 && (integerPart.length - i) % 3 === 0 && thousand_separator) {
      formattedInteger += thousand_separator;
    }
    formattedInteger += integerPart[i];
  }
  
  // Combine parts with decimal separator
  formattedNumber = formattedInteger + (decimalPart ? decimal_separator + decimalPart : '');
  
  // Add negative sign if needed
  const sign = amount < 0 ? '-' : '';
  
  return sign + formattedNumber;
};

// Initialize by loading settings when the module is imported
loadCurrencySettings();

// Make sure we're exporting all the functions
export default {
  formatCurrency,
  formatCurrencySync,
  formatAmount,
  formatAmountSync,
  loadCurrencySettings
};
