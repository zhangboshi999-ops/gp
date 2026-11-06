<template>
  <div class="page">
    <van-nav-bar :title="stock.name || '--'" left-arrow @click-left="$router.back()">
      <template #right>
        <van-icon :name="stock.favorite ? 'star' : 'star-o'" size="18" @click="toggleFavorite" />
      </template>
    </van-nav-bar>

    <div style="padding: 12px">
      <div class="card">
        <div class="row-between">
          <div>
            <div class="num price-lg" :class="[trendClass(stock.change_rate), flash]">
              {{ price(stock.price, stock.currency) }}
            </div>
            <div :class="trendClass(stock.change_rate)" style="font-size: 13px">
              {{ signed(stock.change_amount, '', 2) }} · {{ rate(stock.change_rate) }}
            </div>
          </div>
          <div style="text-align: right" class="muted">
            <div>{{ stock.code }}</div>
            <div>{{ stock.market }} · {{ stock.currency }}</div>
            <div class="live" :class="{ on: connected }">
              {{ connected ? '● ' + $t('realtime') : '○ ' + $t('offline') }}
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <van-tabs v-model:active="chartType" type="card" style="margin-bottom: 8px">
          <van-tab :title="$t('timeline')" name="line" />
          <van-tab :title="$t('minute')" name="minute" />
          <van-tab :title="$t('kline')" name="candle" />
        </van-tabs>
        <KLineChart :data="chartData" :type="chartType === 'line' ? 'line' : 'candle'" :empty-text="t('no_data')" />
        <div class="muted" style="display: flex; justify-content: space-between; margin-top: 6px">
          <span v-for="item in kline.slice(-40).filter((_, i, arr) => i % 20 === 0)" :key="item.time">
            {{ item.date }}
          </span>
        </div>
      </div>

      <div class="card">
        <div class="card-title">{{ $t('market') }}</div>
        <div class="data-grid">
          <div><span class="muted">{{ $t('open') }}</span><b class="num">{{ price(stock.open, stock.currency) }}</b></div>
          <div><span class="muted">{{ $t('high') }}</span><b class="num">{{ price(stock.high, stock.currency) }}</b></div>
          <div><span class="muted">{{ $t('low') }}</span><b class="num">{{ price(stock.low, stock.currency) }}</b></div>
          <div><span class="muted">{{ $t('prev_close') }}</span><b class="num">{{ price(stock.prev_close, stock.currency) }}</b></div>
          <div><span class="muted">{{ $t('volume') }}</span><b class="num">{{ volume(stock.volume) }}</b></div>
          <div><span class="muted">{{ $t('total') }}</span><b class="num">{{ money(stock.turnover, stock.currency, 0) }}</b></div>
        </div>
      </div>

      <!-- 统计与基本面（Twelve Data / CoinGecko） -->
      <div class="card">
        <div class="card-title">
          <span>{{ $t('market_cap') }}</span>
          <span class="muted" v-if="stock.source">{{ stock.source }}</span>
        </div>
        <div class="data-grid">
          <div><span class="muted">{{ $t('turnover_rate') }}</span><b class="num">{{ stock.turnover_rate || '-' }}%</b></div>
          <div><span class="muted">{{ $t('amplitude') }}</span><b class="num">{{ stock.amplitude || '-' }}%</b></div>
          <div><span class="muted">{{ $t('market_cap') }}</span><b>{{ stock.market_cap ? money(stock.market_cap, stock.currency, 0) : '-' }}</b></div>
          <div><span class="muted">{{ $t('pe') }}</span><b>{{ stock.pe_ratio || '-' }}</b></div>
          <div><span class="muted">{{ $t('pb') }}</span><b>{{ stock.pb_ratio || '-' }}</b></div>
          <div><span class="muted">{{ $t('dividend') }}</span><b class="num">{{ stock.dividend_yield || '-' }}%</b></div>
          <div>
            <span class="muted">{{ $t('month_52w') }}</span>
            <b>{{ stock.low_52w ? price(stock.low_52w, '') + ' ~ ' + price(stock.high_52w, '') : '-' }}</b>
          </div>
          <div><span class="muted">{{ $t('sector') }}</span><b>{{ stock.sector || '-' }}</b></div>
        </div>
      </div>
    </div>

    <div class="submit-bar">
      <van-button plain type="default" @click="toggleFavorite">
        {{ stock.favorite ? $t('remove_favorite') : $t('add_favorite') }}
      </van-button>
      <van-button plain type="primary" @click="goTrade('sell')">{{ $t('sell') }}</van-button>
      <van-button type="danger" @click="goTrade('buy')">{{ $t('buy') }}</van-button>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { showToast } from 'vant'
import KLineChart from '@/components/KLineChart.vue'
import { api } from '@/api'
import { connected, subscribe, unsubscribe } from '@/utils/socket'
import { money, price, rate, signed, trendClass, volume } from '@/utils/format'
import { t } from '@/locales'

const route = useRoute()
const router = useRouter()
const code = route.params.code
const stock = ref({})
const kline = ref([])
const timeline = ref([])
const flash = ref('')
let flashTimer = null

watch(
  () => stock.value.price,
  (now, old) => {
    if (!old || !now || now === old) return
    flash.value = now > old ? 'flash-up' : 'flash-down'
    clearTimeout(flashTimer)
    flashTimer = setTimeout(() => (flash.value = ''), 700)
  }
)
const chartType = ref('candle')

// 分时 / 1分K 使用实时 1 分钟数据，未连上实时服务时退回日线
const chartData = computed(() => {
  if (chartType.value === 'line') {
    return timeline.value.length ? timeline.value : kline.value
  }
  if (chartType.value === 'minute') {
    return timeline.value.length ? timeline.value : kline.value.slice(-60)
  }
  return kline.value
})

const pad = (n) => String(n).padStart(2, '0')
const fmtDate = (ts) => {
  const d = new Date(ts * 1000)
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
}
const fmtTime = (ts) => {
  const d = new Date(ts * 1000)
  return `${pad(d.getHours())}:${pad(d.getMinutes())}`
}

function applyQuote(quote) {
  if (!quote) return
  const favorite = stock.value.favorite
  stock.value = { ...stock.value, ...quote, favorite }
}

function toMinuteBar(bar) {
  return {
    time: bar.time,
    date: fmtTime(bar.time),
    open: bar.open,
    high: bar.high,
    low: bar.low,
    close: bar.close,
    volume: bar.volume
  }
}

/**
 * 实时 K 线：更新分时序列，并让日线最后一根跟随现价
 */
function mergeBar(bar, quote) {
  if (!bar) return
  const item = toMinuteBar(bar)
  const list = timeline.value
  const last = list[list.length - 1]
  if (last && last.time === item.time) {
    list[list.length - 1] = item
  } else {
    list.push(item)
    if (list.length > 240) {
      list.shift()
    }
  }

  const today = fmtDate(bar.time)
  const days = kline.value
  const dayLast = days[days.length - 1]
  if (dayLast && dayLast.date === today) {
    dayLast.high = Math.max(dayLast.high, item.high)
    dayLast.low = Math.min(dayLast.low, item.low)
    dayLast.close = item.close
    if (quote && quote.volume) {
      dayLast.volume = quote.volume
    }
  } else if (!dayLast || dayLast.date < today) {
    days.push({
      time: bar.time,
      date: today,
      open: quote && quote.open ? quote.open : item.open,
      high: item.high,
      low: item.low,
      close: item.close,
      volume: quote && quote.volume ? quote.volume : item.volume
    })
  }
}

function onRealtime(message) {
  if (message.type === 'snapshot') {
    applyQuote(message.quote)
    if (Array.isArray(message.bars) && message.bars.length) {
      timeline.value = message.bars.map(toMinuteBar)
    }
    if (message.bar) {
      mergeBar(message.bar, message.quote)
    }
    return
  }
  if (message.type === 'bar') {
    applyQuote(message.quote)
    mergeBar(message.bar, message.quote)
  }
}

async function loadDetail() {
  try {
    stock.value = await api.marketDetail({ code })
  } catch (e) {
    stock.value = {}
  }
}

async function loadKline() {
  try {
    kline.value = await api.kline({ code, period: '1d', limit: 90 })
  } catch (e) {
    kline.value = []
  }
}

async function toggleFavorite() {
  try {
    const data = await api.toggleFavorite({ code })
    stock.value.favorite = data.favorite
    showToast(data.favorite ? t('add_favorite') : t('remove_favorite'))
  } catch (e) {
    showToast(e.message || t('network_error'))
  }
}

function goTrade(side) {
  router.push({ path: `/buy/${code}`, query: { side } })
}

onMounted(() => {
  loadDetail()
  loadKline()
  subscribe(code, onRealtime)
})

onUnmounted(() => {
  clearTimeout(flashTimer)
  unsubscribe(code, onRealtime)
})
</script>

<style scoped>
.live {
  margin-top: 2px;
  font-size: 11px;
  color: #b0b3bb;
}

.live.on {
  color: #14b97a;
}

.price-lg {
  font-size: 26px;
  font-weight: 700;
  padding: 0 2px;
}

.data-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px 16px;
}

.data-grid > div {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
}
</style>
