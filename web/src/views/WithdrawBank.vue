<template>
  <div class="page">
    <van-nav-bar :title="$t('withdraw_bank')" left-arrow @click-left="$router.back()" />

    <div style="padding: 12px">
      <div class="card">
        <div class="card-title">{{ $t('bank_card') }}</div>
        <van-radio-group v-model="bankId">
          <van-cell
            v-for="item in banks"
            :key="item.id"
            clickable
            :title="`${item.bank_name} ${item.branch_name}`"
            :label="`${item.account_no} · ${item.account_name}`"
            @click="bankId = item.id"
          >
            <template #right-icon><van-radio :name="item.id" /></template>
          </van-cell>
        </van-radio-group>
        <van-button block plain size="small" style="margin-top: 10px" @click="$router.push('/banks')">
          + {{ $t('bank_card') }}
        </van-button>
        <EmptyState v-if="!banks.length" icon="card" />
      </div>

      <div class="card">
        <div class="card-title">{{ $t('amount') }}（{{ $t('balance') }} {{ money(balance) }}）</div>
        <van-field v-model="amount" type="number" placeholder="0" />
        <div class="quick-amount">
          <span v-for="item in [10000, 50000, 100000]" :key="item" @click="amount = String(item)">
            {{ money(item) }}
          </span>
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
        <van-field v-model="remark" :label="$t('remark')" :placeholder="$t('optional')" />
      </div>

      <van-button block type="primary" :loading="loading" @click="submit">{{ $t('confirm') }}</van-button>
    </div>
  </div>
</template>

<script setup>
import EmptyState from '@/components/EmptyState.vue'
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { showToast } from 'vant'
import { api } from '@/api'
import { useUserStore } from '@/store/user'
import { money } from '@/utils/format'
import { t } from '@/locales'

const router = useRouter()
const user = useUserStore()
const banks = ref([])
const bankId = ref('')
const amount = ref('')
const payPassword = ref('')
const remark = ref('')
const loading = ref(false)

const balance = computed(() => {
  const account = (user.info?.accounts || []).find((item) => item.currency === 'JPY')
  return account ? account.balance : 0
})
const fee = computed(() => (Number(amount.value) > 0 ? 330 : 0))

async function load() {
  if (user.isLogin && !user.info) {
    await user.fetchInfo()
  }
  try {
    banks.value = await api.banks()
    if (banks.value.length) {
      bankId.value = banks.value[0].id
    }
  } catch (e) {
    banks.value = []
  }
}

async function submit() {
  if (!bankId.value) {
    showToast(t('bank_card'))
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
    const bank = banks.value.find((item) => item.id === bankId.value)
    await api.withdraw({
      currency: 'JPY',
      amount: amount.value,
      method: 'bank',
      pay_password: payPassword.value,
      bank_name: bank.bank_name,
      branch_name: bank.branch_name,
      account_no: bank.account_no,
      account_name: bank.account_name,
      remark: remark.value
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
