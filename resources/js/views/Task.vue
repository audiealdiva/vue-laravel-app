<template>
  <div class="container">
    <h2>Task List</h2>

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
        <select v-model="filters.status" class="form-select w-auto">
          <option value="">All Status</option>
          <option :value="1">Done</option>
          <option :value="0">Not Yet</option>
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
            <th @click="sortBy('project_id')" style="cursor: pointer">
              Project
              <span v-if="filters.sort_by === 'project_id'">
                <i v-if="filters.sort_direction === 'asc'" class="bi bi-arrow-up"></i>
                <i v-if="filters.sort_direction === 'desc'" class="bi bi-arrow-down"></i>
              </span>
            </th>
            <th @click="sortBy('employee_id')" style="cursor: pointer">
              Employee
              <span v-if="filters.sort_by === 'employee_id'">
                <i v-if="filters.sort_direction === 'asc'" class="bi bi-arrow-up"></i>
                <i v-if="filters.sort_direction === 'desc'" class="bi bi-arrow-down"></i>
              </span>
            </th>
            <th @click="sortBy('title')" style="cursor: pointer">
              Title
              <span v-if="filters.sort_by === 'title'">
                <i v-if="filters.sort_direction === 'asc'" class="bi bi-arrow-up"></i>
                <i v-if="filters.sort_direction === 'desc'" class="bi bi-arrow-down"></i>
              </span>
            </th>
            <th>Description</th>
            <th @click="sortBy('duedate')" style="cursor: pointer">
              Due Date
              <span v-if="filters.sort_by === 'duedate'">
                <i v-if="filters.sort_direction === 'asc'" class="bi bi-arrow-up"></i>
                <i v-if="filters.sort_direction === 'desc'" class="bi bi-arrow-down"></i>
              </span>
            </th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="task in tasks" :key="task.id">
            <td>{{ task.project?.name || '' }}</td>
            <td>{{ task.employee?.name || '' }}</td>
            <td>{{ task.title }}</td>
            <td>{{ task.description === undefined ? '' : task.description }}</td>
            <td>{{ task.duedate ? formatDate(task.duedate) : task.duedate }}</td>
            <td>
              <span :class="task.is_done ? 'text-success' : 'text-danger'">
                {{ task.is_done ? 'Done' : 'Not Yet' }}
              </span>
            </td>
            <td>{{ formatDateTime(task.created_at) }}</td>
            <td>
              <button class="btn btn-primary btn-sm me-2" @click="openForm('Update', task)">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-danger btn-sm me-2" @click="openForm('Delete', task)">
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
                <label for="project_id" class="form-label">Project</label>
                <select id="project_id" v-model="form.project_id" class="form-control" :disabled="formAction === 'Delete'">
                  <option v-for="project in projectList" :key="project.id" :value="project.id">{{ project.name }}</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="employee_id" class="form-label">Employee</label>
                <select id="employee_id" v-model="form.employee_id" class="form-control" :disabled="formAction === 'Delete'">
                  <option v-for="employee in employeeList" :key="employee.id" :value="employee.id">{{ employee.name }}</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input
                  type="text"
                  id="title"
                  v-model="form.title"
                  class="form-control"
                  required
                  :readonly="formAction === 'Delete'"
                />
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
                <label for="duedate" class="form-label">Due Date</label>
                <input
                  type="date"
                  id="duedate"
                  v-model="form.duedate"
                  class="form-control"
                  :readonly="formAction === 'Delete'"
                />
              </div>
              <div class="mb-3">
                <label for="document" class="form-label">Document (PDF)</label>
                <input
                  type="file"
                  id="document"
                  @change="handleFileUpload"
                  class="form-control"
                  accept=".pdf"
                  :disabled="formAction === 'Delete'"
                />
              </div>
              <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select
                  id="status"
                  v-model="form.is_done"
                  class="form-select"
                  :disabled="formAction === 'Delete'"
                  required>
                  <option :value="0">Progress</option>
                  <option :value="1">Completed</option>
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
                v-if="formAction !== 'Insert' && taskId"
                :model="'Task'"
                :model-id="taskId"
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

const tasks = ref([])
const employeeList = ref([])
const projectList = ref([])
const total = ref(0)
const showModal = ref(false)
const form = ref({
  title: '',
  description: '',
  is_done: 0,
  duedate: '',
  document: ''
})
const formTitle = ref('Insert New Task')
const formAction = ref('Insert')
const taskId = ref(null)
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
  status: '',
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

watch(() => filters.value.is_done, () => {
  pagination.value.page = 1
  loadTable()
})

const loadTable = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/tasks', { params: { ...filters.value } })
    tasks.value = res.data.data
    total.value = res.data.total_data
    updatePagination()
  } catch (error) {
    console.error('Error fetching tasks:', error)
    showMessage(error.response?.data?.message || 'Error loading tasks', 'danger')
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

const getEmployeeList = async () => {
  try {
    const res = await axios.get('/api/employees')
    employeeList.value = res.data?.data
  } catch (error) {
    console.error('Error fetching employees:', error)
    const msg = error.response?.data?.message || 'An error occurred'
    showMessage(msg, 'error')
  }
}

const getProjectList = async () => {
  try {
    const res = await axios.get('/api/projects')
    projectList.value = res.data?.data
  } catch (error) {
    console.error('Error fetching projects:', error)
    const msg = error.response?.data?.message || 'An error occurred'
    showMessage(msg, 'error')
  }
}

const openForm = (action, task = null) => {
  const formatDate = (dateString) => dayjs(dateString).format('YYYY-MM-DD')

  form.value = {
    ...(task || {}),
    duedate: action === 'Insert' ? '' : (task.duedate ? formatDate(task.duedate) : task.duedate),
    is_done: action === 'Insert' ? 0 : (task?.is_done ? 1 : 0),
  }

  formTitle.value = `${action} Task`
  formAction.value = action
  taskId.value = task?.id || null
  formErrors.value = {}
  showModal.value = true
}

const closeForm = () => {
  showModal.value = false
}

const showMessage = (text, type = 'success') => {
  message.value = { text, type }
  setTimeout(() => (message.value = { text: '', type: '' }), 3000)
}

const submitForm = async () => {
  try {
    const formData = new FormData();
    formData.append('title', form.value.title);
    formData.append('description', form.value.description ?? '');
    formData.append('is_done', String(form.value.is_done));
    formData.append('duedate', form.value.duedate ?? '');
    formData.append('project_id', form.value.project_id);
    formData.append('employee_id', form.value.employee_id);

    if (form.value.document) {
      formData.append('document', form.value.document);
    }

    let response;
    if (formAction.value === 'Insert') {
      response = await axios.post('/api/tasks', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    } else if (formAction.value === 'Update') {
      formData.append('_method', 'PUT');
      response = await axios.post(`/api/tasks/${taskId.value}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    } else if (formAction.value === 'Delete') {
      response = await axios.delete(`/api/tasks/${taskId.value}`);
    }

    showMessage(response.data.message || `${formAction.value} successful`, 'success');
    loadTable();
    closeForm();
  } catch (error) {
    console.error('Error:', error);
    const msg = error.response?.data?.message || 'An error occurred';
    showMessage(msg, 'error');
    formErrors.value = error.response?.data?.errors || {};
  }
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  formErrors.value = {}

  if (file && file.type === 'application/pdf') {

    if (file.size >= 102400 && file.size <= 512000) {
      form.value.document = file;
    } else {
      formErrors.value = {
        document: ['File size must be between 100KB and 500KB.']
      };
    }
  } else {
    formErrors.value = {
        document: ['Please upload a valid PDF file.']
      };
  }
};


const exportTable = async () => {

}

const importTable = async () => {

}

onMounted(() => {
  loadTable()
  getProjectList()
  getEmployeeList()
})
</script>
