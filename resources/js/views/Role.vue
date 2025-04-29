<template>
  <div class="container mt-4">
    <h2>Role Management</h2>

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
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="role in roles" :key="role.id">
          <td>{{ role.name }}</td>
          <td>
            <button class="btn btn-primary btn-sm me-2" @click="openForm('Update', role)">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-danger btn-sm" @click="openForm('Delete', role)">
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
            <button type="button" class="btn-close" @click="closeForm"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="mb-3">
                <label for="role" class="form-label">Role Name</label>
                <input type="text" id="role" v-model="form.name" class="form-control" required :readonly="formAction === 'Delete'" />
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

const roles = ref([])
const showModal = ref(false)
const form = ref({ name: '' })
const formTitle = ref('Insert New Role')
const formAction = ref('Insert')
const roleId = ref(null)
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
  loading.value = true
  try {
    const res = await axios.get('/api/roles', {
      params: {
        ...filters.value,
      },
    })
    roles.value = res.data?.data
    total.value = res.data.total_data
    updatePagination()
  } catch (error) {
    console.error('Error fetching roles:', error)
    const msg = error.response?.data?.message || 'An error occurred'
    showMessage(msg, 'error')
  } finally {
    loading.value = false
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

const openForm = (action, role = null) => {
  form.value = {
    ...(role || {}),
  }

  formTitle.value = `${action} Role`
  formAction.value = action
  formErrors.value = {}
  roleId.value = role?.id || null
  showModal.value = true
}

const submitForm = async () => {
  try {
    const updatedForm = { ...form.value }
    
    let response
    switch (formAction.value) {
      case 'Insert':
        response = await axios.post('/api/roles', updatedForm)
        break
      case 'Update':
        response = await axios.put(`/api/roles/${roleId.value}`, updatedForm)
        break
      case 'Delete':
        response = await axios.delete(`/api/roles/${roleId.value}`)
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

    formErrors.value = error.response?.data?.errors || {}
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

onMounted(loadTable)
</script>

<style scoped>
</style>
