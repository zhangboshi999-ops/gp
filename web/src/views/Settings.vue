<template>
  <div class="page">
    <van-nav-bar :title="$t('settings')" left-arrow @click-left="$router.back()" />

    <div style="padding: 12px">
      <div class="card" style="padding: 0">
        <van-cell is-link :title="$t('language')" icon="font-o" :value="localeLabel" @click="showLang = true" />
        <van-cell is-link :title="$t('verification')" icon="idcard" @click="$router.push('/verify')" />
        <van-cell is-link :title="$t('pay_password')" icon="lock" @click="$router.push('/paypwd')" />
        <van-cell is-link :title="$t('bank_card')" icon="card" @click="$router.push('/banks')" />
        <van-cell is-link :title="$t('crypto_wallet')" icon="paid" @click="$router.push('/wallets')" />
      </div>

      <div class="card" style="padding: 0">
        <van-cell :title="$t('app_name')" value="v1.0.0" />
        <van-cell :title="$t('support')" is-link icon="service-o" @click="$router.push('/support')" />
      </div>

      <van-button block plain type="danger" @click="logout">{{ $t('logout') }}</van-button>
    </div>

    <van-action-sheet v-model:show="showLang" :actions="langActions" @select="selectLang" :cancel-text="$t('cancel')" />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/store/user'
import { availableLanguages, localeState, setLocale, t } from '@/locales'

const router = useRouter()
const user = useUserStore()
const showLang = ref(false)

// 语言来自 locales/messages/*.js，并按后端启用的列表过滤
const langActions = computed(() =>
  availableLanguages.value.map((item) => ({ name: item.name, value: item.code }))
)

const localeLabel = computed(
  () => langActions.value.find((item) => item.value === localeState.locale)?.name || localeState.locale
)

function selectLang(action) {
  setLocale(action.value)
  showLang.value = false
}

function logout() {
  user.logout()
  router.push('/login')
}
</script>
