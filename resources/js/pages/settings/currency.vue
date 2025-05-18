<template>
    <div class="currency-settings-page">
        <el-card class="settings-card">
            <template #header>
                <div class="card-header">
                    <h2 class="header-title">Currency Settings</h2>
                </div>
            </template>

            <el-form 
                :model="form" 
                :rules="rules" 
                ref="formRef" 
                label-position="top" 
                @submit.prevent="saveSettings"
                v-loading="loading"
            >
                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item label="Currency" prop="currency_code">
                            <el-select 
                                v-model="form.currency_code" 
                                filterable 
                                placeholder="Select Currency"
                                @change="onCurrencyChange"
                                class="w-full"
                            >
                                <el-option
                                    v-for="(currency, code) in currencies"
                                    :key="code"
                                    :label="`${code} - ${currency.name}`"
                                    :value="code"
                                >
                                    <div class="currency-option">
                                        <span>{{ code }}-</span>
                                        <span class="currency-name">{{ currency.name }}-</span>
                                        <span class="currency-symbol">{{ currency.symbol }}</span>
                                    </div>
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    
                    <el-col :span="12">
                        <el-form-item label="Currency Symbol" prop="currency_symbol">
                            <el-input v-model="form.currency_symbol" placeholder="Currency Symbol" />
                        </el-form-item>
                    </el-col>
                </el-row>

                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item label="Symbol Position" prop="currency_position">
                            <el-radio-group v-model="form.currency_position">
                                <el-radio label="before">Before Amount ({{ form.currency_symbol }}100)</el-radio>
                                <el-radio label="after">After Amount (100{{ form.currency_symbol }})</el-radio>
                            </el-radio-group>
                        </el-form-item>
                    </el-col>
                    
                    <el-col :span="12">
                        <el-form-item label="Decimal Places" prop="decimal_places">
                            <el-input-number v-model="form.decimal_places" :min="0" :max="4" />
                        </el-form-item>
                    </el-col>
                </el-row>

                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item label="Decimal Separator" prop="decimal_separator">
                            <el-select v-model="form.decimal_separator" class="w-full">
                                <el-option label="Dot (.)" value="." />
                                <el-option label="Comma (,)" value="," />
                            </el-select>
                        </el-form-item>
                    </el-col>
                    
                    <el-col :span="12">
                        <el-form-item label="Thousand Separator" prop="thousand_separator">
                            <el-select v-model="form.thousand_separator" class="w-full">
                                <el-option label="Comma (,)" value="," />
                                <el-option label="Dot (.)" value="." />
                                <el-option label="Space ( )" value=" " />
                                <el-option label="None" value="" />
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>

                <el-form-item>
                    <div class="preview-section">
                        <h3>Preview:</h3>
                        <div class="preview-box">
                            {{ formatPreview(1234.56) }}
                        </div>
                    </div>
                </el-form-item>

                <el-form-item>
                    <el-button type="primary" native-type="submit" :loading="saving">
                        Save Settings
                    </el-button>
                </el-form-item>
            </el-form>
        </el-card>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { ElMessage } from 'element-plus';
import axios from 'axios';

const formRef = ref(null);
const loading = ref(true);
const saving = ref(false);
const currencies = ref({});

const form = reactive({
    currency_code: 'USD',
    currency_symbol: '$',
    currency_position: 'before',
    decimal_separator: '.',
    thousand_separator: ',',
    decimal_places: 2
});

const rules = {
    currency_code: [
        { required: true, message: 'Please select a currency', trigger: 'change' }
    ],
    currency_symbol: [
        { required: true, message: 'Currency symbol is required', trigger: 'blur' }
    ],
    currency_position: [
        { required: true, message: 'Please select symbol position', trigger: 'change' }
    ],
    decimal_places: [
        { required: true, message: 'Please specify decimal places', trigger: 'change' }
    ],
    decimal_separator: [
        { required: true, message: 'Please select decimal separator', trigger: 'change' }
    ],
    thousand_separator: [
        { required: true, message: 'Please select thousand separator', trigger: 'change' }
    ]
};

// Format preview based on current settings
const formatPreview = (amount) => {
    const { currency_symbol, currency_position, decimal_separator, thousand_separator, decimal_places } = form;
    
    // Format the number
    let formattedNumber = amount.toFixed(decimal_places);
    
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
    
    // Add currency symbol based on position
    if (currency_position === 'before') {
        return currency_symbol + formattedNumber;
    } else {
        return formattedNumber + currency_symbol;
    }
};

// Handle currency change
const onCurrencyChange = (code) => {
    if (currencies.value[code]) {
        form.currency_symbol = currencies.value[code].symbol;
    }
};

// Load available currencies
const loadCurrencies = async () => {
    try {
        const response = await axios.get('/api/settings/currencies');
        currencies.value = response.data;
    } catch (error) {
        console.error('Failed to load currencies:', error);
        ElMessage.error('Failed to load available currencies');
    }
};

// Load current settings
const loadSettings = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/api/settings/currency');
        
        // Update form with current settings
        Object.keys(form).forEach(key => { 
            if (response.data[key] !== null) {
                form[key] = response.data[key];
            }
        });
    } catch (error) {
        console.error('Failed to load settings:', error);
        ElMessage.error('Failed to load currency settings');
    } finally {
        loading.value = false;
    }
};

// Save settings
const saveSettings = async () => {
    if (!formRef.value) return;
    
    await formRef.value.validate(async (valid) => {
        if (!valid) return;
        
        try {
            saving.value = true;
            await axios.post('/api/settings/currency', form);
            ElMessage.success('Currency settings saved successfully');
            refreshPage();
        } catch (error) {
            console.error('Failed to save settings:', error);
            ElMessage.error('Failed to save currency settings');
        } finally {
            saving.value = false;
        }
    });
};

const refreshPage = () => {
    setTimeout(() => {
        window.location.reload();
    }, 1000);
};

onMounted(async () => {
    await loadCurrencies();
    await loadSettings();
});
</script>

<style scoped>
.currency-settings-page {
    padding: 20px;
}

.settings-card {
    max-width: 800px;
    margin: 0 auto;
}

.preview-section {
    margin-top: 20px;
}

.preview-box {
    border: 1px solid #ebeef5;
    padding: 10px;
    border-radius: 4px;
    background-color: #f5f7fa;
    text-align: center;
}
</style>
