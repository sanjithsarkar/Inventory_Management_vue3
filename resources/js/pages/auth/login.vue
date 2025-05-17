<script setup>
import { reactive, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import axios from 'axios'

const form = reactive({
    email: '',
    password: ''
})

const loading = ref(false)
const passwordVisible = ref(false)
const route = useRoute()
const router = useRouter()

const loginUser = async () => {
    loading.value = true

    try {
        const response = await axios.post('http://127.0.0.1:8000/api/login', form)

        if (response.data.success) {
            localStorage.setItem('token', response.data.data.token)
            localStorage.setItem('user', response.data.data.name)

            ElMessage.success('Login successful!')
            router.push({ path: '/dashboard' })
        } else {
            ElMessage.error(response.data.message || 'Login failed')
        }
    } catch (error) {
        if (error.response.data.message) {
            const errorMessages = Object.values(error.response.data.message).flat()
            errorMessages.forEach((message, index) => {
                setTimeout(() => {
                    ElMessage.error(message)
                }, index * 100) // 100ms delay between messages
            })
        } else {
            ElMessage.error(errorMessage)
        }
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="login-container">
        <div class="background-pattern"></div>

        <el-card class="login-card">
            <div class="brand-section">
                <div class="brand-logo">
                    <el-icon :size="40" color="#409EFF">
                        <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg">
                            <path fill="currentColor"
                                d="M512 64a448 448 0 1 1 0 896 448 448 0 0 1 0-896zm-55.808 536.384-99.52-99.584a38.4 38.4 0 0 0-54.336 54.336l126.72 126.72a38.272 38.272 0 0 0 54.336 0l262.4-262.464a38.4 38.4 0 0 0-54.272-54.336L456.192 600.384z" />
                        </svg>
                    </el-icon>
                </div>
                <h1 class="brand-title">Welcome Back</h1>
                <p class="brand-subtitle">Sign in to your account</p>
            </div>

            <el-form :model="form" @submit.prevent="loginUser" label-position="top" class="login-form">
                <el-form-item label="Email Address" prop="email" :rules="[
                    { required: true, message: 'Email is required', trigger: 'blur' },
                    { type: 'email', message: 'Please enter a valid email', trigger: 'blur' }
                ]">
                    <el-input v-model="form.email" type="email" placeholder="your@email.com" size="large"
                        :prefix-icon="Message" />
                </el-form-item>

                <el-form-item label="Password" prop="password" :rules="[
                    { required: true, message: 'Password is required', trigger: 'blur' },
                    { min: 6, message: 'Minimum 6 characters', trigger: 'blur' }
                ]">
                    <el-input v-model="form.password" :type="passwordVisible ? 'text' : 'password'"
                        placeholder="••••••••" size="large" :prefix-icon="Lock"
                        :suffix-icon="passwordVisible ? View : Hide"
                        @click:suffix-icon="passwordVisible = !passwordVisible" />
                </el-form-item>

                <div class="form-actions">
                    <el-checkbox label="Remember me" size="large" />
                    <el-link type="primary" :underline="false">Forgot password?</el-link>
                </div>

                <el-button type="primary" native-type="submit" :loading="loading" size="large" class="login-button">
                    {{ loading ? 'Signing in...' : 'Sign In' }}
                </el-button>

                <div class="social-login">
                    <p class="divider">or continue with</p>
                    <div class="social-buttons">
                        <el-button circle size="large">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" d="M17.05 20.28c-.98.95-2.05.8-3.08.35c-1.09-.46-2.09-.48-3.24 0c-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8c1.18-.24 2.31-.93 3.57-.84c1.51.12 2.65.72 3.4 1.8c-3.12 1.87-2.38 5.98.48 7.13c-.57 1.5-1.31 2.99-2.54 4.09zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25c.29 2.58-2.34 4.5-3.74 4.25"/></svg>
                        </el-button>
                        <el-button circle size="large">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 48 48"><path fill="#ffc107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4C12.955 4 4 12.955 4 24s8.955 20 20 20s20-8.955 20-20c0-1.341-.138-2.65-.389-3.917"/><path fill="#ff3d00" d="m6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4C16.318 4 9.656 8.337 6.306 14.691"/><path fill="#4caf50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.9 11.9 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44"/><path fill="#1976d2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002l6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917"/></svg>
                        </el-button>
                        <el-button :icon="Github" circle size="large">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2A10 10 0 0 0 2 12c0 4.42 2.87 8.17 6.84 9.5c.5.08.66-.23.66-.5v-1.69c-2.77.6-3.36-1.34-3.36-1.34c-.46-1.16-1.11-1.47-1.11-1.47c-.91-.62.07-.6.07-.6c1 .07 1.53 1.03 1.53 1.03c.87 1.52 2.34 1.07 2.91.83c.09-.65.35-1.09.63-1.34c-2.22-.25-4.55-1.11-4.55-4.92c0-1.11.38-2 1.03-2.71c-.1-.25-.45-1.29.1-2.64c0 0 .84-.27 2.75 1.02c.79-.22 1.65-.33 2.5-.33s1.71.11 2.5.33c1.91-1.29 2.75-1.02 2.75-1.02c.55 1.35.2 2.39.1 2.64c.65.71 1.03 1.6 1.03 2.71c0 3.82-2.34 4.66-4.57 4.91c.36.31.69.92.69 1.85V21c0 .27.16.59.67.5C19.14 20.16 22 16.42 22 12A10 10 0 0 0 12 2"/></svg>
                        </el-button>
                    </div>
                </div>

                <p class="signup-link">
                    Don't have an account? <router-link to="/register">
                        <el-link type="primary" :underline="false">Sign up</el-link>
                    </router-link>


                </p>
            </el-form>
        </el-card>

        <div class="footer">
            <p>© 2023 Your Company. All rights reserved.</p>
            <div class="footer-links">
                <el-link :underline="false">Terms</el-link>
                <el-link :underline="false">Privacy</el-link>
                <el-link :underline="false">Contact</el-link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #f8fafc;
    position: relative;
    overflow: hidden;
    padding: 2rem;
}

.background-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
    background-size: 16px 16px;
    opacity: 0.6;
    z-index: 0;
}

.login-card {
    width: 100%;
    max-width: 420px;
    border-radius: 12px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    border: none;
    z-index: 1;
    padding: 2rem;
}

.brand-section {
    text-align: center;
    margin-bottom: 2rem;
}

.brand-logo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
    border-radius: 12px;
    background-color: #ebf5ff;
    margin-bottom: 1rem;
}

.brand-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.brand-subtitle {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0;
}

.login-form {
    margin-top: 1.5rem;
}

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.login-button {
    width: 100%;
    margin-bottom: 1.5rem;
}

.social-login {
    margin-bottom: 1.5rem;
}

.divider {
    display: flex;
    align-items: center;
    color: #64748b;
    font-size: 0.875rem;
    margin: 1.5rem 0;
}

.divider::before,
.divider::after {
    content: "";
    flex: 1;
    border-bottom: 1px solid #e2e8f0;
}

.divider::before {
    margin-right: 1rem;
}

.divider::after {
    margin-left: 1rem;
}

.social-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.signup-link {
    text-align: center;
    color: #64748b;
    font-size: 0.875rem;
    margin: 0;
}

.footer {
    position: absolute;
    bottom: 1rem;
    left: 0;
    right: 0;
    text-align: center;
    color: #64748b;
    font-size: 0.75rem;
    z-index: 1;
}

.footer-links {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 0.5rem;
}

:deep(.el-form-item__label) {
    font-weight: 500;
    color: #475569;
    margin-bottom: 0.25rem;
}

:deep(.el-input__wrapper) {
    border-radius: 8px;
}

:deep(.el-button) {
    border-radius: 8px;
}

:deep(.el-button--circle) {
    border: 1px solid #e2e8f0;
    background: white;
    color: #64748b;
}
</style>