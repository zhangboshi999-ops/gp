<template>
  <div class="page">
    <van-nav-bar :title="$t('crypto_wallet')" left-arrow @click-left="$router.back()" />

    <div class="page-body">
      <!-- 安全提示 -->
      <div class="tip-bar">
        <van-icon name="info-o" />
        <span>{{ $t('wallet_tip') }}</span>
      </div>

      <!-- 钱包列表 -->
      <div v-if="list.length" class="wallet-list">
        <div v-for="item in list" :key="item.id" class="wallet-card">
          <div class="chain-badge" :class="'chain-' + item.chain.toLowerCase()">
            {{ chainShort(item.chain) }}
          </div>
          <div class="wallet-main">
            <div class="row-between">
              <div class="chain-name">
                {{ item.chain }}
                <span v-if="item.label" class="label-tag">{{ item.label }}</span>
              </div>
              <div class="copy-btn pressable" @click="copy(item.address)">
                <van-icon name="description" />
                {{ $t('copy') }}
              </div>
            </div>
            <div class="wallet-addr num">{{ item.address }}</div>
          </div>
        </div>
      </div>
      <div v-else class="card">
        <EmptyState icon="paid" />
      </div>

      <van-button block round type="primary" class="add-btn" @click="openAdd">
        <van-icon name="plus" />
        {{ $t('add_wallet') }}
      </van-button>
    </div>

    <!-- 添加钱包 -->
    <van-popup v-model:show="show" position="bottom" round>
      <div class="sheet">
        <div class="sheet-head">
          <span class="sheet-title">{{ $t('add_wallet') }}</span>
          <van-icon name="close" class="sheet-close" @click="show = false" />
        </div>

        <div class="sheet-body">
          <div class="field-label">{{ $t('chain_type') }}</div>
          <div class="chain-grid">
            <div
              v-for="item in chains"
              :key="item.name"
              class="chain-item"
              :class="{ active: form.chain === item.name }"
              @click="form.chain = item.name"
            >
              <div class="chain-badge sm" :class="'chain-' + item.name.toLowerCase()">
                {{ chainShort(item.name) }}
              </div>
              <span class="chain-desc">{{ item.desc }}</span>
              <van-icon v-if="form.chain === item.name" name="checked" class="chain-check" />
            </div>
          </div>

          <div class="field-label">{{ $t('address') }}</div>
          <van-field
            v-model="form.address"
            type="textarea"
            rows="2"
            autosize
            maxlength="120"
            class="addr-field num"
            :placeholder="$t('address')"
          />

          <div class="field-label">{{ $t('remark') }}（{{ $t('optional') }}）</div>
          <van-field v-model="form.label" maxlength="20" :placeholder="$t('remark')" />
        </div>

        <div class="sheet-foot">
          <van-button block round plain @click="show = false">{{ $t('cancel') }}</van-button>
          <van-button block round type="primary" :loading="saving" :disabled="!canSubmit" @click="submit">
            {{ $t('submit') }}
          </van-button>
        </div>
      </div>
    </van-popup>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { showToast } from 'vant'
import EmptyState from '@/components/EmptyState.vue'
import { api } from '@/api'
import { t } from '@/locales'

const list = ref([])
const show = ref(false)
const saving = ref(false)
const form = reactive({ chain: 'ETH', address: '', label: '' })

const chains = [
  { name: 'BTC', desc: 'Bitcoin' },
  { name: 'ETH', desc: 'Ethereum' },
  { name: 'USDT', desc: 'Tether' }
]

const canSubmit = computed(() => form.address.trim().length >= 20)

function chainShort(chain) {
  const value = (chain || '').toUpperCase()
  return value === 'USDT' ? 'TRC' : value
}

async function load() {
  try {
    list.value = await api.wallets()
  } catch (e) {
    list.value = []
  }
}

function openAdd() {
  form.address = ''
  form.label = ''
  show.value = true
}

async function copy(text) {
  if (!text) return
  try {
    await navigator.clipboard.writeText(text)
  } catch (e) {
    // 非 HTTPS 环境降级
    const input = document.createElement('textarea')
    input.value = text
    document.body.appendChild(input)
    input.select()
    document.execCommand('copy')
    input.remove()
  }
  showToast(t('wallet_copied'))
}

async function submit() {
  const address = form.address.trim()
  if (address.length < 20) {
    showToast(t('wallet_invalid'))
    return
  }
  saving.value = true
  try {
    await api.addWallet({ chain: form.chain, address, label: form.label.trim() })
    showToast(t('operation_success'))
    show.value = false
    load()
  } catch (e) {
    showToast(e.message || t('network_error'))
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.tip-bar {
  display: flex;
  align-items: flex-start;
  gap: 6px;
  padding: 10px 12px;
  margin-bottom: 12px;
  font-size: 12px;
  line-height: 1.5;
  color: #a3641a;
  background: var(--warning-soft);
  border-radius: var(--radius-s);
}

.wallet-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 14px;
}

.wallet-card {
  display: flex;
  gap: 12px;
  padding: 14px;
  background: var(--card);
  border-radius: var(--radius);
  box-shadow: var(--shadow-1);
}

.chain-badge {
  flex: 0 0 auto;
  width: 42px;
  height: 42px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.chain-badge.sm {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  font-size: 11px;
}

.chain-btc {
  background: linear-gradient(135deg, #f9a02c, #f7931a);
}

.chain-eth {
  background: linear-gradient(135deg, #8a9bff, #627eea);
}

.chain-usdt {
  background: linear-gradient(135deg, #38c793, #26a17b);
}

.wallet-main {
  flex: 1;
  min-width: 0;
}

.chain-name {
  font-size: 15px;
  font-weight: 600;
}

.label-tag {
  margin-left: 6px;
  font-size: 11px;
  font-weight: 400;
  color: var(--text-2);
  background: var(--fill);
  border-radius: 4px;
  padding: 1px 6px;
}

.copy-btn {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  font-size: 12px;
  color: var(--primary);
  background: var(--primary-soft);
  border-radius: 999px;
  padding: 3px 10px;
}

.wallet-addr {
  margin-top: 6px;
  font-size: 12.5px;
  color: var(--text-2);
  word-break: break-all;
  line-height: 1.5;
}

.add-btn {
  margin-top: 4px;
}

/* 弹层 */
.sheet {
  padding-bottom: var(--safe-bottom);
}

.sheet-head {
  position: relative;
  padding: 16px 0 12px;
  text-align: center;
}

.sheet-title {
  font-size: 16px;
  font-weight: 600;
}

.sheet-close {
  position: absolute;
  right: 16px;
  top: 16px;
  font-size: 18px;
  color: var(--text-light);
}

.sheet-body {
  padding: 0 16px;
  max-height: 60vh;
  overflow-y: auto;
}

.field-label {
  font-size: 12px;
  color: var(--text-light);
  margin: 14px 0 8px;
}

.chain-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

.chain-item {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  padding: 12px 4px;
  border: 1px solid var(--border);
  border-radius: var(--radius-s);
  transition: all 0.16s ease;
}

.chain-item.active {
  border-color: var(--primary);
  background: var(--primary-soft);
}

.chain-desc {
  font-size: 11px;
  color: var(--text-2);
}

.chain-check {
  position: absolute;
  top: 4px;
  right: 4px;
  font-size: 12px;
  color: var(--primary);
}

.addr-field {
  border: 1px solid var(--border);
  border-radius: var(--radius-s);
  font-size: 13px;
}

.sheet-foot {
  display: flex;
  gap: 10px;
  padding: 14px 16px 16px;
}
</style>
