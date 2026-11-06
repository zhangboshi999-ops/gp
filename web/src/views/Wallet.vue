<template>
  <div class="page">
    <van-nav-bar :title="currencyName" left-arrow @click-left="$router.back()" />

    <div style="padding: 12px">
      <div class="asset-card">
        <div class="label">{{ $t('balance') }}</div>
        <div class="value">{{ money(account.balance, currency) }}</div>
        <div class="sub">
          <span>{{ $t('frozen') }} {{ money(account.frozen, currency) }}</span>
          <span>{{ $t('total') }} {{ money(account.balance + account.frozen, currency) }}</span>
        </div>
      </div>

      <div class="card">
        <div class="quick-grid">
          <div class="quick-item" @click="$router.push('/deposit')">
            <div class="icon"><van-icon name="refund-o" /></div>
            <div class="name">{{ $t('recharge') }}</div>
          </div>
          <div class="quick-item" @click="$router.push('/withdraw-bank')">
            <div class="icon"><van-icon name="gold-coin-o" /></div>
            <div class="name">{{ $t('withdraw_bank') }}</div>
          </div>
          <div class="quick-item" @click="$router.push('/withdraw-crypto')">
            <div class="icon"><van-icon name="paid" /></div>
            <div class="name">{{ $t('withdraw_crypto') }}</div>
          </div>
          <div class="quick-item" @click="$router.push('/transfer')">
            <div class="icon"><van-icon name="exchange" /></div>
            <div class="name">{{ $t('transfer') }}</div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-title">
          <span>{{ $t('records') }}</span>
          <span class="muted" @click="$router.push('/records')">{{ $t('all') }}{{ $t('records') }} ›</span>
        </div>
        <div v-for="item in records" :key="item.id" class="row-between" style="padding: 8px 0">
          <div>
            <div>{{ typeText(item.type) }} · {{ item.method }}</div>
            <div class="muted">{{ dateTime(item.createtime) }}</div>
          </div>
          <div :class="item.type === 'withdraw' ? 'down' : 'up'">
            {{ item.type === 'withdraw' ? '-' : '+' }}{{ money(item.amount, item.currency) }}
          </div>
        </div>
        <EmptyState v-if="!records.length" icon="balance-list-o" />
      </div>
    </div>
  </div>
</template>

<script setup>
import EmptyState from '@/components/EmptyState.vue'
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '@/api'
import { money, dateTime } from '@/utils/format'
import { t } from '@/locales'

const route = useRoute()
const currency = computed(() => route.params.currency || 'JPY')
const accounts = ref([])
const records = ref([])

const account = computed(() => {
  return accounts.value.find((item) => item.currency === currency.value) || { balance: 0, frozen: 0 }
})

const currencyName = computed(() =>
  t(currency.value === 'JPY' ? 'jpy_account' : currency.value === 'USD' ? 'usd_account' : 'usdt_account')
)

function typeText(type) {
  return t(type === 'deposit' ? 'deposit' : type === 'withdraw' ? 'withdraw' : 'transfer')
}

async function load() {
  try {
    accounts.value = await api.accounts()
  } catch (e) {
    accounts.value = []
  }
  try {
    const data = await api.records({ type: '', limit: 10 })
    records.value = (data.list || []).filter((item) => item.currency === currency.value)
  } catch (e) {
    records.value = []
  }
}

onMounted(load)
</script>
