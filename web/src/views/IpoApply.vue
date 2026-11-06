<template>
  <div class="page">
    <van-nav-bar :title="$t('ipo_apply')" left-arrow @click-left="$router.back()" />

    <div style="padding: 12px">
      <div class="card">
        <div class="row-between">
          <div>
            <div style="font-size: 17px; font-weight: 600">{{ ipo.name }}</div>
            <div class="muted">{{ ipo.code }} · {{ ipo.market }}</div>
          </div>
          <van-tag type="danger">{{ statusText(ipo.status) }}</van-tag>
        </div>
        <div class="ipo-grid">
          <div><span class="muted">{{ $t('issue_price') }}</span><b>{{ price(ipo.issue_price, 'JPY') }}</b></div>
          <div><span class="muted">{{ $t('total') }}</span><b>{{ (ipo.total_shares || 0).toLocaleString() }}</b></div>
          <div><span class="muted">{{ $t('subscribe_quantity') }}</span><b>{{ ipo.min_quantity }} ~ {{ ipo.max_quantity }}</b></div>
        </div>
        <div class="muted" style="margin-top: 10px">{{ ipo.intro }}</div>
      </div>

      <div class="card">
        <div class="card-title">{{ $t('subscribe_quantity') }}</div>
        <van-field :label="$t('quantity')">
          <template #input>
            <van-stepper
              v-model="quantity"
              :step="ipo.min_quantity || 100"
              :min="0"
              :max="ipo.max_quantity || 10000"
              integer
              button-size="26px"
            />
          </template>
        </van-field>
        <van-cell :title="$t('total')" :value="money(total, 'JPY')" />
        <van-cell :title="$t('balance')" :value="money(balance, 'JPY')" />
      </div>

      <div class="card">
        <van-field
          v-model="payPassword"
          type="password"
          maxlength="6"
          :label="$t('pay_password')"
          :placeholder="$t('pay_password')"
        />
      </div>

      <van-button block type="primary" :loading="loading" @click="submit">{{ $t('confirm_shares') }}</van-button>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { showToast } from 'vant'
import { api } from '@/api'
import { useUserStore } from '@/store/user'
import { money, price } from '@/utils/format'
import { t } from '@/locales'

const route = useRoute()
const router = useRouter()
const user = useUserStore()
const ipoId = route.params.id
const ipo = ref({})
const quantity = ref(0)
const payPassword = ref('')
const loading = ref(false)

const total = computed(() => Number(ipo.value.issue_price || 0) * quantity.value)
const balance = computed(() => {
  const account = (user.info?.accounts || []).find((item) => item.currency === 'JPY')
  return account ? account.balance : 0
})

function statusText(value) {
  return t(value === 'listed' || value === 'closed' ? 'success' : 'pending')
}

async function load() {
  if (user.isLogin && !user.info) {
    await user.fetchInfo()
  }
  try {
    ipo.value = await api.ipoDetail({ id: ipoId })
    quantity.value = ipo.value.min_quantity || 100
  } catch (e) {
    showToast(e.message || t('network_error'))
  }
}

async function submit() {
  if (!quantity.value) {
    showToast(t('subscribe_quantity'))
    return
  }
  if (payPassword.value.length !== 6) {
    showToast(t('pay_password'))
    return
  }
  loading.value = true
  try {
    await api.ipoApply({ ipo_id: ipoId, quantity: quantity.value, pay_password: payPassword.value })
    showToast(t('submit_success'))
    router.push('/ipo')
  } catch (e) {
    showToast(e.message || t('network_error'))
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.ipo-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px 14px;
  margin-top: 12px;
  font-size: 13px;
}

.ipo-grid > div {
  display: flex;
  justify-content: space-between;
}
</style>
