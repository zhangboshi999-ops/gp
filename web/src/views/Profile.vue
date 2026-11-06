<template>
  <div class="page">
    <van-nav-bar :title="$t('my')">
      <template #right>
        <van-icon name="setting-o" size="18" @click="$router.push('/settings')" />
      </template>
    </van-nav-bar>

    <div style="padding: 12px">
      <!-- 用户信息 -->
      <div class="card profile-header">
        <van-image round width="52" height="52" :src="avatar">
          <template #error>
            <div class="avatar-fallback">{{ initial }}</div>
          </template>
        </van-image>
        <div style="flex: 1; margin-left: 12px">
          <div style="font-size: 17px; font-weight: 600" @click="goLogin">
            {{ user.info ? user.info.nickname || user.info.username : $t('login') }}
          </div>
          <div class="muted">
            <van-tag :type="kycType">{{ kycText }}</van-tag>
          </div>
        </div>
        <van-icon name="arrow" color="#c8ccd4" />
      </div>

      <!-- 资产 -->
      <div class="card">
        <div class="card-title">{{ $t('total_asset') }}(JPY)</div>
        <div class="asset-total">{{ money(user.info ? user.info.total_asset : 0) }}</div>
        <div class="asset-sub muted">
          <span>{{ $t('balance') }} {{ money(user.info ? user.info.total_balance : 0) }}</span>
          <span>{{ $t('market_value') }} {{ money(user.info ? user.info.market_value : 0) }}</span>
        </div>
        <div class="account-grid">
          <div
            v-for="item in accounts"
            :key="item.currency"
            class="account-item"
            @click="$router.push(`/wallet/${item.currency}`)"
          >
            <div class="muted">{{ currencyName(item.currency) }}</div>
            <div class="account-amount">{{ money(item.balance, item.currency) }}</div>
          </div>
        </div>
      </div>

      <!-- 功能列表 -->
      <div class="card" style="padding: 0">
        <van-cell is-link :title="$t('verification')" icon="idcard" @click="$router.push('/verify')" />
        <van-cell is-link :title="$t('pay_password')" icon="lock" @click="$router.push('/paypwd')" />
        <van-cell is-link :title="$t('bank_card')" icon="card" @click="$router.push('/banks')" />
        <van-cell is-link :title="$t('crypto_wallet')" icon="paid" @click="$router.push('/wallets')" />
        <van-cell is-link :title="$t('records')" icon="orders-o" @click="$router.push('/records')" />
        <van-cell is-link :title="$t('ipo')" icon="fire-o" @click="$router.push('/ipo')" />
        <van-cell is-link :title="$t('support')" icon="service-o" @click="$router.push('/support')" />
        <van-cell is-link :title="$t('language')" icon="font-o" :value="localeLabel" @click="showLang = true" />
      </div>

      <div style="margin-top: 16px">
        <van-button block plain type="danger" @click="logout">{{ $t('logout') }}</van-button>
      </div>
    </div>

    <van-action-sheet
      v-model:show="showLang"
      :actions="langActions"
      @select="selectLang"
      :cancel-text="$t('cancel')"
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
import { localeState, setLocale, t } from '@/locales'

const router = useRouter()
const user = useUserStore()
const accounts = ref([])
const showLang = ref(false)

const langActions = [
  { name: '日本語', value: 'ja' },
  { name: '简体中文', value: 'zh' },
  { name: 'English', value: 'en' }
]

const avatar = computed(() => (user.info && user.info.avatar ? user.info.avatar : ''))
const initial = computed(() => (user.info ? (user.info.nickname || user.info.username || 'U').slice(0, 1) : '?'))
const localeLabel = computed(() => langActions.find((item) => item.value === localeState.locale)?.name || '日本語')

const kycMap = {
  0: { text: 'kyc_none', type: 'default' },
  1: { text: 'kyc_pending', type: 'warning' },
  2: { text: 'kyc_pass', type: 'success' },
  3: { text: 'kyc_reject', type: 'danger' }
}

const kycText = computed(() => t(kycMap[user.kycStatus]?.text || 'kyc_none'))
const kycType = computed(() => kycMap[user.kycStatus]?.type || 'default')

function currencyName(currency) {
  return t(currency === 'JPY' ? 'jpy_account' : currency === 'USD' ? 'usd_account' : 'usdt_account')
}

function goLogin() {
  if (!user.isLogin) {
    router.push('/login')
  }
}

function selectLang(action) {
  setLocale(action.value)
  showLang.value = false
}

function logout() {
  user.logout()
  accounts.value = []
  showToast(t('operation_success'))
  router.push('/login')
}

async function load() {
  if (user.isLogin && !user.info) {
    await user.fetchInfo()
  }
  try {
    accounts.value = await api.accounts()
  } catch (e) {
    accounts.value = []
  }
}

onMounted(load)
</script>

<style scoped>
.profile-header {
  display: flex;
  align-items: center;
}

.avatar-fallback {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2f6bff, #1b46c8);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  font-weight: 700;
}

/* 总资产：位数多时自动缩字号并允许折行，不撑破卡片 */
.asset-total {
  font-size: clamp(20px, 6.5vw, 24px);
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  overflow-wrap: anywhere;
  line-height: 1.2;
}

/* 余额 / 市值两项：放不下就换行，不再溢出 */
.asset-sub {
  margin-top: 6px;
  display: flex;
  flex-wrap: wrap;
  gap: 2px 12px;
  font-size: 12px;
}

.asset-sub > span {
  min-width: 0;
  overflow-wrap: anywhere;
}

/* minmax(0, 1fr) 是关键：默认的 1fr 会被长金额顶开，导致整页横向溢出 */
.account-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
  margin-top: 12px;
}

.account-item {
  background: #f7f8fb;
  border-radius: 10px;
  padding: 10px 6px;
  text-align: center;
  min-width: 0;
}

.account-amount {
  font-weight: 600;
  font-size: clamp(12px, 3.4vw, 15px);
  font-variant-numeric: tabular-nums;
  overflow-wrap: anywhere;
  line-height: 1.25;
}
</style>
