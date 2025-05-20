<template>
  <div class="chat-container">
    <header class="chat-header">
      <div class="chat-header-content">
        <router-link to="/chats" class="back-button">
          {{ $t('back') }}
        </router-link>
        <div class="chat-info">
          <h2>{{ chatName }}</h2>
          <span v-if="onlineStatus" class="online-status">
            {{ onlineStatus }}
          </span>
        </div>
      </div>
    </header>

    <main class="messages-container" ref="messagesContainer">
      <div v-for="message in messages" 
           :key="message.id"
           :class="['message', message.isOutgoing ? 'outgoing' : 'incoming']">
        <div class="message-content">
          <span class="message-text">{{ message.text }}</span>
          <span class="message-time">{{ formatTime(message.time) }}</span>
        </div>
      </div>
    </main>

    <footer class="message-input">
      <form @submit.prevent="sendMessage">
        <textarea 
          v-model="newMessage"
          @keydown.enter.prevent="sendMessage"
          placeholder="Write a message..."
          rows="1"
        ></textarea>
        <button type="submit">
          {{ $t('send') }}
        </button>
      </form>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { formatDistanceToNow } from 'date-fns'

const { t } = useI18n()
const route = useRoute()
const messages = ref([])
const newMessage = ref('')
const messagesContainer = ref(null)
const chatName = ref('')
const onlineStatus = ref('')

const scrollToBottom = async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const formatTime = (timestamp) => {
  return formatDistanceToNow(new Date(timestamp), { addSuffix: true })
}

const sendMessage = async () => {
  if (!newMessage.value.trim()) return
  
  try {
    const res = await fetch('/api/send.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        chatId: route.params.id,
        message: newMessage.value
      })
    })
    
    if (res.ok) {
      newMessage.value = ''
      await loadMessages()
      scrollToBottom()
    }
  } catch (err) {
    console.error('Failed to send message:', err)
  }
}

const loadMessages = async () => {
  try {
    const res = await fetch(`/api/messages.php?chat=${route.params.id}`)
    const data = await res.json()
    messages.value = data.messages
    chatName.value = data.chat.name
    onlineStatus.value = data.chat.status
    scrollToBottom()
  } catch (err) {
    console.error('Failed to load messages:', err)
  }
}

onMounted(() => {
  loadMessages()
  // Set up polling for new messages
  const pollInterval = setInterval(loadMessages, 5000)
  onUnmounted(() => clearInterval(pollInterval))
})
</script>

<style scoped>
.chat-container {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.chat-header {
  padding: 1rem;
  background: var(--primary-gradient);
  color: white;
}

.chat-header-content {
  max-width: 768px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.back-button {
  color: white;
  text-decoration: none;
}

.chat-info {
  flex: 1;
}

.chat-info h2 {
  margin: 0;
  font-size: 1.2rem;
}

.online-status {
  font-size: 0.8rem;
  opacity: 0.8;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  background: var(--bg-color);
}

.message {
  margin-bottom: 1rem;
  max-width: 70%;
}

.message-content {
  padding: 0.8rem;
  border-radius: 12px;
  background: white;
  box-shadow: var(--box-shadow);
}

.message.outgoing {
  margin-left: auto;
  .message-content {
    background: var(--primary-gradient);
    color: white;
  }
}

.message-time {
  display: block;
  font-size: 0.8rem;
  opacity: 0.7;
  margin-top: 0.3rem;
}

.message-input {
  padding: 1rem;
  background: white;
  border-top: 1px solid var(--border-color);
}

.message-input form {
  max-width: 768px;
  margin: 0 auto;
  display: flex;
  gap: 1rem;
}

textarea {
  flex: 1;
  padding: 0.8rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  resize: none;
  font-family: inherit;
}

button {
  padding: 0.8rem 1.5rem;
  background: var(--primary-gradient);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}
</style>