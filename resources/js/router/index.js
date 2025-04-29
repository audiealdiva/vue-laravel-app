import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Layout from '../views/Layout.vue'
import Dashboard from '../views/Dashboard.vue'
import User from '../views/User.vue'
import Role from '../views/Role.vue'
import Employee from '../views/Employee.vue'
import Task from '../views/Task.vue'
import Project from '../views/Project.vue'

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name: 'Login', component: Login },
  { path: '/', component: Layout, meta: { requiresAuth: true },
    children: [
      { path: 'dashboard', component: Dashboard },
      { path: 'users', component: User, meta: { allowedRoles: ['Administrator'] }},
      { path: 'roles', component: Role },
      { path: 'employees', component: Employee },
      { path: 'projects', component: Project },
      { path: 'tasks', component: Task }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user'))

  if (to.meta.requiresAuth && !token) {
    next('/login')
  } else if (to.path === '/login' && token) {
    next('/dashboard')
  } else if (to.meta.allowedRoles && (!user || !to.meta.allowedRoles.includes(user.role_name))) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router
