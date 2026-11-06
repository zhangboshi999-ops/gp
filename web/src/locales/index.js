import { computed, reactive, ref, watch } from 'vue'

// v2：默认语言改为中文，旧键（日语默认）失效，避免历史缓存继续生效
const STORE_KEY = 'stock_locale_v2'

// 默认语言（后端未返回可用语言列表时兜底，后端配置见 fastadmin/application/extra/lang.php）
const FALLBACK_LOCALE = 'zh'

/**
 * 自动扫描 locales/messages/*.js
 * 新增一门语言：只要新建 messages/xx.js（导出 name / pack / order / messages），
 * 无需改动本文件，语言会自动出现在切换列表；后端 extra/lang.php 也要同步加一项。
 */
const modules = import.meta.glob('./messages/*.js', { eager: true })

const packs = Object.keys(modules)
  .map((path) => {
    const matched = path.match(/\/([^/]+)\.js$/)
    const mod = modules[path].default || {}
    return {
      code: matched ? matched[1] : '',
      name: mod.name || (matched ? matched[1] : ''),
      pack: mod.pack || (matched ? matched[1] : ''),
      order: typeof mod.order === 'number' ? mod.order : 99,
      messages: mod.messages || {}
    }
  })
  .filter((item) => item.code && Object.keys(item.messages).length)
  .sort((a, b) => a.order - b.order)

/** 全部语言（按 order 排序） */
export const languages = packs.map((item) => ({
  code: item.code,
  name: item.name,
  pack: item.pack
}))

const messages = packs.reduce((acc, item) => {
  acc[item.code] = item.messages
  return acc
}, {})

/** 后端启用中的语言（由 /api/index/langs 写入，为空则显示全部） */
const enabledCodes = ref([])

export function setEnabledLanguages(codes) {
  enabledCodes.value = Array.isArray(codes) && codes.length ? codes : []
}

/** 供语言切换 UI 使用 */
export const availableLanguages = computed(() => {
  if (!enabledCodes.value.length) {
    return languages
  }
  return enabledCodes.value
    .map((code) => languages.find((item) => item.code === code))
    .filter(Boolean)
})

/**
 * 语言标识 → 后端请求头 X-Lang 的值
 * @param {string} locale
 * @returns {string}
 */
export function langPack(locale) {
  const item = languages.find((entry) => entry.code === locale)
  return (item && item.pack) || locale
}

export const localeState = reactive({
  locale: localStorage.getItem(STORE_KEY) || FALLBACK_LOCALE
})

watch(
  () => localeState.locale,
  (value) => {
    localStorage.setItem(STORE_KEY, value)
    document.documentElement.lang = value
  },
  { immediate: true }
)

export function setLocale(locale) {
  localeState.locale = locale
}

export function t(key, params = {}) {
  const fallback = messages[FALLBACK_LOCALE] || {}
  const dict = messages[localeState.locale] || fallback
  let text = dict[key] || fallback[key] || key
  Object.keys(params).forEach((name) => {
    text = text.replace(new RegExp(`\\{${name}\\}`, 'g'), params[name])
  })
  return text
}
