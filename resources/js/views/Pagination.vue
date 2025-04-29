<template>
  <div class="pagination-container d-flex justify-content-between align-items-center">
    <div class="d-flex gap-2">
      <label for="items-per-page">Items per page:</label>
      <select v-model="filters.per_page" id="items-per-page" class="form-select w-auto">
        <option v-for="option in [5, 25, 50, 100]" :key="option" :value="option">
          {{ option }}
        </option>
      </select>
    </div>
    <div class="pagination-info">
      <span>
        {{ pagination.startIndex }} - {{ pagination.endIndex }} of {{ total }}
      </span>
    </div>
    <div class="pagination-controls d-flex gap-2">
      <button :disabled="pagination.page === 1" @click="changePage(1)" class="btn btn-sm btn-secondary">|&lt;</button>
      <button :disabled="pagination.page === 1" @click="changePage(pagination.page - 1)" class="btn btn-sm btn-secondary">&lt;</button>
      <button :disabled="pagination.page === pagination.totalPages" @click="changePage(pagination.page + 1)" class="btn btn-sm btn-secondary">&gt;</button>
      <button :disabled="pagination.page === pagination.totalPages" @click="changePage(pagination.totalPages)" class="btn btn-sm btn-secondary">&gt;|</button>
    </div>
  </div>
</template>

<script setup>
import { defineProps, watch } from 'vue'

const props = defineProps({
  filters: Object,
  pagination: Object,
  total: Number,
  loadTable: Function
})

const changePage = (page) => {
  if (page < 1 || page > props.pagination.totalPages) return
  props.pagination.page = page
  props.loadTable()
}

watch(() => props.filters.per_page, (newPerPage) => {
  props.pagination.perPage = newPerPage
  props.pagination.page = 1
  props.loadTable()
})
</script>

<style scoped>
.pagination-container {
  width: 100%;
  bottom: 0;
  background-color: var(--background-color);
  padding: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

</style>
