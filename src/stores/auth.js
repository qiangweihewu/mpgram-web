import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    isAuthenticated: false,
    loading: false,
    error: null
  }),

  actions: {
    async login(phone) {
      this.loading = true
      this.error = null
      try {
        const res = await fetch('/api/login.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ phone })
        })
        const data = await res.json()
        if (data.success) {
          this.user = data.user
          this.isAuthenticated = true
        } else {
          this.error = data.error
        }
      } catch (err) {
        this.error = 'Login failed'
      } finally {
        this.loading = false
      }
    },

    async verifyCode(code) {
      this.loading = true
      this.error = null
      try {
        const res = await fetch('/api/verify.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ code })
        })
        const data = await res.json()
        if (data.success) {
          this.user = data.user
          this.isAuthenticated = true
        } else {
          this.error = data.error
        }
      } catch (err) {
        this.error = 'Verification failed'
      } finally {
        this.loading = false
      }
    },

    logout() {
      this.user = null
      this.isAuthenticated = false
    }
  }
})