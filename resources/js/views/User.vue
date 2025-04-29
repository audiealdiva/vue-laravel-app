<template>
  <div class="container mt-4">
    <h2>User Management</h2>

    <div class="mb-3 d-flex justify-content-between align-items-center">
      <div class="d-flex gap-2">
        <button class="btn btn-primary" @click="openForm('Insert')" title="Add New">
        <i class="bi bi-plus-lg"></i>
      </button>
      <button class="btn btn-primary" @click="loadTable" title="Refresh">
        <i class="bi bi-arrow-clockwise"></i>
      </button>
      </div>
      <div class="d-flex gap-2">
        <input v-model="filters.search" placeholder="Search" class="form-control" />
      </div>
    </div>

    <div v-if="loading" class="loading-bar">
      <div class="progress"></div>
    </div>
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
           <th @click="sortBy('name')" style="cursor: pointer">
              Name
              <span v-if="filters.sort_by === 'name'">
                <i v-if="filters.sort_direction === 'asc'" class="bi bi-arrow-up"></i>
                <i v-if="filters.sort_direction === 'desc'" class="bi bi-arrow-down"></i>
              </span>
            </th>
            <th @click="sortBy('email')" style="cursor: pointer">
              Email
              <span v-if="filters.sort_by === 'email'">
                <i v-if="filters.sort_direction === 'asc'" class="bi bi-arrow-up"></i>
                <i v-if="filters.sort_direction === 'desc'" class="bi bi-arrow-down"></i>
              </span>
            </th>
            <th @click="sortBy('role_id')" style="cursor: pointer">
              Role
              <span v-if="filters.sort_by === 'role_id'">
                <i v-if="filters.sort_direction === 'asc'" class="bi bi-arrow-up"></i>
                <i v-if="filters.sort_direction === 'desc'" class="bi bi-arrow-down"></i>
              </span>
            </th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>{{ user.role?.name || '' }}</td>
          <td>
            <button class="btn btn-primary btn-sm me-2" @click="openForm('Update', user)">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-danger btn-sm" @click="openForm('Delete', user)">
              <i class="bi bi-trash"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="message.text" :class="`alert alert-${message.type}`">
      {{ message.text }}
    </div>

    <Pagination 
      :filters="filters"
      :pagination="pagination"
      :total="total"
      :loadTable="loadTable"
    />
    
    <div v-if="showModal" class="modal" tabindex="-1" style="display: block;">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ formTitle }}</h5>
            <button type="button" class="btn-close" @click="closeForm">
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" v-model="form.name" class="form-control" required :readonly="formAction === 'Delete'"/>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" v-model="form.email" class="form-control" required :readonly="formAction === 'Delete'"/>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" v-model="form.password" class="form-control" required :readonly="formAction === 'Delete'"/>
              </div>
              <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select id="role" v-model="form.role_id" class="form-control" :disabled="formAction === 'Delete'">
                  <option v-for="role in roleList" :key="role.id" :value="role.id">{{ role.name }}</option>
                </select>
              </div>
              <div class="text-end">
                <button type="submit" class="btn btn-primary">{{ formAction }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import Pagination from './Pagination.vue'

const users = ref([])
const roleList = ref([])
const showModal = ref(false)
const form = ref({ name: '', email: '', password: '', role_id: null })
const formTitle = ref('Insert New User')
const formAction = ref('Insert')
const userId = ref(null)
const message = ref({ text: '', type: '' })
const loading = ref(false)
const formErrors = ref({})
const total = ref(0)
const pagination = ref({
  page: 1,
  perPage: 50,
  startIndex: 1,
  endIndex: 10,
  totalPages: 1
})
const filters = ref({
  search: '',
  is_active: '',
  sort_by: 'created_at',
  sort_direction: 'desc',
  per_page: pagination.value.perPage,
})

watch(() => filters.value, (newPerPage) => {
  pagination.value.perPage = newPerPage
  pagination.value.page = 1
  loadTable()
})

watch(() => filters.value.search, () => {
  pagination.value.page = 1
  loadTable()
})

const loadTable = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/users', {
      params: {
        ...filters.value,
      },
    })
    users.value = res.data?.data
    total.value = res.data.total_data
    updatePagination()
  } catch (error) {
    console.error('Error fetching users:', error)
    const msg = error.response?.data?.message || 'An error occurred'
    showMessage(msg, 'error')
  } finally {
    loading.value = false;
  }
}

const updatePagination = () => {
  pagination.value.totalPages = Math.ceil(total.value / pagination.value.perPage)
  pagination.value.startIndex = (pagination.value.page - 1) * pagination.value.perPage + 1
  pagination.value.endIndex = Math.min(pagination.value.page * pagination.value.perPage, total.value)
}

const sortBy = (field) => {
  if (filters.value.sort_by === field) {
    filters.value.sort_direction = filters.value.sort_direction === 'asc' ? 'desc' : 'asc'
  } else {
    filters.value.sort_by = field
    filters.value.sort_direction = 'asc'
  }
  loadTable()
}

const getRoleList = async () => {
  try {
    const res = await axios.get('/api/roles')
    roleList.value = res.data?.data
  } catch (error) {
    console.error('Error fetching roles:', error)
    const msg = error.response?.data?.message || 'An error occurred'
    showMessage(msg, 'error')
  }
}

const openForm = (action, user = null) => {
  form.value = {
    ...(user || {}),
    password: action !== 'Insert' ? '****' : user?.password,
  }

  formTitle.value = `${action} User`
  formAction.value = action
  formErrors.value = {}
  userId.value = user?.id || null
  showModal.value = true
}

const submitForm = async () => {
  try {
    const updatedForm = { ...form.value }

    if (form.value.password === '****') {
      delete updatedForm.password
    }

    let response;
    switch (formAction.value) {
      case 'Insert':
        response = await axios.post('/api/users', updatedForm)
        break
      case 'Update':
        response = await axios.put(`/api/users/${userId.value}`, updatedForm)
        break
      case 'Delete':
        response = await axios.delete(`/api/users/${userId.value}`)
        break
      default:
        throw new Error(`Unknown form action: ${formAction.value}`)
    }

    const msg = response?.data?.message || 'Operation successful'
    showMessage(msg, 'success')

    loadTable()
    closeForm()
  } catch (error) {
    console.error('Error:', error)
    const msg = error.response?.data?.message || 'An error occurred'
    showMessage(msg, 'error')
  }
}


const closeForm = () => {
  showModal.value = false
}

const showMessage = (text, type = 'success') => {
  message.value = { text, type }
  setTimeout(() => {
    message.value = { text: '', type: '' }
  }, 3000)
}

onMounted(() => {
  loadTable()
  getRoleList()
})
</script>

<style scoped>

</style>
