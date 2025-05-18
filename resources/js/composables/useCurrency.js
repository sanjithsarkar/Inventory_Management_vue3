import { formatCurrencySync, loadCurrencySettings } from '../utils/currency';

export function useCurrency() {
  return {
    formatCurrency: formatCurrencySync,
    loadCurrencySettings
  };
}