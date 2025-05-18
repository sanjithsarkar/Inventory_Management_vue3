import { formatCurrencySync, formatCurrency, loadCurrencySettings, formatAmountSync, formatAmount } from '../utils/currency';

export function useCurrency() {
  return {
    formatCurrency: formatCurrencySync,
    formatCurrencyAsync: formatCurrency,
    formatAmount: formatAmountSync,
    formatAmountAsync: formatAmount,
    loadCurrencySettings
  };
}
