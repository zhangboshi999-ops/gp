<template>
  <div class="page">
    <van-nav-bar :title="$t('transfer')" left-arrow @click-left="$router.back()" />

    <div class="body">
      <div class="section-title">{{ $t('transfer') }}</div>

      <!-- 转出币种 -->
      <div class="cur-card pressable" @click="pickFrom = true">
        <span class="cur-flag">{{ flag(from) }}</span>
        <div class="cur-info">
          <div class="cur-name">{{ currencyName(from) }}</div>
          <div class="cur-balance">{{ $t('balance') }}: {{ money(fromBalance, from) }}</div>
        </div>
        <van-icon name="arrow" color="#c8ccd4" />
      </div>

      <!-- 互换方向 + 金额 -->
      <div class="swap-row">
        <div class="swap-btn pressable" @click="swap">
          <van-icon name="upgrade" />
          <van-icon name="down" />
        </div>
        <van-field
          v-model="amount"
          type="number"
          class="swap-input"
          :placeholder="$t('exchange_amount_ph')"
        />
      </div>

      <!-- 转入币种 -->
      <div class="cur-card pressable" @click="pickTo = true">
        <span class="cur-flag">{{ flag(to) }}</span>
        <div class="cur-info">
          <div class="cur-name">{{ currencyName(to) }}</div>
          <div class="cur-balance">{{ $t('balance') }}: {{ money(toBalance, to) }}</div>
        </div>
        <van-icon name="arrow" color="#c8ccd4" />
      </div>

      <!-- 汇率与预计到账 -->
      <div class="rate-row">
        <span class="muted">{{ $t('exchange_rate_now') }}:</span>
        <span>1 {{ from }} = {{ rateText }} {{ to }}</span>
      </div>
      <div class="rate-row">
        <span class="muted">{{ $t('estimated_arrival') }}:</span>
        <span>{{ money(toAmount, to) }} {{ to }}</span>
      </div>

      <!-- 笔记 -->
      <div class="note-block">
        <div class="note-title">{{ $t('exchange_note') }}</div>
        <div class="note-text">{{ $t('exchange_warning') }}</div>
      </div>

      <!-- 支付密码 -->
      <div class="card" style="margin-top: 12px">
        <van-field
          v-model="payPassword"
          type="password"
          maxlength="6"
          :label="$t('pay_password')"
          :placeholder="$t('pay_password')"
        />
      </div>

      <van-button block type="primary" class="confirm-btn" :loading="loading" @click="submit">
        {{ $t('confirm_transfer') }}
      </van-button>
    </div>

    <!-- 选择转出币种 -->
    <van-action-sheet
      v-model:show="pickFrom"
      :actions="fromActions"
      :cancel-text="$t('cancel')"
      @select="(a) => selectFrom(a)"
    />
    <!-- 选择转入币种 -->
    <van-action-sheet
      v-model:show="pickTo"
      :actions="toActions"
      :cancel-text="$t('cancel')"
      @select="(a) => selectTo(a)"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { showToast } from 'vant'
import { api } from '@/api'
import { useUserStore } from '@/store/user'
import { money } from '@/utils/format'
import { t } from '@/locales'

const router = useRouter()
const user = useUserStore()

// 划转只支持日元 <-> 美元（USDT 仅用于加密充值/提现，不参与划转）
// 若以后要放开 USDT，把 'USDT' 加回这个数组即可
const currencies = ['JPY', 'USD']
const from = ref('USD')
const to = ref('JPY')
const amount = ref('')
const payPassword = ref('')
const loading = ref(false)
const rates = ref({})
const pickFrom = ref(false)
const pickTo = ref(false)

// 币种展示：国旗 / 符号
const FLAGS = { JPY: '🇯🇵', USD: '🇺🇸', USDT: '₮' }
const NAMES = {
  JPY: () => t('currency_jpy'),
  USD: () => t('currency_usd'),
  USDT: () => t('currency_usdt')
}
const flag = (c) => FLAGS[c] || c
const currencyName = (c) => (NAMES[c] ? NAMES[c]() : c)

const fromBalance = computed(() => balanceOf(from.value))
const toBalance = computed(() => balanceOf(to.value))
const balanceOf = (c) => {
  const account = (user.info?.accounts || []).find((item) => item.currency === c)
  return account ? account.balance : 0
}

// 汇率：以 JPY 为基准换算
const rateText = computed(() => {
  const rf = rates.value[from.value]
  const rt = rates.value[to.value]
  if (!rf || !rt) return '-'
  const r = rf / rt
  return r >= 100 ? r.toFixed(2) : r.toFixed(4)
})

const toAmount = computed(() => {
  const n = Number(amount.value)
  if (!n || !rateText.value || rateText.value === '-') return 0
  return Math.round(n * (rates.value[from.value] / rates.value[to.value]) * 100) / 100
})

// 转出可选：除转入币种外；转入同理
const fromActions = computed(() =>
  currencies.filter((c) => c !== to.value).map((c) => ({ name: currencyName(c), value: c }))
)
const toActions = computed(() =>
  currencies.filter((c) => c !== from.value).map((c) => ({ name: currencyName(c), value: c }))
)
const selectFrom = (a) => {
  from.value = a.value
  pickFrom.value = false
}
const selectTo = (a) => {
  to.value = a.value
  pickTo.value = false
}

// 上下箭头：交换方向，金额清空
function swap() {
  const f = from.value
  from.value = to.value
  to.value = f
  amount.value = ''
}

async function submit() {
  if (!Number(amount.value)) {
    showToast(t('exchange_amount_ph'))
    return
  }
  if (Number(amount.value) > fromBalance.value) {
    showToast(t('insufficient_balance'))
    return
  }
  if (payPassword.value.length !== 6) {
    showToast(t('pay_password'))
    return
  }
  loading.value = true
  try {
    await api.exchange({
      from_currency: from.value,
      to_currency: to.value,
      amount: amount.value,
      pay_password: payPassword.value
    })
    showToast(t('exchange_success'))
    amount.value = ''
    payPassword.value = ''
    await user.fetchInfo()
    router.push('/records')
  } catch (e) {
    showToast(e.message || t('network_error'))
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    rates.value = await api.exchangeRate()
  } catch (e) {
    rates.value = { JPY: 1, USD: 157.44, USDT: 157.44 }
  }
})
</script>

<style scoped>
.body {
  padding: 12px;
}

.section-title {
  font-size: 16px;
  font-weight: 700;
  margin: 4px 0 12px;
}

/* 币种卡片：国旗 + 名称 + 余额 */
.cur-card {
  display: flex;
  align-items: center;
  background: #f7f8fb;
  border-radius: 12px;
  padding: 14px 14px;
  margin-bottom: 12px;
  min-width: 0;
}

.cur-flag {
  font-size: 30px;
  width: 44px;
  text-align: center;
  flex: none;
}

.cur-info {
  flex: 1;
  min-width: 0;
  margin-left: 8px;
}

.cur-name {
  font-size: 16px;
  font-weight: 700;
}

.cur-balance {
  font-size: 12px;
  color: #9aa0aa;
  margin-top: 2px;
}

/* 中间行：互换按钮 + 金额输入 */
.swap-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 4px 0 12px;
  min-width: 0;
}

.swap-btn {
  flex: none;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  color: var(--primary);
  padding: 8px;
}

.swap-btn .van-icon {
  font-size: 18px;
  font-weight: 700;
}

.swap-input {
  flex: 1;
  min-width: 0;
  background: #f7f8fb;
  border-radius: 10px;
  padding: 8px 12px;
}

/* 汇率与到账行 */
.rate-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  margin-top: 8px;
}

/* 笔记 */
.note-block {
  margin-top: 12px;
}

.note-title {
  font-size: 14px;
  font-weight: 700;
  color: var(--danger, #f5455c);
  margin-bottom: 4px;
}

.note-text {
  font-size: 13px;
  color: var(--danger, #f5455c);
  line-height: 1.5;
  overflow-wrap: anywhere;
}

.confirm-btn {
  margin-top: 20px;
}
</style>
