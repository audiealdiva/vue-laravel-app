<template>
  <div class="audit-trail mt-4">
    <h5>Audit Trail</h5>
    <div v-if="loading">Loading audit trail...</div>
    <ul v-else class="list-group">
      <li v-for="log in logs" :key="log.id" class="list-group-item">
        <div>
          <strong>{{ log.event }} at {{ formatDateTime(log.created_at) }}</strong>
        </div>
        <div v-if="log.old_values && log.old_values != '[]'">
          <strong>Old Values</strong>
          <pre>{{ parseMetadata(log.old_values) }}</pre>
        </div>
        <div v-if="log.new_values && log.new_values != '[]'">
          <strong>New Values</strong>
          <pre>{{ parseMetadata(log.new_values) }}</pre>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import dayjs from 'dayjs'

const props = defineProps({
  model: { type: String, required: true },
  modelId: { type: [String, Number], required: true }, 
})

const logs = ref([])
const loading = ref(false)

const parseMetadata = (metadata) => {
  try {
    let parsedData = JSON.parse(metadata.replace(/\\"/g, '"'));
    
    return JSON.stringify(parsedData, null, 2); 
  } catch (error) {
    console.error('Error parsing metadata:', error);
    return metadata;
  }
}


const loadAuditTrail = async () => {
  loading.value = true
  try {
    const res = await axios.get(`/api/audit-trail`, {
      params: {
        auditable_type: `App\\Models\\${props.model}`,
        auditable_id: props.modelId,
      },
    })
    logs.value = res.data
  } catch (error) {
    console.error('Failed to load audit logs', error)
  } finally {
    loading.value = false
  }
}

const formatDateTime = (value) => {
  return dayjs(value).format('DD-MM-YYYY HH:mm')
}

onMounted(loadAuditTrail)
watch(() => props.modelId, loadAuditTrail)
</script>
