<template>
    <el-container class="admin-layout">
        <!-- Sidebar -->
        <el-aside :width="isCollapse ? '64px' : '220px'" class="sidebar" v-if="isAuthenticated">
            <div class="logo">
                <span v-if="!isCollapse">Inventory Management</span>
                <el-icon v-else>
                    <ElementPlus />
                </el-icon>
            </div>
            <el-menu :default-active="activeMenu" :collapse="isCollapse" :collapse-transition="false" router
                background-color="#1f2937" text-color="#fff" active-text-color="#409EFF">
                <el-menu-item index="/dashboard">
                    <el-icon>
                        <House />
                    </el-icon>
                    <span>Dashboard</span>
                </el-menu-item>
                <el-menu-item index="/pos">
                    <el-icon>
                        <Iphone />
                    </el-icon>
                    <span>POS</span>
                </el-menu-item>
                <el-menu-item index="/order">
                    <el-icon>
                        <ShoppingCart />
                    </el-icon>
                    <span>Order</span>
                </el-menu-item>
                <el-sub-menu index="2">
                    <template #title>
                        <el-icon>
                            <Folder />
                        </el-icon>
                        <span>Products</span>
                    </template>
                    <el-menu-item index="/product">List</el-menu-item>
                    <el-menu-item index="/product/create">Add New</el-menu-item>
                </el-sub-menu>
                <el-menu-item index="/settings">
                    <el-icon>
                        <Setting />
                    </el-icon>
                    <span>Settings</span>
                </el-menu-item>
            </el-menu>
        </el-aside>

        <!-- Main Content -->
        <el-container>
            <!-- Header -->
            <el-header class="header" v-if="isAuthenticated">
                <div class="header-left">
                    <el-button @click="toggleSidebar" type="info" circle plain>
                        <Expand v-if="isCollapse" />
                        <Fold v-else />
                    </el-button>
                    <el-breadcrumb separator="/">
                        <el-breadcrumb-item>Home</el-breadcrumb-item>
                        <el-breadcrumb-item>{{ currentRoute }}</el-breadcrumb-item>
                    </el-breadcrumb>
                </div>
                <div class="header-right">
                    <el-switch v-model="isDarkMode" inline-prompt :active-icon="Moon" :inactive-icon="Sunny"
                        @change="toggleTheme" />
                    <el-dropdown>
                        <div class="user-dropdown">
                            <el-avatar :size="32" src="https://via.placeholder.com/150" />
                            <span>{{ user.name }}</span>
                        </div>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item>Profile</el-dropdown-item>
                                <el-dropdown-item>Settings</el-dropdown-item>
                                <el-dropdown-item divided @click="handleLogout">Logout</el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </div>
            </el-header>

            <!-- Main Content -->
            <el-main>
                <router-view />
            </el-main>

            <!-- Footer -->
            <el-footer class="footer" v-if="isAuthenticated">
                <span>© 2024 Admin Panel. All rights reserved.</span>
            </el-footer>
        </el-container>
    </el-container>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
    House,
    Folder,
    Setting,
    Moon,
    Sunny,
    ShoppingCart,
    ElementPlus,
    Expand,
    Fold,
    Goods,
    Iphone
} from '@element-plus/icons-vue';

const route = useRoute();
const router = useRouter();
const isCollapse = ref(false);
const isDarkMode = ref(false);
const isAuthenticated = ref(false);

// Check authentication status whenever route changes
watch(() => route.path, () => {
    checkAuth();
});

const currentRoute = computed(() => route.__name || 'Dashboard');

const toggleSidebar = () => {
    isCollapse.value = !isCollapse.value;
};

const toggleTheme = () => {
    document.documentElement.classList.toggle('dark', isDarkMode.value);
};

const checkAuth = () => {
    isAuthenticated.value = !!localStorage.getItem('token');
};

const activeMenu = computed(() => {
    return route.path;
});

const user = computed(() => {
    return {
        name: localStorage.getItem('user') || 'Admin',
        avatar: 'https://via.placeholder.com/150'
    };
});

const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    isAuthenticated.value = false; // Update immediately
    router.push('/');
};

onMounted(() => {
    checkAuth();

    // Redirect to login if not authenticated
    if (!isAuthenticated.value && !['login', 'register'].includes(route.name)) {
        router.push('/');
    }
    
    // Listen for storage events (if token is changed in another tab)
    window.addEventListener('storage', (event) => {
        if (event.key === 'token') {
            checkAuth();
        }
    });
});
</script>

<style scoped>
.admin-layout {
    height: 100vh;
}

.sidebar {
    background-color: #1f2937;
    transition: width 0.3s;
    overflow: hidden;
}

.logo {
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    font-weight: bold;
    border-bottom: 1px solid #374151;
}

.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: white;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    padding: 0 20px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 16px;
}

.user-dropdown {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.footer {
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: white;
    border-top: 1px solid #e5e7eb;
    color: #6b7280;
}

.el-menu {
    border-right: none;
}

.el-menu-item.is-active {
    background-color: #111827 !important;
}

.dark .el-header,
.dark .el-footer {
    background-color: #1a1a1a;
    color: white;
}

.dark .el-menu {
    background-color: #111827;
}
</style>
