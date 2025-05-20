&lt;template>
  <div class="chat-list">
    <div v-for="chat in chats" 
         :key="chat.id"
         class="chat-item"
         @click="selectChat(chat)">
      <div class="chat-avatar">
        <img :src="chat.avatar" :alt="chat.name">
      </div>
      <div class="chat-info">
        <h3>{{ chat.name }}</h3>
        <p>{{ chat.lastMessage }}</p>
      </div>
      <div class="chat-meta">
        <span class="chat-time">{{ formatTime(chat.lastMessageTime) }}</span>
        <span v-if="chat.unreadCount" class="unread-count">
          {{ chat.unreadCount }}
        </span>
      </div>
    </div>
  </div>
&lt;/template>

&lt;script setup>
import { storeToRefs } from 'pinia'
import { useChatStore } from '../stores/chat'
import { formatDistanceToNow } from 'date-fns'

const chatStore = useChatStore()
const { chats } = storeToRefs(chatStore)

const formatTime = (timestamp) => {
  return formatDistanceToNow(new Date(timestamp), { addSuffix: true })
}

const selectChat = (chat) => {
  router.push(`/chat/${chat.id}`)
}
&lt;/script>

&lt;style scoped>
.chat-list {
  @apply divide-y divide-gray-200;
}

.chat-item {
  @apply flex items-center p-4 hover:bg-gray-50 cursor-pointer;
}

.chat-avatar {
  @apply w-12 h-12 rounded-full overflow-hidden mr-4;
}

.chat-info {
  @apply flex-1;
}

.chat-meta {
  @apply text-sm text-gray-500;
}

.unread-count {
  @apply bg-blue-500 text-white rounded-full px-2 py-1 text-xs;
}
&lt;/style>