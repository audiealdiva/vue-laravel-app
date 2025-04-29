import { ref } from 'vue'
import { useRouter } from 'vue-router'

export function useAuth() {
  const router = useRouter()
  const isLoggedIn = ref(false)
  const user = ref(null)

  const refreshUser = () => {
    const token = localStorage.getItem('token')
    if (token) {
      isLoggedIn.value = true
      user.value = JSON.parse(localStorage.getItem('user')) || null
    } else {
      isLoggedIn.value = false
      user.value = null
    }
  }
  
  refreshUser()

  const logout = () => {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    isLoggedIn.value = false
    user.value = null
    router.push('/login')
  }

  return {
    isLoggedIn,
    user,
    logout,
    refreshUser
  }
}
