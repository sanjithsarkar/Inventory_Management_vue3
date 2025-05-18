<template>
  <el-aside
    class="admin-sidebar"
    :width="collapsed ? '64px' : '220px'"
    :class="{ collapsed }"
  >
    <div class="logo-container" @click="$emit('toggle-collapse')">
      <span class="logo-text" v-if="!collapsed">Inventory Management</span>
      <el-icon v-else><Menu /></el-icon>
    </div>

    <el-scrollbar class="sidebar-scrollbar">
      <el-menu
        :default-active="activeMenu"
        class="sidebar-menu"
        :collapse="collapsed"
        :collapse-transition="false"
        background-color="#001529"
        text-color="#b7bdc3"
        active-text-color="#ffffff"
        router
      >
        <el-menu-item index="/admin/dashboard">
          <el-icon><PieChart /></el-icon>
          <template #title>Dashboard</template>
        </el-menu-item>

        <el-sub-menu index="users">
          <template #title>
            <el-icon><User /></el-icon>
            <span>Users</span>
          </template>
          <el-menu-item index="/admin/users">All Users</el-menu-item>
          <el-menu-item index="/admin/users/create">Create User</el-menu-item>
        </el-sub-menu>

        <el-menu-item index="/admin/products">
          <el-icon><ShoppingCart /></el-icon>
          <template #title>Products</template>
        </el-menu-item>

        <el-menu-item index="/admin/orders">
          <el-icon><Tickets /></el-icon>
          <template #title>Orders</template>
        </el-menu-item>

        <el-sub-menu index="settings">
          <template #title>
            <el-icon><Setting /></el-icon>
            <span>Settings</span>
          </template>
          <el-menu-item index="/admin/settings/general">General</el-menu-item>
          <el-menu-item index="/admin/settings/theme">Theme</el-menu-item>
        </el-sub-menu>
      </el-menu>
    </el-scrollbar>
  </el-aside>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { PieChart, User, ShoppingCart, Tickets, Setting, Menu } from '@element-plus/icons-vue';

const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false
  }
});

const route = useRoute();

const activeMenu = computed(() => {
  return route.path;
});
</script>

<style scoped>
.admin-sidebar {
  height: 100vh;
  background-color: #001529;
  transition: width 0.3s ease;
  position: relative;
  overflow: hidden;
  box-shadow: 2px 0 6px rgba(0, 21, 41, 0.35);
}

.logo-container {
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 18px;
  font-weight: 600;
  cursor: pointer;
  padding: 0 16px;
  overflow: hidden;
  white-space: nowrap;
}

.logo-text {
  color: white;
}

.sidebar-scrollbar {
  height: calc(100vh - 64px);
}

.sidebar-menu {
  border-right: none;
}

.sidebar-menu:not(.el-menu--collapse) {
  width: 220px;
}
</style>
