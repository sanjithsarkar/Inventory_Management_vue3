<template>
    <div class="register-container">
      <el-card class="register-card">
        <template #header>
          <div class="card-header">
            <h2>Create Your Account</h2>
          </div>
        </template>
  
        <el-form 
          ref="registerForm"
          :model="form" 
          :rules="rules" 
          @submit.prevent="registerUser"
          label-position="top"
        >
          <el-form-item label="Full Name" prop="name">
            <el-input 
              v-model="form.name" 
              placeholder="Enter your full name"
              clearable
            >
              <template #prefix>
                <el-icon><User /></el-icon>
              </template>
            </el-input>
          </el-form-item>
  
          <el-form-item label="Email Address" prop="email">
            <el-input 
              v-model="form.email" 
              placeholder="Enter your email"
              clearable
            >
              <template #prefix>
                <el-icon><Message /></el-icon>
              </template>
            </el-input>
          </el-form-item>
  
          <el-form-item label="Password" prop="password">
            <el-input 
              v-model="form.password" 
              placeholder="Create a password"
              show-password
              type="password"
            >
              <template #prefix>
                <el-icon><Lock /></el-icon>
              </template>
            </el-input>
          </el-form-item>
  
          <el-form-item label="Confirm Password" prop="c_password">
            <el-input 
              v-model="form.c_password" 
              placeholder="Confirm your password"
              show-password
              type="password"
            >
              <template #prefix>
                <el-icon><Lock /></el-icon>
              </template>
            </el-input>
          </el-form-item>
  
          <el-button 
            type="primary" 
            native-type="submit" 
            class="submit-btn"
            :loading="loading"
          >
            Register
          </el-button>
  
          <div class="login-link">
            Already have an account? <router-link to="/">Sign in</router-link>
          </div>
        </el-form>
      </el-card>
  
      <!-- Error notification -->
      <el-dialog
        v-model="errorDialogVisible"
        title="Registration Error"
        width="30%"
        center
      >
        <div v-for="(error, index) in errors" :key="index">
          <p v-for="(message, i) in error" :key="i" class="error-message">
            {{ message }}
          </p>
        </div>
        <template #footer>
          <el-button type="primary" @click="errorDialogVisible = false">
            OK
          </el-button>
        </template>
      </el-dialog>
    </div>
  </template>
  
  <script>
  import { User, Message, Lock } from '@element-plus/icons-vue'
  import axios from 'axios'
  import { useRouter } from 'vue-router'
  
  export default {
    name: 'RegisterPage',
    components: { User, Message, Lock },
    data() {
      const validatePassword = (rule, value, callback) => {
        if (value !== this.form.password) {
          callback(new Error('Passwords do not match!'))
        } else {
          callback()
        }
      }
      
      return {
        form: {
          name: '',
          email: '',
          password: '',
          c_password: ''
        },
        rules: {
          name: [
            { required: true, message: 'Please input your name', trigger: 'blur' },
            { min: 3, message: 'Name should be at least 3 characters', trigger: 'blur' }
          ],
          email: [
            { required: true, message: 'Please input email address', trigger: 'blur' },
            { type: 'email', message: 'Please input correct email address', trigger: ['blur', 'change'] }
          ],
          password: [
            { required: true, message: 'Please input password', trigger: 'blur' },
            { min: 6, message: 'Password should be at least 6 characters', trigger: 'blur' }
          ],
          c_password: [
            { required: true, message: 'Please confirm password', trigger: 'blur' },
            { validator: validatePassword, trigger: 'blur' }
          ]
        },
        errors: [],
        loading: false,
        errorDialogVisible: false,
        router: useRouter()
      }
    },
    methods: {
      async registerUser() {
        try {
          this.loading = true
          const response = await axios.post('api/register', this.form)
          
          if (response.data.success) {
            localStorage.setItem('token', response.data.data.token)
            localStorage.setItem('user', response.data.data.name)
            this.$message.success('Registration successful!')
            await this.router.push({ path: '/dashboard' })
            // window.location.reload()
          } else {
            this.errors = response.data.message
            this.errorDialogVisible = true
          }
        } catch (error) {
          if (error.response) {
            this.errors = error.response.data.message
            this.errorDialogVisible = true
          } else {
            this.$message.error('Network error occurred. Please try again.')
          }
        } finally {
          this.loading = false
        }
      }
    }
  }
  </script>
  
  <style scoped>
  .register-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #f5f7fa;
    padding: 20px;
  }
  
  .register-card {
    width: 100%;
    max-width: 500px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
  
  .card-header {
    text-align: center;
    padding: 20px 0;
  }
  
  .card-header h2 {
    margin: 0;
    color: #303133;
  }
  
  .submit-btn {
    width: 100%;
    margin-top: 20px;
    height: 48px;
    font-size: 16px;
  }
  
  .login-link {
    margin-top: 20px;
    text-align: center;
    color: #606266;
  }
  
  .login-link a {
    color: #409eff;
    text-decoration: none;
  }
  
  .login-link a:hover {
    text-decoration: underline;
  }
  
  .error-message {
    color: #f56c6c;
    margin: 5px 0;
  }
  </style>