import { createI18n } from 'vue-i18n'
import en from '../locale/en.json'
import ru from '../locale/ru.json'
import zh from '../locale/zh.json'

const messages = { 
  en,
  ru,
  zh
}

export default createI18n({
  legacy: false,
  globalInjection: true,
  locale: 'zh',
  fallbackLocale: 'zh',
  messages
})