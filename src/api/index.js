import { createFetch } from '@vueuse/core'

const useApiFetch = createFetch({
  baseUrl: '/api',
  options: {
    beforeFetch({ options }) {
      options.headers = {
        ...options.headers,
        'Content-Type': 'application/json',
      }
      return { options }
    },
  },
})

export const api = {
  // Auth endpoints
  login: (phone) => useApiFetch('login.php').post({ phone }).json(),
  verifyCode: (code) => useApiFetch('verify.php').post({ code }).json(),
  logout: () => useApiFetch('logout.php').post().json(),

  // Chat endpoints
  getChats: () => useApiFetch('chats.php').get().json(),
  getMessages: (chatId) => useApiFetch(`messages.php?chat=${chatId}`).get().json(),
  sendMessage: (chatId, message) => useApiFetch('send.php').post({ chatId, message }).json(),
  
  // File endpoints
  uploadFile: (file) => {
    const formData = new FormData()
    formData.append('file', file)
    return useApiFetch('upload.php').post(formData).json()
  },

  // User endpoints
  getProfile: () => useApiFetch('profile.php').get().json(),
  updateProfile: (data) => useApiFetch('profile.php').post(data).json()
}