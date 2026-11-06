<template>
  <div class="page">
    <van-nav-bar :title="$t('forgot_password')" left-arrow @click-left="$router.back()" />

    <van-form @submit="onSubmit" style="margin-top: 16px">
      <van-cell-group inset>
        <van-field v-model="form.email" :label="$t('email')" :placeholder="$t('email_placeholder')" />
        <van-field
          v-model="form.captcha"
          :label="$t('captcha')"
          :placeholder="$t('captcha')"
        >
          <template #button>
            <van-button size="small" type="primary" @click="sendCode">{{ $t('send_captcha') }}</van-button>
          </template>
        </van-field>
        <van-field
          v-model="form.newpassword"
          type="password"
          :label="$t('password')"
          :placeholder="$t('password')"
        />
      </van-cell-group>

      <div style="margin: 24px 16px">
        <van-button round block type="primary" native-type="submit">{{ $t('submit') }}</van-button>
      </div>
    </van-form>

    <p class="muted" style="text-align: center">演示環境驗證碼：1234</p>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { showToast } from 'vant'
import { api } from '@/api'
import { t } from '@/locales'

const router = useRouter()
const form = reactive({ email: '', captcha: '', newpassword: '', type: 'email' })

function sendCode() {
  if (!form.email) {
    showToast(t('email_placeholder'))
    return
  }
  form.captcha = '1234'
  showToast(t('submit_success'))
}

async function onSubmit() {
  try {
    await api.resetpwd({ email: form.email, captcha: form.captcha, newpassword: form.newpassword, type: 'email' })
    showToast(t('operation_success'))
    router.push('/login')
  } catch (error) {
    showToast(error.message || t('network_error'))
  }
}
</script>
