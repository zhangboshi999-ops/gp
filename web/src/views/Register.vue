<template>
  <div class="page">
    <van-nav-bar :title="$t('register')" left-arrow @click-left="$router.back()" />

    <van-form @submit="onSubmit" style="margin-top: 16px">
      <van-cell-group inset>
        <van-field
          v-model="form.username"
          left-icon="contact"
          :label="$t('username')"
          :placeholder="$t('username')"
          :rules="[{ required: true, message: $t('username') }]"
        />
        <van-field
          v-model="form.password"
          type="password"
          left-icon="lock"
          :label="$t('password')"
          :placeholder="$t('password')"
          :rules="[{ required: true, message: $t('password') }]"
        />
        <van-field
          v-model="form.confirm"
          type="password"
          left-icon="lock"
          :label="$t('confirm_password')"
          :placeholder="$t('confirm_password')"
          :rules="[{ validator: checkConfirm, message: t('password_mismatch') }]"
        />
        <van-field v-model="form.email" left-icon="envelop-o" :label="$t('email')" :placeholder="$t('email_placeholder')" />
        <van-field v-model="form.mobile" left-icon="phone-o" :label="$t('mobile')" :placeholder="$t('mobile_placeholder')" />
      </van-cell-group>

      <div style="margin: 24px 16px">
        <van-button
          round
          block
          type="primary"
          native-type="submit"
          :loading="loading"
          :disabled="!form.username || !form.password || form.password !== form.confirm"
        >
          {{ $t('register') }}
        </van-button>
      </div>
    </van-form>

    <p class="muted" style="text-align: center">
      {{ $t('login_tip') }}
      <span style="color: #2f6bff" @click="$router.push('/login')">{{ $t('login') }}</span>
    </p>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { showToast } from 'vant'
import { useUserStore } from '@/store/user'
import { t } from '@/locales'

const router = useRouter()
const user = useUserStore()
const loading = ref(false)
const form = reactive({ username: '', password: '', confirm: '', email: '', mobile: '' })

function checkConfirm() {
  return form.confirm === form.password
}

async function onSubmit() {
  if (!checkConfirm()) {
    showToast('两次输入不一致')
    return
  }
  loading.value = true
  try {
    await user.register(form)
    showToast(t('submit_success'))
    router.replace('/')
  } catch (error) {
    showToast(error.message || t('network_error'))
  } finally {
    loading.value = false
  }
}
</script>
