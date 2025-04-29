<template>
  <div class="container">
    <h2>Project List</h2>

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
        <select v-model="filters.is_completed" class="form-select w-auto">
          <option value="">All Status</option>
          <option :value="1">Completed</option>
          <option :value="0">On Going</option>
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
                <i :class="filters.sort_direction === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
              </span>
            </th>
            <th>Description</th>
            <th @click="sortBy('datestart')" style="cursor: pointer">
              Date Start
              <span v-if="filters.sort_by === 'datestart'">
                <i :class="filters.sort_direction === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
              </span>
            </th>
            <th @click="sortBy('dateend')" style="cursor: pointer">
              Date End
              <span v-if="filters.sort_by === 'dateend'">
                <i :class="filters.sort_direction === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
              </span>
            </th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="project in projects" :key="project.id">
            <td>{{ project.name }}</td>
            <td>{{ project.description }}</td>
            <td>{{ project.datestart ? formatDate(project.datestart) : project.datestart }}</td>
            <td>{{ project.dateend ? formatDate(project.dateend) : project.dateend }}</td>
            <td>
              <span :class="project.is_completed ? 'text-success' : 'text-danger'">
                {{ project.is_completed ? 'Completed' : 'On Going' }}
              </span>
            </td>
            <td>{{ formatDateTime(project.created_at) }}</td>
            <td>
              <button class="btn btn-primary btn-sm me-2" @click="openForm('Update', project)">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-danger btn-sm me-2" @click="openForm('Delete', project)">
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
                  :readonly="formAction === 'Delete'" />
              </div>
              <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea
                  id="description"
                  v-model="form.description"
                  class="form-control"
                  rows="3"
                  :readonly="formAction === 'Delete'"
                ></textarea>
              </div>
              <div class="mb-3">
                <label for="datestart" class="form-label">Date Start</label>
                <input
                  type="date"
                  id="datestart"
                  v-model="form.datestart"
                  class="form-control"
                  :readonly="formAction === 'Delete'"
                />
              </div>
              <div class="mb-3">
                <label for="dateend" class="form-label">Date End</label>
                <input
                  type="date"
                  id="dateend"
                  v-model="form.dateend"
                  class="form-control"
                  :readonly="formAction === 'Delete'"
                />
              </div>
              <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select
                  id="status"
                  v-model="form.is_completed"
                  class="form-select"
                  :disabled="formAction === 'Delete'">
                  <option :value="1">Completed</option>
                  <option :value="0">On Going</option>
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
                v-if="formAction !== 'Insert' && projectId"
                :model="'Project'"
                :model-id="projectId"
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

const projects = ref([])
const total = ref(0)
const showModal = ref(false)
const form = ref({
  name: '',
  description: '',
  datestart: '',
  dateend: '',
  is_completed: 1,
})
const formTitle = ref('')
const formAction = ref('Insert')
const projectId = ref(null)
const message = ref({ text: '', type: '' })
const loading = ref(false)
const formErrors = ref({})
const formatDateTime = (dateString) => dayjs(dateString).format('DD-MM-YYYY HH:mm')
const formatDate = (dateString) => dayjs(dateString).format('DD-MM-YYYY')

const pagination = ref({
  page: 1,
  perPage: 50,
  startIndex: 1,
  endIndex: 10,
  totalPages: 1
})

const filters = ref({
  search: '',
  is_completed: '',
  sort_by: 'created_at',
  sort_direction: 'desc',
  per_page: pagination.value.perPage,
})

watch(() => filters.value.per_page, (newPerPage) => {
  pagination.value.perPage = newPerPage
  pagination.value.page = 1
  loadTable()
})

watch(() => filters.value.search, () => {
  pagination.value.page = 1
  loadTable()
})

watch(() => filters.value.is_completed, () => {
  pagination.value.page = 1
  loadTable()
})

const loadTable = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/projects', {
      params: { ...filters.value },
    })
    projects.value = res.data.data
    total.value = res.data.total_data
    updatePagination()
  } catch (error) {
    console.error(error)
    showMessage('Error loading data', 'danger')
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

const openForm = (action, project = null) => {
  const formatDate = (dateString) => dayjs(dateString).format('YYYY-MM-DD')

  form.value = {
    ...(project || {}),
    datestart: action === 'Insert' ? '' : (project.datestart ? formatDate(project.datestart) : project.datestart),
    dateend: action === 'Insert' ? '' : (project.dateend ? formatDate(project.dateend) : project.dateend),
    is_completed: action === 'Insert' ? 0 : (project?.is_completed ? 1 : 0),
  }

  formTitle.value = `${action} Project`
  formAction.value = action
  projectId.value = project?.id || null
  formErrors.value = {}
  showModal.value = true
}

const submitForm = async () => {
  try {
    const updatedForm = { ...form.value }

    let response
    switch (formAction.value) {
      case 'Insert':
        response = await axios.post('/api/projects', updatedForm)
        break
      case 'Update':
        response = await axios.put(`/api/projects/${projectId.value}`, updatedForm)
        break
      case 'Delete':
        response = await axios.delete(`/api/projects/${projectId.value}`)
        break
      default:
        throw new Error(`Unknown form action: ${formAction.value}`)
    }

    const msg = response?.data?.message || 'Operation successful'
    showMessage(msg, 'success')

    closeForm()
    loadTable()
  } catch (err) {
    formErrors.value = err.response?.data?.errors || {}
    showMessage(err.response?.data?.message || 'Error occurred', 'danger')
  }
}

const closeForm = () => {
  showModal.value = false
}

const showMessage = (text, type = 'success') => {
  message.value = { text, type }
  setTimeout(() => (message.value.text = ''), 3000)
}

const exportTable = async () => {

}

const importTable = async () => {

}

onMounted(() => {
  loadTable()
})
</script>