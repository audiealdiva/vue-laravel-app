<template>
  <div :class="['bg-sidebar', 'border-end', { collapsed }]" id="sidebar-wrapper">
    <div class="sidebar-heading text-center">
      <span v-if="!collapsed">Project Management</span>
    </div>
    <div class="list-group list-group-flush">
      <div class="d-flex flex-column text-center" style="padding: 1rem 0.5rem;">
        <span v-if="!collapsed">{{ user?.name }}</span>
        <small v-if="!collapsed" class="text-var(--border-color)">
          {{ user?.role_name }}
        </small>
      </div>
      <router-link class="list-group-item list-group-item-action" to="/dashboard">
        <i class="bi bi-house-door"></i>
        <span v-if="!collapsed">Dashboard</span>
      </router-link>
      <router-link v-if="user?.role_name === 'Administrator'"
        class="list-group-item list-group-item-action"
        to="/users">
        <i class="bi bi-person"></i>
        <span v-if="!collapsed">Users</span>
      </router-link>
      <router-link class="list-group-item list-group-item-action" to="/roles">
        <i class="bi bi-shield-lock"></i>
        <span v-if="!collapsed">Roles</span>
      </router-link>
      <router-link class="list-group-item list-group-item-action" to="/employees">
        <i class="bi bi-person"></i>
        <span v-if="!collapsed">Employee</span>
      </router-link>
      <router-link class="list-group-item list-group-item-action" to="/projects">
        <i class="bi bi-folder"></i>
        <span v-if="!collapsed">Project</span>
      </router-link>
      <router-link class="list-group-item list-group-item-action" to="/tasks">
        <i class="bi bi-check-square"></i>
        <span v-if="!collapsed">Task</span>
      </router-link>
    </div>
  </div>
</template>

<script setup>
defineProps({
  collapsed: Boolean,
  user: Object
})
</script>

<style scoped>
#sidebar-wrapper {
  min-width: 250px;
  max-width: 250px;
  background-color: var(--primary-color);
  color: var(--background-color);
  transition: all 0.3s ease;
  overflow-x: hidden;
  height: 100vh;
  padding-top: 0;
  padding-bottom: 0;
  padding-left: 0;
  padding-right: 0;
}

#sidebar-wrapper.collapsed {
  min-width: 70px;
  max-width: 70px;
  overflow-x: hidden;
}

.sidebar-heading {
  font-size: 1.2rem;
  padding: 1rem 0.5rem;
  height: 60px;
  background-color: var(--primary-color);
  color: var(--background-color);
  text-align: center;
  border-bottom: 1px solid var(--secondary-color);
}

.list-group {
  padding-top: 1rem;
}

.list-group-item {
  background-color: transparent;
  color: var(--background-color);
  border: none;
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  transition: background-color 0.2s ease;
}

.list-group-item:hover {
  background-color: var(--accent-color);
  text-decoration: none;
}

.list-group-item i {
  font-size: 1.2rem;
  margin-right: 15px;
  min-width: 24px;
  text-align: center;
}

.list-group-item span {
  transition: opacity 0.3s ease, visibility 0.3s ease;
}

#sidebar-wrapper.collapsed .list-group-item span,
#sidebar-wrapper.collapsed .sidebar-heading span,
#sidebar-wrapper.collapsed .sidebar-heading small {
  opacity: 0;
  visibility: hidden;
  width: 0;
  height: 0;
  overflow: hidden;
  display: inline-block;
}
</style>