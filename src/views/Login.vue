<template>
  <div class="login-container">
    <div class="login-header">
      <h1>MPGram Web</h1>
      <p class="login-subtitle">{{ $t('welcome_text') }}</p>
    </div>
    
    <div class="login-box">
      <div v-if="error" class="login-error">
        {{ error }}
      </div>
      <div class="login-methods">
        <div class="phone-login">
          <h2>{{ $t('phone_number') }}</h2>
          <form @submit.prevent="handlePhoneLogin" class="login-form">
            <input 
              v-model="phone"
              type="text" 
              class="login-input"
              :placeholder="'+1234567890'"
            >
            <button type="submit" class="login-button">
              {{ $t('continue') }}
            </button>
          </form>
        </div>
        
        <div class="qr-login">
          <h2>{{ $t('qr_login') }}</h2>
          <div class="qr-section" v-if="showQR">
            <img :src="qrCode" alt="QR Code" class="qr-code">
            <p class="qr-text">{{ $t('scan_qr') }}</p>
            <div class="qr-divider">{{ $t('or') }}</div>
          </div>
          <button @click="showQR = true" v-else class="qr-button">
            {{ $t('scan_qr') }}
          </button>
        </div>
      </div>
    </div>

    <div class="login-footer">
      <a href="/about">{{ $t('about') }}</a>
      <a @click="setLocale('en')">English</a>
      <a @click="setLocale('ru')">Русский</a>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

const { t, locale } = useI18n()
const router = useRouter()
const phone = ref('')
const showQR = ref(false)
const qrCode = ref('')
const error = ref('')

const setLocale = (lang) => {
  locale.value = lang
}

const handlePhoneLogin = async () => {
  try {
    error.value = '' // Clear any previous errors
    
    const res = await fetch('/api/login.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        phone: phone.value
      })
    })

    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`)
    }

    const contentType = res.headers.get('content-type')
    if (!contentType || !contentType.includes('application/json')) {
      throw new Error('Server did not return JSON')
    }

    const data = await res.json()
    if (data.success) {
      router.push('/chat')
    } else if (data.error) {
      error.value = data.error
    }
  } catch (err) {
    console.error('Login error:', err)
    error.value = t('login_error') || 'An error occurred during login'
  }
}
</script>