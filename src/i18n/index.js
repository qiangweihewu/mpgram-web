import { createI18n } from 'vue-i18n'
import en from '../locale/en.json'
import ru from '../locale/ru.json'

const messages = { 
  en,
  ru
}

export default createI18n({
  legacy: false,
  globalInjection: true,
  locale: 'en',
  fallbackLocale: 'en',
  messages
})