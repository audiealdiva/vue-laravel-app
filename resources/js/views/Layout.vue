<template>
  <div class="d-flex" id="wrapper">
    <Sidebar :collapsed="isCollapsed" :user="user" />

    <div id="page-content-wrapper" class="w-100">
      <Header @toggle-sidebar="toggleSidebar" @logout="logout" />
      <div class="container-fluid mt-4">
        <div class="content">
          <router-view />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuth } from '@/composables/useAuth'
import Sidebar from './Sidebar.vue'
import Header from './Header.vue'

const { user, logout } = useAuth()
const isCollapsed = ref(false)

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value
}
</script>

<style scoped>
#page-content-wrapper {
  flex-grow: 1;
  overflow-y: auto; 
  height: 100vh;
}

.content {
  max-height: calc(100vh - 100px);
  overflow-y: auto;
}
</style>
