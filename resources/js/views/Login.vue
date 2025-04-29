<template>
  <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card p-4" style="max-width: 400px; width: 100%;">
      <h2 class="text-center mb-4">Login</h2>
      <form @submit.prevent="login">
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input v-model="email" type="email" id="email" class="form-control" placeholder="Email" required />
        </div>
        
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input v-model="password" type="password" id="password" class="form-control" placeholder="Password" required />
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>

      <p v-if="error" class="text-danger mt-3">{{ error }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const email = ref('')
const password = ref('')
const error = ref('')
const router = useRouter()
const { refreshUser } = useAuth()

const login = async () => {
  try {
    await axios.get('/sanctum/csrf-cookie')

    const res = await axios.post('/api/login', {
      email: email.value,
      password: password.value,
    })

    localStorage.setItem('token', res.data.token)
    localStorage.setItem('user', JSON.stringify(res.data.user))
    
    refreshUser()
    await nextTick()

    router.push('/dashboard')
  } catch (err) {
    console.log(err)
    error.value = err.response?.data?.message || 'Login Failed'
  }
}

</script>
