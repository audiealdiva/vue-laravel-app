<template>
  <div class="container">
    <h2>Employee List</h2>

    <div class="mb-3 d-flex justify-content-between align-items-center">
      <div class="d-flex gap-2">
        <button class="btn btn-primary" @click="openForm('Insert')" title="Add New">
          <i class="bi bi-plus-lg"></i>
        </button>
        <button class="btn btn-primary" @click="loadTable" title="Refresh">
          <i class="bi bi-arrow-clockwise"></i>
        </button>
        <button class="btn btn-primary" @click="exportTable" title="Export">
          <i class="bi bi-file-earmark-arrow-down"></i>
        </button>
        <button class="btn btn-primary" @click="importTable" title="Import">
          <i class="bi bi-file-earmark-arrow-up"></i>
        </button>
      </div>
      <div class="d-flex gap-2">
        <input v-model="filters.search" placeholder="Search" class="form-control" />
        <select v-model="filters.is_active" class="form-select w-auto">
          <option value="">All Status</option>
          <option :value="1">Active</option>
          <option :value="0">Inactive</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="loading-bar">
      <div class="progress"></div>
    </div>

    <div class="table-container">
      <table class="table table-bordered table-striped">
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
            <th>Phone Number</th>
            <th>Department</th>
            <th>Job Title</th>
            <th>Gender</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="employee in employees" :key="employee.id">
            <td>{{ employee.name }}</td>
            <td>{{ employee.email }}</td>
            <td>{{ employee.metadata.phone_number || '' }}</td>
            <td>{{ employee.metadata.department || '' }}</td>
            <td>{{ employee.metadata.job_title || '' }}</td>
            <td>{{ employee.metadata.gender || '' }}</td>
            <td>
              <span :class="employee.is_active ? 'text-success' : 'text-danger'">
                {{ employee.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td>{{ formatDateTime(employee.created_at) }}</td>
            <td>
              <button class="btn btn-primary btn-sm me-2" @click="openForm('Update', employee)">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-danger btn-sm me-2" @click="openForm('Delete', employee)">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="message.text" :class="`alert alert-${message.type} notification`">
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
                <label for="name" class="form-label">Name</label>
                <input
                  type="text"
                  id="name"
                  v-model="form.name"
                  class="form-control"
                  required
                  :readonly="formAction === 'Delete'"/>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                  type="email"
                  id="email"
                  v-model="form.email"
                  class="form-control"
                  required
                  :readonly="formAction === 'Delete'"/>
              </div>
              <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input
                  type="text"
                  id="phone_number"
                  v-model="form.metadata.phone_number"
                  class="form-control"
                  :readonly="formAction === 'Delete'"/>
              </div>
              <div class="mb-3">
                <label for="department" class="form-label">Department</label>
                <input
                  type="text"
                  id="department"
                  v-model="form.metadata.department"
                  class="form-control"
                  :readonly="formAction === 'Delete'"/>
              </div>
              <div class="mb-3">
                <label for="job_title" class="form-label">Job Title</label>
                <input
                  type="text"
                  id="job_title"
                  v-model="form.metadata.job_title"
                  class="form-control"
                  :readonly="formAction === 'Delete'"/>
              </div>
              <div class="mb-3">
                <label for="gender" class="form-label">Gender</label>
                <select
                  id="gender"
                  v-model="form.metadata.gender"
                  class="form-select"
                  :disabled="formAction === 'Delete'">
                  <option :value="'Male'">Male</option>
                  <option :value="'Female'">Female</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select
                  id="status"
                  v-model="form.is_active"
                  class="form-select"
                  :disabled="formAction === 'Delete'"
                  required>
                  <option :value="1">Active</option>
                  <option :value="0">Inactive</option>
                </select>
              </div>
              <div v-if="Object.keys(formErrors).length" class="alert alert-danger">
                <ul class="mb-0">
                  <li v-for="(messages, field) in formErrors" :key="field">
                    {{ messages[0] }}
                  </li>
                </ul>
              </div>
              <div class="text-end">
                <button type="submit" class="btn btn-primary">{{ formAction }}</button>
              </div>

              <AuditTrail
                v-if="formAction !== 'Insert' && employeeId"
                :model="'Employee'"
                :model-id="employeeId"
                />
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
import dayjs from 'dayjs'
import Pagination from './Pagination.vue'
import AuditTrail from './AuditTrail.vue'

const employees = ref([])
const total = ref(0)
const showModal = ref(false)
const form = ref({ 
  name: '', 
  email: '', 
  is_active: '', 
  metadata: {
    job_title: '',
    gender: '',
    department: '',
    phone_number: ''
  }
})
const formTitle = ref('Insert New Employee')
const formAction = ref('Insert')
const employeeId = ref(null)
const message = ref({ text: '', type: '' })
const loading = ref(false)
const formErrors = ref({})
const formatDateTime = (dateString) => {
  return dayjs(dateString).format('DD-MM-YYYY HH:mm')
}
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

watch(() => filters.value.is_active, () => {
  pagination.value.page = 1
  loadTable()
})

const loadTable = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/employees', {
      params: {
        ...filters.value,
      },
    })
    employees.value = res.data.data.map(employee => ({
      ...employee,
      metadata: employee.metadata ? JSON.parse(employee.metadata) : {}
    }))
    total.value = res.data.total_data
    updatePagination()
  } catch (error) {
    console.error('Error fetching employees:', error)
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

const openForm = (action, employee = null) => {
  const defaultMetadata = {
    job_title: '',
    gender: '',
    department: '',
    phone_number: '',
  }

  form.value = {
    ...(employee || {}),
    is_active: action === 'Insert' ? 1 : (employee?.is_active ? 1 : 0),
    metadata: employee?.metadata 
      ? typeof employee.metadata === 'string' 
        ? JSON.parse(employee.metadata) 
        : employee.metadata
      : { ...defaultMetadata }
  }

  formTitle.value = `${action} Employee`
  formAction.value = action
  formErrors.value = {}
  employeeId.value = employee?.id || null
  showModal.value = true
}

const submitForm = async () => {
  try {
    const updatedForm = { ...form.value, 
      metadata: JSON.stringify(form.value.metadata)
    }
    
    let response
    switch (formAction.value) {
      case 'Insert':
        response = await axios.post('/api/employees', updatedForm)
        break
      case 'Update':
        response = await axios.put(`/api/employees/${employeeId.value}`, updatedForm)
        break
      case 'Delete':
        response = await axios.delete(`/api/employees/${employeeId.value}`)
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

const exportTable = async () => {
  try {
    const response = await axios.post('/export', {
      fields: ['name', 'email', 'metadata', 'is_active', 'created_at']
    });
    
    alert(response.data.message); 
  } catch (error) {

    console.error("Error exporting:", error);
    alert('An error occurred while exporting the data.');
  }
}

const importTable = async () => {

}

onMounted(() => {
  loadTable()
})
</script>

<style scoped>
.container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.table-wrapper {
  flex-grow: 1;
}

.pagination {
  margin-top: 10px;
}
</style>