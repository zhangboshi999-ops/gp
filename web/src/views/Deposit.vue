<template>
  <div class="page">
    <van-nav-bar :title="$t('deposit')" left-arrow @click-left="$router.back()" />

    <div class="page-body">
      <!-- 可用余额 -->
      <div class="balance-card">
        <div class="balance-label">
          <span>{{ $t('available_balance') }}（{{ currency }}）</span>
        </div>
        <div class="balance-value num">{{ money(balance, '') }}</div>
      </div>

      <!-- 充值方式 -->
      <div class="card">
        <div class="card-title">{{ $t('deposit_method') }}</div>
        <div class="method-grid">
          <div
            v-for="item in methods"
            :key="item.key"
            class="method-card"
            :class="{ active: method === item.key }"
            @click="switchMethod(item.key)"
          >
            <div class="method-icon" :class="item.iconClass">
              <van-icon :name="item.icon" />
            </div>
            <div class="method-name">{{ item.name }}</div>
          </div>
        </div>
      </div>

      <!-- 存款地址（加密） -->
      <div v-if="isCrypto" class="card address-card">
        <div class="address-title">
          {{ $t('deposit_address') }} {{ currentCrypto.network }} {{ currentCrypto.name }}
        </div>
        <div class="qr-wrap">
          <img v-if="qrUrl" :src="qrUrl" class="qr-img" alt="QR" />
          <div v-else class="qr-loading sk"></div>
        </div>
        <div class="addr-bar" @click="copy(currentCrypto.address)">
          <span class="num">{{ currentCrypto.address }}</span>
          <van-icon name="description" />
        </div>
        <div class="muted" style="margin-top: 8px; font-size: 11px">
          {{ $t('network') }}：{{ currentCrypto.network }} · {{ $t('confirmations') }}：{{ currentCrypto.confirm }}
        </div>
      </div>

      <!-- 金额 -->
      <div class="card">
        <div class="card-title">{{ $t('amount') }}（{{ isCrypto ? method.toUpperCase() : currency }}）</div>
        <div class="amount-input">
          <span class="symbol">{{ isCrypto ? '' : currency === 'JPY' ? '¥' : '$' }}</span>
          <input
            v-model="amount"
            class="num"
            type="number"
            inputmode="decimal"
            :placeholder="minAmount ? String(minAmount) : '0'"
          />
        </div>
        <div class="quick-amount">
          <span
            v-for="item in quickAmounts"
            :key="item"
            class="quick-chip"
            :class="{ active: amount === String(item) }"
            @click="amount = String(item)"
          >
            {{ money(item, '') }}
          </span>
        </div>
        <div v-if="minAmount || maxAmount" class="limit-tip">
          {{ $t('amount_limit') }} {{ money(minAmount, '') }} ~ {{ money(maxAmount, '') }}
        </div>
      </div>

      <!-- 汇率 / 预期付款（加密） -->
      <div v-if="isCrypto" class="card">
        <div class="rate-row">
          <span class="muted">{{ $t('current_rate') }}</span>
          <span class="num">1 {{ method.toUpperCase() }} ≈ {{ money(currentCrypto.rate, '') }} 日元</span>
        </div>
        <div class="rate-row">
          <span class="muted">{{ $t('expected_payment') }}</span>
          <span class="num pay-value">{{ expectedPayment }} 日元</span>
        </div>
      </div>

      <!-- 付款收据 -->
      <div class="card">
        <div class="card-title">{{ $t('payment_receipt') }}</div>
        <div class="receipt-tip">{{ $t('receipt_tip') }}</div>
        <van-uploader
          v-model="receiptList"
          :max-count="1"
          :after-read="afterRead"
          :max-size="8 * 1024 * 1024"
          @oversize="onOversize"
          class="receipt-uploader"
        >
          <div class="upload-box pressable">
            <van-icon name="upgrade" />
            <span>{{ $t('upload') }}</span>
          </div>
        </van-uploader>
      </div>

      <!-- 银行补充信息 -->
      <div v-if="!isCrypto" class="card">
        <van-field
          v-model="form.account_name"
          class="form-field"
          :label="$t('remitter')"
          :placeholder="$t('remitter')"
        />
        <van-field v-model="form.remark" class="form-field" :label="$t('remark')" :placeholder="$t('optional')" />
        <div v-if="payee.bank.tips" class="tip-bar">
          <van-icon name="info-o" />
          <span>{{ payee.bank.tips }}</span>
        </div>
      </div>

      <!-- 银行收款账户 -->
      <div v-if="!isCrypto" class="card">
        <div class="card-title">{{ $t('payee_info') }}</div>
        <div class="payee">
          <div v-for="row in bankRows" :key="row.label" class="payee-row">
            <span class="muted">{{ row.label }}</span>
            <div class="payee-value">
              <b class="num">{{ row.value }}</b>
              <van-icon name="description" class="copy-icon" @click="copy(row.value)" />
            </div>
          </div>
        </div>
      </div>

      <!-- 加密补充：TXID -->
      <div v-if="isCrypto" class="card">
        <van-field v-model="form.txid" class="form-field" label="TXID" :placeholder="$t('optional')" />
        <div class="tip-bar">
          <van-icon name="info-o" />
          <span>{{ $t('deposit_tip') }}</span>
        </div>
      </div>
    </div>

    <div class="submit-bar">
      <van-button block round type="primary" :loading="loading" :disabled="!canSubmit" @click="submit">
        {{ $t('pay') }}
      </van-button>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { showToast } from 'vant'
import QRCode from 'qrcode'
import { api, request } from '@/api'
import { money } from '@/utils/format'
import { t } from '@/locales'

const router = useRouter()
const method = ref('usdt')
const amount = ref('')
const loading = ref(false)
const accounts = ref([])
const receiptList = ref([])
const receiptUrl = ref('')
const qrUrl = ref('')
const payee = ref({ bank: {}, crypto: [], min_amount: 0, max_amount: 0 })
const form = reactive({ account_name: '', remark: '', txid: '' })

const methods = ref([
  { key: 'eth', name: 'ETH', icon: 'paid', iconClass: 'i-purple' },
  { key: 'btc', name: 'BTC', icon: 'paid', iconClass: 'i-orange' },
  { key: 'usdt', name: 'USDT', icon: 'paid', iconClass: 'i-green' },
  { key: 'bank', name: 'JPY', icon: 'card', iconClass: 'i-blue' }
])

const isCrypto = computed(() => method.value !== 'bank')
const currency = computed(() => (isCrypto.value ? method.value.toUpperCase() : 'JPY'))
const balance = computed(() => {
  const item = accounts.value.find((row) => row.currency === currency.value)
  return item ? item.balance : 0
})

const minAmount = computed(() => Number(payee.value.min_amount) || 0)
const maxAmount = computed(() => Number(payee.value.max_amount) || 0)

const quickAmounts = computed(() => {
  if (!isCrypto.value) return [10000, 50000, 100000, 500000]
  return method.value === 'btc' ? [0.01, 0.05, 0.1, 0.5] : [100, 500, 1000, 5000]
})

const currentCrypto = computed(() => {
  const list = payee.value.crypto || []
  return list.find((item) => item.method === method.value) || { network: '', address: '', confirm: 0, rate: 0 }
})

const expectedPayment = computed(() => {
  const value = Number(amount.value)
  if (!value || !currentCrypto.value.rate) return '0.00'
  return money(value * currentCrypto.value.rate, '')
})

const bankRows = computed(() => {
  const bank = payee.value.bank || {}
  return [
    { label: t('bank_name'), value: bank.bank_name || '' },
    { label: t('branch_name'), value: bank.branch_name || '' },
    { label: t('account_no'), value: bank.account_no || '' },
    { label: t('account_name'), value: bank.account_name || '' }
  ].filter((row) => row.value)
})

const canSubmit = computed(() => {
  const value = Number(amount.value)
  if (!value) return false
  if (minAmount.value && value < minAmount.value) return false
  if (maxAmount.value && value > maxAmount.value) return false
  return true
})

async function renderQr() {
  if (!isCrypto.value || !currentCrypto.value.address) {
    qrUrl.value = ''
    return
  }
  try {
    qrUrl.value = await QRCode.toDataURL(currentCrypto.value.address, { margin: 1, width: 220 })
  } catch (e) {
    qrUrl.value = ''
  }
}

function switchMethod(key) {
  method.value = key
  amount.value = ''
}

async function load() {
  try {
    accounts.value = await api.accounts()
  } catch (e) {
    accounts.value = []
  }
  try {
    payee.value = await api.payee()
  } catch (e) {
    payee.value = { bank: {}, crypto: [], min_amount: 0, max_amount: 0 }
  }
  renderQr()
}

watch([method, () => payee.value.crypto], renderQr)

async function copy(text) {
  if (!text) return
  try {
    await navigator.clipboard.writeText(text)
  } catch (e) {
    const input = document.createElement('textarea')
    input.value = text
    document.body.appendChild(input)
    input.select()
    document.execCommand('copy')
    input.remove()
  }
  showToast(t('wallet_copied'))
}

/**
 * 收据上传：走 /api/common/upload，成功后保存 URL 随充值提交
 */
async function afterRead(file) {
  file.status = 'uploading'
  file.message = t('uploading')
  try {
    const data = await request('common/upload', (() => {
      const fd = new FormData()
      fd.append('file', file.file)
      return fd
    })(), 'post')
    file.status = 'done'
    file.message = ''
    receiptUrl.value = data.url || ''
  } catch (e) {
    receiptList.value = []
    showToast(e.message || t('network_error'))
  }
}

function onOversize() {
  showToast(t('upload_too_large'))
}

async function submit() {
  const value = Number(amount.value)
  if (!value) {
    showToast(t('amount'))
    return
  }
  if ((minAmount.value && value < minAmount.value) || (maxAmount.value && value > maxAmount.value)) {
    showToast(t('amount_limit'))
    return
  }
  if (isCrypto.value && !receiptUrl.value) {
    showToast(t('receipt_required'))
    return
  }
  loading.value = true
  const extra = isCrypto.value
    ? { address: currentCrypto.value.address, txid: form.txid, receipt: receiptUrl.value }
    : { account_name: form.account_name, remark: form.remark, receipt: receiptUrl.value }
  try {
    await api.deposit({ currency: currency.value, amount: amount.value, method: method.value, ...extra })
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
.balance-card {
  background: linear-gradient(135deg, #3b76ff 0%, #1b46c8 100%);
  color: #fff;
  border-radius: var(--radius-l);
  padding: 14px 18px;
  margin-bottom: 12px;
  box-shadow: 0 10px 24px rgba(27, 70, 200, 0.22);
}

.balance-label {
  font-size: 12px;
  opacity: 0.85;
}

.balance-value {
  margin-top: 6px;
  font-size: 24px;
  font-weight: 700;
}

/* 方式选择：四宫格 */
.method-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 8px;
}

.method-card {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  padding: 12px 4px 10px;
  border: 1px solid var(--border);
  border-radius: var(--radius-s);
  transition: all 0.15s ease;
}

.method-card.active {
  border-color: var(--primary);
  background: var(--primary-soft);
}

.method-icon {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  transition: all 0.15s ease;
}

/* 未选中：浅色底 + 品牌色图标，保证在白卡片上清晰可见 */
.i-blue {
  background: #eef3ff;
  color: #2f6bff;
}

.i-orange {
  background: #fff3e3;
  color: #f7931a;
}

.i-purple {
  background: #f3ecff;
  color: #627eea;
}

.i-green {
  background: #e8f8f1;
  color: #14b97a;
}

/* 选中：实心渐变底 + 白色图标 */
.method-card.active .method-icon.i-blue {
  background: linear-gradient(135deg, #5b8bff, #2f6bff);
  color: #fff;
}

.method-card.active .method-icon.i-orange {
  background: linear-gradient(135deg, #f9a02c, #f7931a);
  color: #fff;
}

.method-card.active .method-icon.i-purple {
  background: linear-gradient(135deg, #8a9bff, #627eea);
  color: #fff;
}

.method-card.active .method-icon.i-green {
  background: linear-gradient(135deg, #38c793, #26a17b);
  color: #fff;
}

.method-name {
  font-size: 12px;
  font-weight: 600;
}

/* 存款地址与二维码 */
.address-card {
  text-align: center;
}

.address-title {
  font-size: 14px;
  font-weight: 600;
}

.qr-wrap {
  display: flex;
  justify-content: center;
  margin: 12px 0;
}

.qr-img {
  width: 200px;
  height: 200px;
  border-radius: var(--radius-s);
  background: #fff;
  padding: 6px;
  border: 1px solid var(--border);
}

.qr-loading {
  width: 200px;
  height: 200px;
  border-radius: var(--radius-s);
}

.addr-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  background: var(--primary-soft);
  color: var(--primary);
  border-radius: var(--radius-s);
  padding: 9px 12px;
  font-size: 12px;
}

.addr-bar .num {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  text-align: left;
}

/* 金额 */
.amount-input {
  display: flex;
  align-items: baseline;
  gap: 8px;
  border-bottom: 1px solid var(--border);
  padding-bottom: 8px;
}

.amount-input .symbol {
  font-size: 22px;
  font-weight: 600;
  color: var(--text-2);
}

.amount-input input {
  flex: 1;
  border: none;
  outline: none;
  font-size: 26px;
  font-weight: 700;
  color: var(--text);
  background: transparent;
  padding: 0;
}

.amount-input input::placeholder {
  color: #c9ccd4;
  font-weight: 400;
}

.quick-amount {
  display: flex;
  gap: 8px;
  margin-top: 12px;
  flex-wrap: wrap;
}

.quick-chip {
  padding: 6px 14px;
  border-radius: 999px;
  font-size: 12px;
  background: var(--fill);
  color: var(--text-2);
  transition: all 0.15s ease;
}

.quick-chip.active {
  background: var(--primary-soft);
  color: var(--primary);
  font-weight: 600;
}

.limit-tip {
  margin-top: 10px;
  font-size: 11px;
  color: var(--text-light);
}

/* 汇率 */
.rate-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 0;
  font-size: 13px;
}

.pay-value {
  color: var(--primary);
  font-weight: 600;
}

/* 收据上传 */
.receipt-tip {
  font-size: 12px;
  color: var(--text-light);
  margin-bottom: 10px;
}

.upload-box {
  width: 104px;
  height: 104px;
  border-radius: var(--radius-s);
  background: var(--primary-soft);
  border: 1px dashed rgba(47, 107, 255, 0.35);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  color: var(--primary);
  font-size: 12px;
}

.upload-box .van-icon {
  font-size: 26px;
}

.tip-bar {
  display: flex;
  align-items: flex-start;
  gap: 6px;
  margin-top: 10px;
  padding: 10px 12px;
  font-size: 12px;
  line-height: 1.5;
  color: #a3641a;
  background: var(--warning-soft);
  border-radius: var(--radius-s);
}

/* 收款信息 */
.payee {
  background: var(--fill);
  border-radius: var(--radius-s);
  padding: 6px 12px;
}

.payee-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 6px 0;
  font-size: 13px;
}

.payee-value {
  display: flex;
  align-items: center;
  gap: 8px;
}

.copy-icon {
  color: var(--primary);
  font-size: 14px;
}

.form-field {
  padding-left: 0;
  padding-right: 0;
}
</style>
