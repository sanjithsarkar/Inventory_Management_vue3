<template>
    <div class="app-layout">
      <Header />
      <div class="main-container">
        <Sidebar v-if="isAuthenticated" />
        <main class="content">
          <router-view />
        </main>
      </div>
      <Footer v-if="isAuthenticated" />
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import Header from './Header.vue';
  import Sidebar from './Sidebar.vue';
  import Footer from './Footer.vue';
  
  const router = useRouter();
  const isAuthenticated = ref(false);
  
  const checkAuth = () => {
    isAuthenticated.value = !!localStorage.getItem('authToken');
  };
  
  onMounted(() => {
    checkAuth();
    
    // Redirect to login if not authenticated
    if (!isAuthenticated.value && !['login', 'register'].includes(router.currentRoute.value.name)) {
      router.push('/login');
    }
  });
  </script>
  
  <style scoped>
  .app-layout {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }
  
  .main-container {
    display: flex;
    flex: 1;
  }
  
  .content {
    flex: 1;
    padding: 20px;
    background-color: #f8fafc;
  }
  
  @media (max-width: 768px) {
    .main-container {
      flex-direction: column;
    }
  }
  </style>