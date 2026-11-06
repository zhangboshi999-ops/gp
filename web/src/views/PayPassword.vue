<template>
  <div class="page">
    <van-nav-bar :title="$t('set_pay_password')" left-arrow @click-left="$router.back()" />

    <div style="padding: 12px">
      <div class="card">
        <p class="muted" style="margin-top: 0">{{ $t('pay_password') }}：6桁の数字</p>
        <van-password-input :value="password" :focused="showKeyboard" @focus="showKeyboard = true" />

        <div v-if="step === 2" style="margin-top: 16px">
          <p class="muted">{{ $t('confirm_password') }}</p>
          <van-password-input :value="confirm" :focused="showKeyboard" @focus="showKeyboard = true" />
        </div>

        <van-button block type="primary" style="margin-top: 20px" :loading="loading" @click="submit">
          {{ step === 1 ? $t('next') : $t('submit') }}
        </van-button>
      </div>
    </div>

    <van-number-keyboard
      v-model="current"
      :show="showKeyboard"
      @blur="showKeyboard = false"
    />
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { showToast } from 'vant'
import { api } from '@/api'
import { useUserStore } from '@/store/user'
import { t } from '@/locales'

const router = useRouter()
const user = useUserStore()
const step = ref(1)
const password = ref('')
const confirm = ref('')
const showKeyboard = ref(true)
const loading = ref(false)

const current = computed({
  get: () => (step.value === 1 ? password.value : confirm.value),
  set: (value) => {
    if (step.value === 1) {
      password.value = value
    } else {
      confirm.value = value
    }
  }
})

watch(password, (value) => {
  if (value.length === 6) {
    step.value = 2
  }
})

async function submit() {
  if (step.value === 1) {
    if (password.value.length !== 6) {
      showToast(t('pay_password'))
    }
    return
  }
  if (confirm.value !== password.value) {
    showToast(t('confirm_password'))
    confirm.value = ''
    return
  }
  loading.value = true
  try {
    await api.paypwd({ pay_password: password.value })
    showToast(t('operation_success'))
    await user.fetchInfo()
    router.back()
  } catch (e) {
    showToast(e.message || t('network_error'))
  } finally {
    loading.value = false
  }
}
</script>
