<template>
  <el-aside
    class="admin-sidebar"
    :width="collapsed ? '64px' : '220px'"
    :class="{ collapsed }"
  >
    <div class="logo-container" @click="$emit('toggle-collapse')">
      <transition name="fade" mode="out-in">
        <img
          v-if="!collapsed"
          src="@/assets/logo-full.png"
          alt="Logo"
          class="logo-full"
          key="full"
        />
        <img
          v-else
          src="@/assets/logo-icon.png"
          alt="Logo"
          class="logo-icon"
          key="icon"
        />
      </transition>
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

<script>
import { PieChart, User, ShoppingCart, Tickets, Setting } from '@element-plus/icons-vue'
import { useRoute } from 'vue-router'

export default {
  name: 'AdminSidebar',
  props: {
    collapsed: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      route: useRoute()
    }
  },
  computed: {
    activeMenu() {
      return this.route.path
    }
  },
  components: {
    PieChart,
    User,
    ShoppingCart,
    Tickets,
    Setting
  }
}
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
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s;
}

.logo-container:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

.logo-full {
  height: 32px;
  transition: all 0.3s;
}

.logo-icon {
  height: 24px;
  transition: all 0.3s;
}

.sidebar-scrollbar {
  height: calc(100vh - 60px);
}

.sidebar-menu {
  border-right: none;
}

.sidebar-menu:not(.el-menu--collapse) {
  width: 100%;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
