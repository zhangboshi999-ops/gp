<template>
  <div class="page">
    <van-nav-bar :title="$t('market')" />

    <van-search
      v-model="keyword"
      :placeholder="$t('search')"
      shape="round"
      background="#f4f5f8"
      @search="load"
    />

    <!-- 日本 / 美国 / 加密 切换 -->
    <div class="market-tabs">
      <div
        v-for="tab in tabs"
        :key="tab.key"
        class="market-tab"
        :class="{ active: active === tab.key }"
        @click="switchTab(tab.key)"
      >
        {{ tab.name }}
      </div>
    </div>

    <van-pull-refresh v-model="refreshing" @refresh="onRefresh">
      <div class="card market-card">
        <div v-if="loading">
          <div class="sk-row" v-for="n in 6" :key="n">
            <div style="flex: 1">
              <div class="sk" style="width: 130px; height: 14px"></div>
              <div class="sk" style="width: 110px; height: 11px; margin-top: 6px"></div>
            </div>
            <div style="text-align: right">
              <div class="sk" style="width: 72px; height: 14px"></div>
              <div class="sk" style="width: 58px; height: 11px; margin-top: 6px"></div>
            </div>
          </div>
        </div>

        <template v-else>
          <MarketList :list="list" :unit="priceUnit" show-header />
          <div v-if="!list.length" class="empty">
            <div class="empty-icon"><van-icon name="search" /></div>
            {{ $t('no_data') }}
          </div>
        </template>
      </div>
    </van-pull-refresh>
  </div>
</template>

<script setup>
import { computed, onUnmounted, ref, watch } from 'vue'
import MarketList from '@/components/MarketList.vue'
import { api } from '@/api'
import { t } from '@/locales'

const keyword = ref('')
const active = ref('TSE')
const list = ref([])
const loading = ref(true)
const refreshing = ref(false)
let timer = null

// 日本 / 美国 / 加密 三个市场（美股由后端把 US 展开成 NASDAQ + NYSE）
const tabs = [
  { key: 'TSE', name: t('jp_market') },
  { key: 'US', name: t('us_market') },
  { key: 'CRYPTO', name: t('crypto') }
]

// 表头货币单位
const priceUnit = computed(() => {
  if (active.value === 'US') return t('unit_usd')
  if (active.value === 'CRYPTO') return 'USDT'
  return t('unit_jpy')
})

// 「东京证券交易所代码：1417」这类前缀
const marketLabelKeys = {
  TSE: 'lbl_tse_code',
  NASDAQ: 'lbl_nasdaq_code',
  NYSE: 'lbl_nyse_code',
  CRYPTO: 'crypto'
}
const marketLabel = (market) => t(marketLabelKeys[market] || market)

function switchTab(key) {
  if (active.value === key) return
  active.value = key
  loading.value = true
  load()
}

async function load() {
  const params = { page: 1, limit: 50, market: active.value }
  if (keyword.value) {
    params.keyword = keyword.value
  }
  try {
    const data = await api.marketList(params)
    list.value = data.list || []
  } catch (e) {
    list.value = []
  } finally {
    loading.value = false
  }
}

async function onRefresh() {
  await load()
  refreshing.value = false
}

// 搜索防抖，避免逐字请求
watch(keyword, () => {
  clearTimeout(timer)
  timer = setTimeout(load, 300)
})

onUnmounted(() => clearTimeout(timer))
load()
</script>

<style scoped>
/* 市场 tab：选中的带下划线 */
.market-tabs {
  display: flex;
  gap: 28px;
  padding: 14px 16px 0;
  background: var(--bg);
}

.market-tab {
  font-size: 17px;
  font-weight: 600;
  color: #9aa0aa;
  padding-bottom: 9px;
  position: relative;
  cursor: pointer;
}

.market-tab.active {
  color: var(--text);
}

.market-tab.active::after {
  content: '';
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 3px;
  border-radius: 2px;
  background: var(--primary);
}

.market-card {
  margin-top: 12px;
  padding: 4px 14px 6px;
}

</style>
