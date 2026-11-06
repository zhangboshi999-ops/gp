<template>
  <div class="page">
    <van-nav-bar :title="$t('withdraw_crypto')" left-arrow @click-left="$router.back()" />

    <div style="padding: 12px">
      <div class="card">
        <div class="card-title">{{ $t('crypto') }}</div>
        <van-tabs v-model:active="chain" type="card">
          <van-tab title="Bitcoin" name="btc" />
          <van-tab title="Ethereum" name="eth" />
          <van-tab title="USDT" name="usdt" />
        </van-tabs>
      </div>

      <div class="card">
        <div class="card-title">{{ $t('address') }}</div>
        <van-radio-group v-model="walletId">
          <van-cell
            v-for="item in filteredWallets"
            :key="item.id"
            clickable
            :title="item.label || item.chain"
            :label="item.address"
            @click="walletId = item.id"
          >
            <template #right-icon><van-radio :name="item.id" /></template>
          </van-cell>
        </van-radio-group>
        <van-field v-model="address" label="" :placeholder="$t('address')" />
        <van-button block plain size="small" @click="$router.push('/wallets')">+ {{ $t('crypto_wallet') }}</van-button>
      </div>

      <div class="card">
        <div class="card-title">{{ $t('amount') }}（{{ $t('balance') }} {{ money(balance, 'USD') }}）</div>
        <van-field v-model="amount" type="number" placeholder="0" />
        <div class="quick-amount">
          <span v-for="item in [100, 500, 1000]" :key="item" @click="amount = String(item)">{{ money(item) }}</span>
          <span @click="amount = String(balance)">{{ $t('all') }}</span>
        </div>
        <van-field :label="$t('fee')" :model-value="money(fee)" readonly />
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

      <van-button block type="primary" :loading="loading" @click="submit">{{ $t('confirm') }}</van-button>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { showToast } from 'vant'
import { api } from '@/api'
import { useUserStore } from '@/store/user'
import { money } from '@/utils/format'
import { t } from '@/locales'

const router = useRouter()
const user = useUserStore()
const chain = ref('eth')
const wallets = ref([])
const walletId = ref('')
const address = ref('')
const amount = ref('')
const payPassword = ref('')
const loading = ref(false)

const filteredWallets = computed(() =>
  wallets.value.filter((item) => item.chain.toLowerCase() === chain.value)
)

const balance = computed(() => {
  const account = (user.info?.accounts || []).find((item) => item.currency === 'USD')
  return account ? account.balance : 0
})
const fee = computed(() => (chain.value === 'btc' ? 5 : chain.value === 'eth' ? 2 : 1))

watch(chain, () => {
  walletId.value = ''
  address.value = ''
})

watch(walletId, (value) => {
  const wallet = wallets.value.find((item) => item.id === value)
  if (wallet) {
    address.value = wallet.address
  }
})

async function load() {
  if (user.isLogin && !user.info) {
    await user.fetchInfo()
  }
  try {
    wallets.value = await api.wallets()
  } catch (e) {
    wallets.value = []
  }
}

async function submit() {
  if (!address.value) {
    showToast(t('address'))
    return
  }
  if (!Number(amount.value)) {
    showToast(t('amount'))
    return
  }
  if (payPassword.value.length !== 6) {
    showToast(t('pay_password'))
    return
  }
  loading.value = true
  try {
    await api.withdraw({
      currency: 'USD',
      amount: amount.value,
      method: chain.value,
      pay_password: payPassword.value,
      address: address.value
    })
    showToast(t('submit_success'))
    router.push('/records')
  } catch (e) {
    showToast(e.message || t('network_error'))
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.quick-amount {
  display: flex;
  gap: 8px;
  margin-top: 10px;
}

.quick-amount span {
  background: #f2f4f8;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 12px;
}
</style>
