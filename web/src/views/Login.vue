<template>
  <div class="page">
    <div class="login-header">
      <div class="logo">S</div>
      <h2>{{ $t('app_name') }}</h2>
      <p class="muted">{{ $t('login_tip') }}</p>
    </div>

    <van-form @submit="onSubmit">
      <van-cell-group inset>
        <van-field
          v-model="form.account"
          name="account"
          left-icon="contact"
          :label="$t('username')"
          :placeholder="$t('username')"
          :rules="[{ required: true, message: $t('username') }]"
        />
        <van-field
          v-model="form.password"
          type="password"
          name="password"
          left-icon="lock"
          :label="$t('password')"
          :placeholder="$t('password')"
          :rules="[{ required: true, message: $t('password') }]"
        />
      </van-cell-group>

      <div class="login-links">
        <span @click="$router.push('/forgot')">{{ $t('forgot_password') }}</span>
        <span @click="$router.push('/register')">{{ $t('register') }}</span>
      </div>

      <div style="margin: 24px 16px">
        <van-button
          round
          block
          type="primary"
          native-type="submit"
          :loading="loading"
          :disabled="!form.account || !form.password"
        >
          {{ $t('login') }}
        </van-button>
      </div>
    </van-form>

    <div class="demo-chip pressable" @click="fillDemo">
      <van-icon name="user-circle-o" />
      <span>{{ $t('demo_account') }}</span>
      <van-icon name="arrow" size="11" />
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { showToast } from 'vant'
import { useUserStore } from '@/store/user'
import { t } from '@/locales'

const route = useRoute()
const router = useRouter()
const user = useUserStore()
const loading = ref(false)
const form = reactive({ account: '', password: '' })

function fillDemo() {
  form.account = '13800000000'
  form.password = '123456'
}

async function onSubmit() {
  loading.value = true
  try {
    await user.login(form)
    showToast(t('operation_success'))
    router.replace(route.query.redirect || '/')
  } catch (error) {
    showToast(error.message || t('network_error'))
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-header {
  text-align: center;
  padding: 60px 0 30px;
}

.logo {
  width: 64px;
  height: 64px;
  border-radius: 20px;
  background: linear-gradient(135deg, #2f6bff, #1b46c8);
  color: #fff;
  font-size: 32px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
}

.login-header h2 {
  margin: 0 0 6px;
  font-size: 22px;
}

.demo-chip {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  width: fit-content;
  margin: 8px auto 0;
  padding: 6px 12px;
  font-size: 12px;
  color: var(--primary);
  background: var(--primary-soft);
  border-radius: 999px;
}

.login-links {
  display: flex;
  justify-content: space-between;
  padding: 12px 24px 0;
  font-size: 13px;
  color: #2f6bff;
}
</style>
