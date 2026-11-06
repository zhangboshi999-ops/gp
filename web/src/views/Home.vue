<template>
  <div class="page">
    <van-nav-bar :title="$t('app_name')">
      <template #right>
        <van-icon name="search" size="18" @click="$router.push('/market')" />
      </template>
    </van-nav-bar>

    <!-- 资产概览 -->
    <div class="page-body">
      <!-- 大盘指数 -->
      <div class="index-strip">
        <div class="index-item" v-for="item in indexes" :key="item.code">
          <div class="name">{{ item.name }}</div>
          <div class="value" :class="trendClass(item.change_rate)">{{ price(item.price, '') }}</div>
          <div class="rate" :class="trendClass(item.change_rate)">{{ rate(item.change_rate) }}</div>
        </div>
      </div>

      <div class="asset-card pressable" @click="goAsset">
        <div class="label">
          <span>{{ $t('total_asset') }}(JPY)</span>
          <van-icon :name="hidden ? 'closed-eye' : 'eye-o'" @click.stop="hidden = !hidden" />
        </div>
        <div class="value">{{ hidden ? '******' : money(info ? info.total_asset : 0, '') }}</div>
        <div class="sub">
          <span>{{ $t('balance') }} {{ hidden ? '****' : money(info ? info.total_balance : 0) }}</span>
          <span>{{ $t('market_value') }} {{ hidden ? '****' : money(info ? info.market_value : 0) }}</span>
        </div>
      </div>

      <!-- 快捷入口 -->
      <div class="card">
        <div class="quick-grid">
          <div class="quick-item" @click="go('/deposit')">
            <div class="icon quick-icon-blue"><van-icon name="refund-o" /></div>
            <div class="name">{{ $t('deposit') }}</div>
          </div>
          <div class="quick-item" @click="go('/withdraw-bank')">
            <div class="icon quick-icon-green"><van-icon name="gold-coin-o" /></div>
            <div class="name">{{ $t('withdraw') }}</div>
          </div>
          <div class="quick-item" @click="go('/transfer')">
            <div class="icon quick-icon-orange"><van-icon name="exchange" /></div>
            <div class="name">{{ $t('transfer') }}</div>
          </div>
          <div class="quick-item" @click="go('/ipo')">
            <div class="icon quick-icon-purple"><van-icon name="fire-o" /></div>
            <div class="name">{{ $t('ipo') }}</div>
          </div>
          <div class="quick-item" @click="go('/orders')">
            <div class="icon quick-icon-blue"><van-icon name="orders-o" /></div>
            <div class="name">{{ $t('my_orders') }}</div>
          </div>
          <div class="quick-item" @click="go('/verify')">
            <div class="icon quick-icon-green"><van-icon name="idcard" /></div>
            <div class="name">{{ $t('verification') }}</div>
          </div>
          <div class="quick-item" @click="go('/paypwd')">
            <div class="icon quick-icon-orange"><van-icon name="lock" /></div>
            <div class="name">{{ $t('pay_password') }}</div>
          </div>
          <div class="quick-item" @click="go('/support')">
            <div class="icon quick-icon-red"><van-icon name="service-o" /></div>
            <div class="name">{{ $t('support') }}</div>
          </div>
        </div>
      </div>

      <!-- 热门标的 -->
      <div class="card">
        <div class="card-title">
          <span>{{ $t('hot') }}</span>
          <span class="more pressable" @click="$router.push('/market')">{{ $t('market') }} ›</span>
        </div>
        <div v-if="loading.hot">
          <div class="sk-row" v-for="n in 3" :key="n">
            <div style="flex: 1">
              <div class="sk" style="width: 120px; height: 14px"></div>
              <div class="sk" style="width: 80px; height: 11px; margin-top: 6px"></div>
            </div>
            <div style="text-align: right">
              <div class="sk" style="width: 70px; height: 14px"></div>
              <div class="sk" style="width: 56px; height: 16px; margin-top: 6px"></div>
            </div>
          </div>
        </div>
        <template v-else>
          <MarketList :list="hot" show-header />
          <div v-if="!hot.length" class="empty">
            <div class="empty-icon"><van-icon name="chart-trending-o" /></div>
            {{ $t('no_data') }}
          </div>
        </template>
      </div>

      <!-- 榜单 -->
      <div class="card">
        <van-tabs v-model:active="rankType" type="card" @change="loadRank">
          <van-tab :title="$t('gainers')" name="gainers" />
          <van-tab :title="$t('losers')" name="losers" />
          <van-tab :title="$t('turnover_rank')" name="turnover" />
        </van-tabs>
        <MarketList :list="rankList" show-header />
        <div v-if="!rankList.length" class="empty">
          <div class="empty-icon"><van-icon name="bar-chart-o" /></div>
          {{ $t('no_data') }}
        </div>
      </div>

      <!-- 新股 -->
      <div class="card">
        <div class="card-title">
          <span>{{ $t('ipo') }}</span>
          <span class="more pressable" @click="$router.push('/ipo')">{{ $t('ipo_apply') }} ›</span>
        </div>
        <div v-for="item in ipos.slice(0, 2)" :key="item.id" class="stock-row" @click="$router.push(`/ipo/${item.id}`)">
          <div>
            <div class="name">{{ item.name }}</div>
            <div class="code">{{ item.code }} · {{ $t('issue_price') }} {{ price(item.issue_price, 'JPY') }}</div>
          </div>
          <van-tag :type="item.status === 'ongoing' ? 'danger' : 'default'">
            {{ item.status === 'ongoing' ? $t('pending') : $t('success') }}
          </van-tag>
        </div>
      </div>

      <!-- 公告 -->
      <div class="card">
        <div class="card-title">
          <span>{{ $t('notice') }}</span>
          <span class="more pressable" @click="$router.push('/support')">{{ $t('faq') }} ›</span>
        </div>
        <div v-for="item in notices.slice(0, 3)" :key="item.id" class="muted" style="padding: 6px 0">
          · {{ item.title }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import MarketList from '@/components/MarketList.vue'
import { api } from '@/api'
import { useUserStore } from '@/store/user'
import { money, price, rate, trendClass } from '@/utils/format'

const router = useRouter()
const user = useUserStore()
const hot = ref([])
const ipos = ref([])
const notices = ref([])
const indexes = ref([])
const rankList = ref([])
const rankType = ref('gainers')
const hidden = ref(false)
const loading = ref({ hot: true, ipo: true })

const info = computed(() => user.info)

function go(path) {
  router.push(path)
}

function goAsset() {
  router.push('/wallet/JPY')
}

onMounted(async () => {
  if (user.isLogin && !user.info) {
    await user.fetchInfo()
  }
  try {
    hot.value = await api.marketHot()
  } catch (e) {
    hot.value = []
  } finally {
    loading.value.hot = false
  }
  try {
    ipos.value = await api.ipoList()
  } catch (e) {
    ipos.value = []
  } finally {
    loading.value.ipo = false
  }
  try {
    const list = await api.supportList({ type: 'notice' })
    notices.value = list || []
  } catch (e) {
    notices.value = []
  }
  loadIndexes()
  loadRank()
})

async function loadIndexes() {
  try {
    indexes.value = await api.marketIndexes()
  } catch (e) {
    indexes.value = []
  }
}

async function loadRank() {
  try {
    // 榜单只统计股票（日本 + 美国）
    const data = await api.marketList({ sort: rankType.value, type: 'stock', limit: 5 })
    rankList.value = data.list || []
  } catch (e) {
    rankList.value = []
  }
}
</script>
