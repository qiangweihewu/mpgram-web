import { createI18n } from 'vue-i18n'

const messages = {
  en: {
    welcome_text: 'Your secure gateway to Telegram',
    phone_number: 'Phone number',
    continue: 'Continue',
    qr_login: 'Login with QR code',
    scan_qr: 'Scan QR code with Telegram mobile app',
    or: 'or',
    about: 'About MPGram',
    error: {
      invalid_phone: 'Invalid phone number',
      code_expired: 'Code expired',
      code_invalid: 'Invalid code'
    }
  },
  ru: {
    welcome_text: 'Ваш безопасный доступ к Telegram',
    phone_number: 'Номер телефона',
    continue: 'Продолжить',
    qr_login: 'Войти по QR-коду',
    scan_qr: 'Отсканируйте QR-код в приложении Telegram',
    or: 'или',
    about: 'О MPGram',
    error: {
      invalid_phone: 'Неверный номер телефона',
      code_expired: 'Код истек',
      code_invalid: 'Неверный код'
    }
  }
}

export default createI18n({
  legacy: false,
  globalInjection: true,
  locale: 'en',
  fallbackLocale: 'en',
  messages
})