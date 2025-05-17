<template>
    <el-header class="admin-header">
      <div class="header-container">
        <!-- Logo and Brand -->
        <div class="brand-section">
          <router-link to="/dashboard" class="brand-link">
            <el-icon class="logo-icon"><Platform /></el-icon>
            <span class="brand-name">AdminPro</span>
          </router-link>
        </div>
  
        <!-- Navigation Controls -->
        <div class="nav-controls">
          <el-tooltip content="Toggle Sidebar" placement="bottom">
            <el-button 
              class="nav-btn" 
              :icon="Menu" 
              circle 
              @click="toggleSidebar"
            />
          </el-tooltip>
          
          <el-tooltip content="Fullscreen" placement="bottom">
            <el-button 
              class="nav-btn" 
              :icon="FullScreen" 
              circle 
              @click="toggleFullscreen"
            />
          </el-tooltip>
        </div>
  
        <!-- Search Bar -->
        <div class="search-section">
          <el-input
            v-model="searchQuery"
            placeholder="Search..."
            class="search-input"
            clearable
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
        </div>
  
        <!-- User Menu -->
        <div class="user-section">
          <el-dropdown trigger="click" @command="handleUserCommand">
            <div class="user-profile">
              <el-avatar :size="36" :src="user.avatar" class="user-avatar">
                {{ userInitials }}
              </el-avatar>
              <div class="user-info">
                <span class="user-name">{{ user.name }}</span>
                <span class="user-role">{{ user.role }}</span>
              </div>
              <el-icon class="dropdown-icon"><ArrowDown /></el-icon>
            </div>
            
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item command="profile">
                  <el-icon><User /></el-icon> My Profile
                </el-dropdown-item>
                <el-dropdown-item command="settings">
                  <el-icon><Setting /></el-icon> Settings
                </el-dropdown-item>
                <el-dropdown-item divided command="logout">
                  <el-icon><SwitchButton /></el-icon> Logout
                </el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
  
          <!-- Notification Bell -->
          <el-popover
            placement="bottom-end"
            trigger="click"
            :width="300"
          >
            <template #reference>
              <el-badge 
                :value="unreadNotifications" 
                :max="99" 
                class="notification-badge"
              >
                <el-button 
                  class="notification-btn" 
                  :icon="Bell" 
                  circle 
                  >
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none"><path d="m12.594 23.258l-.012.002l-.071.035l-.02.004l-.014-.004l-.071-.036q-.016-.004-.024.006l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.016-.018m.264-.113l-.014.002l-.184.093l-.01.01l-.003.011l.018.43l.005.012l.008.008l.201.092q.019.005.029-.008l.004-.014l-.034-.614q-.005-.019-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.003-.011l.018-.43l-.003-.012l-.01-.01z"/><path fill="currentColor" d="M12 2a7 7 0 0 0-7 7v3.528a1 1 0 0 1-.105.447l-1.717 3.433A1.1 1.1 0 0 0 4.162 18h15.676a1.1 1.1 0 0 0 .984-1.592l-1.716-3.433a1 1 0 0 1-.106-.447V9a7 7 0 0 0-7-7m0 19a3 3 0 0 1-2.83-2h5.66A3 3 0 0 1 12 21"/></g></svg>
                </el-button>
              </el-badge>
            </template>
            
            <div class="notification-popover">
              <div class="notification-header">
                <h4>Notifications</h4>
                <el-button 
                  type="text" 
                  size="small"
                  @click="markAllAsRead"
                >
                  Mark all as read
                </el-button>
              </div>
              
              <el-scrollbar max-height="300px">
                <div 
                  v-for="notification in notifications" 
                  :key="notification.id"
                  class="notification-item"
                  :class="{ unread: !notification.read }"
                >
                  <el-avatar :size="40" :src="notification.avatar" />
                  <div class="notification-content">
                    <p class="notification-message">{{ notification.message }}</p>
                    <p class="notification-time">{{ notification.time }}</p>
                  </div>
                </div>
              </el-scrollbar>
              
              <div class="notification-footer">
                <el-button type="text" @click="viewAllNotifications">
                  View All Notifications
                </el-button>
              </div>
            </div>
          </el-popover>
        </div>
      </div>
    </el-header>
  </template>
  
  <script>
  import { 
    Platform, 
    Menu, 
    FullScreen, 
    Search, 
    User, 
    Setting, 
    SwitchButton, 
    Bell, 
    ArrowDown 
  } from '@element-plus/icons-vue'
  
  export default {
    name: 'AdminHeader',
    components: {
      Platform,
      Menu,
      FullScreen,
      Search,
      User,
      Setting,
      SwitchButton,
      Bell,
      ArrowDown
    },
    data() {
      return {
        searchQuery: '',
        user: {
          name: 'John Doe',
          role: 'Administrator',
          avatar: 'https://randomuser.me/api/portraits/men/1.jpg'
        },
        notifications: [
          {
            id: 1,
            message: 'New user registered',
            time: '10 min ago',
            read: false,
            avatar: 'https://randomuser.me/api/portraits/women/1.jpg'
          },
          {
            id: 2,
            message: 'System update available',
            time: '1 hour ago',
            read: true,
            avatar: 'https://randomuser.me/api/portraits/men/2.jpg'
          },
          {
            id: 3,
            message: 'New order received',
            time: '2 hours ago',
            read: false,
            avatar: 'https://randomuser.me/api/portraits/women/2.jpg'
          }
        ]
      }
    },
    computed: {
      userInitials() {
        return this.user.name.split(' ').map(n => n[0]).join('')
      },
      unreadNotifications() {
        return this.notifications.filter(n => !n.read).length
      }
    },
    methods: {
      toggleSidebar() {
        this.$emit('toggle-sidebar')
      },
      toggleFullscreen() {
        if (!document.fullscreenElement) {
          document.documentElement.requestFullscreen()
        } else {
          if (document.exitFullscreen) {
            document.exitFullscreen()
          }
        }
      },
      handleUserCommand(command) {
        switch (command) {
          case 'profile':
            this.$router.push('/profile')
            break
          case 'settings':
            this.$router.push('/settings')
            break
          case 'logout':
            this.logout()
            break
        }
      },
      logout() {
        // Implement logout logic
        this.$message.success('Logged out successfully')
        this.$router.push('/login')
      },
      markAllAsRead() {
        this.notifications = this.notifications.map(n => ({ ...n, read: true }))
        this.$message.success('All notifications marked as read')
      },
      viewAllNotifications() {
        this.$router.push('/notifications')
      }
    }
  }
  </script>
  
  <style scoped>
  .admin-header {
    height: 64px;
    background-color: #fff;
    box-shadow: 0 1px 4px rgba(0, 21, 41, 0.08);
    padding: 0 20px;
    display: flex;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 1000;
  }
  
  .header-container {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  
  .brand-section {
    display: flex;
    align-items: center;
  }
  
  .brand-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: inherit;
  }
  
  .logo-icon {
    font-size: 24px;
    color: #409eff;
    margin-right: 10px;
  }
  
  .brand-name {
    font-size: 18px;
    font-weight: 600;
    color: #303133;
  }
  
  .nav-controls {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  .nav-btn {
    border: none;
    background-color: transparent;
    color: #606266;
  }
  
  .nav-btn:hover {
    background-color: #f5f7fa;
    color: #409eff;
  }
  
  .search-section {
    flex: 1;
    max-width: 400px;
    margin: 0 20px;
  }
  
  .search-input {
    transition: all 0.3s;
  }
  
  .search-input:focus-within {
    box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.2);
  }
  
  .user-section {
    display: flex;
    align-items: center;
    gap: 16px;
  }
  
  .user-profile {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.3s;
  }
  
  .user-profile:hover {
    background-color: #f5f7fa;
  }
  
  .user-info {
    display: flex;
    flex-direction: column;
    margin: 0 8px;
  }
  
  .user-name {
    font-size: 14px;
    font-weight: 500;
    color: #303133;
  }
  
  .user-role {
    font-size: 12px;
    color: #909399;
  }
  
  .dropdown-icon {
    margin-left: 4px;
    color: #909399;
    font-size: 12px;
  }
  
  .notification-badge {
    margin-right: 8px;
  }
  
  .notification-btn {
    border: none;
    background-color: transparent;
    color: #606266;
  }
  
  .notification-btn:hover {
    background-color: #f5f7fa;
    color: #409eff;
  }
  
  .notification-popover {
    padding: 0;
  }
  
  .notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid #ebeef5;
  }
  
  .notification-header h4 {
    margin: 0;
    font-size: 16px;
    color: #303133;
  }
  
  .notification-item {
    display: flex;
    padding: 12px 16px;
    gap: 12px;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  
  .notification-item:hover {
    background-color: #f5f7fa;
  }
  
  .notification-item.unread {
    background-color: #f0f7ff;
  }
  
  .notification-content {
    flex: 1;
  }
  
  .notification-message {
    margin: 0;
    font-size: 14px;
    color: #303133;
  }
  
  .notification-time {
    margin: 4px 0 0;
    font-size: 12px;
    color: #909399;
  }
  
  .notification-footer {
    padding: 8px 16px;
    border-top: 1px solid #ebeef5;
    text-align: center;
  }
  </style>