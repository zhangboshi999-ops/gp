import { createApp } from 'vue'
import { createPinia } from 'pinia'
import Vant from 'vant'
import 'vant/lib/index.css'
import App from './App.vue'
import router from './router'
import { t } from './locales'
import './styles/main.css'

const app = createApp(App)

app.config.globalProperties.$t = t
app.use(createPinia())
app.use(router)
app.use(Vant)
app.mount('#app')
