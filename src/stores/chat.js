import { defineStore } from 'pinia'
import { api } from '../api'

export const useChatStore = defineStore('chat', {
  state: () => ({
    chats: [],
    currentChat: null,
    messages: [],
    loading: false,
    error: null
  }),

  actions: {
    async loadChats() {
      this.loading = true
      try {
        const { data } = await api.getChats()
        this.chats = data
      } catch (err) {
        this.error = 'Failed to load chats'
      } finally {
        this.loading = false
      }
    },

    async loadMessages(chatId) {
      this.loading = true
      try {
        const { data } = await api.getMessages(chatId)
        this.messages = data
        this.currentChat = this.chats.find(c => c.id === chatId)
      } catch (err) {
        this.error = 'Failed to load messages'
      } finally {
        this.loading = false
      }
    },

    async sendMessage(chatId, message) {
      try {
        await api.sendMessage(chatId, message)
        await this.loadMessages(chatId)
      } catch (err) {
        this.error = 'Failed to send message'
      }
    }
  }
})