<template>
  <div>
    <!-- 表头（市价页显示，首页列表不显示） -->
    <div v-if="showHeader" class="mk-head">
      <div class="col-name">{{ $t('brand_name') }}</div>
      <div class="col-price">
        {{ $t('stock_price') }}{{ resolvedUnit ? '（' + resolvedUnit + '）' : '' }}
      </div>
      <div class="col-change">{{ $t('vs_prev_day') }}</div>
    </div>

    <div
      v-for="item in list"
      :key="item.id || item.code"
      class="mk-row pressable"
      @click="$router.push(`/detail/${item.code}`)"
    >
      <!-- 品牌名称 + 交易所代码 -->
      <div class="col-name">
        <div class="mk-name">
          <span class="mk-bar" :class="trendClass(item.change_rate)"></span>
          <span class="mk-name-text">{{ item.name }}</span>
        </div>
        <div class="mk-code">{{ marketLabel(item.market) }}：{{ item.code }}</div>
      </div>

      <!-- 股票价格 + 前一天价格 -->
      <div class="col-price">
        <div class="mk-price" :class="trendClass(item.change_rate)">
          {{ money(item.price, '', 2) }}
        </div>
        <div class="mk-prev">{{ money(item.prev_close, '', 2) }}</div>
      </div>

      <!-- 与前一天相比：涨跌幅 + 涨跌额 -->
      <div class="col-change">
        <div class="mk-rate" :class="trendClass(item.change_rate)">{{ rate(item.change_rate) }}</div>
        <div class="mk-amount" :class="trendClass(item.change_rate)">
          {{ signed(item.change_amount, '', 2) }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { money, rate, signed, trendClass } from '@/utils/format'
import { t } from '@/locales'

const props = defineProps({
  list: { type: Array, default: () => [] },
  unit: { type: String, default: '' },     // 表头货币单位，如「日元」；不传则按列表币种自动判断
  showHeader: { type: Boolean, default: false }
})

const router = useRouter()

// 单位：优先用传入值；否则列表币种统一时显示对应单位，混合币种（如日股+美股）就不显示
const resolvedUnit = computed(() => {
  if (props.unit) return props.unit
  const currencies = new Set(props.list.map((item) => item.currency).filter(Boolean))
  if (currencies.size !== 1) return ''
  const only = [...currencies][0]
  if (only === 'USD') return t('unit_usd')
  if (only === 'USDT') return 'USDT'
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
</script>

<style scoped>
/* 表头与行共用三列网格：minmax(0,1fr) 防长内容撑开 */
.mk-head,
.mk-row {
  display: grid;
  grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr) minmax(0, 0.9fr);
  gap: 8px;
  align-items: center;
}

.mk-head {
  padding: 10px 0;
  font-size: 12px;
  color: #9aa0aa;
  border-bottom: 1px solid var(--border);
}

.mk-head .col-change {
  text-align: right;
}

.mk-row {
  padding: 13px 0;
  border-bottom: 1px solid var(--border);
}

.mk-row:last-child {
  border-bottom: none;
}

/* 名称列：前置涨跌色小竖条 */
.mk-name {
  display: flex;
  align-items: center;
  gap: 7px;
  min-width: 0;
}

.mk-name-text {
  font-size: 15px;
  font-weight: 700;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.mk-bar {
  flex: none;
  width: 3px;
  height: 14px;
  border-radius: 2px;
  background: #d5d9e0;
}

.mk-bar.up {
  background: var(--up);
}

.mk-bar.down {
  background: var(--down);
}

.mk-code {
  margin-top: 5px;
  font-size: 12px;
  color: #9aa0aa;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.col-price {
  text-align: left;
}

.mk-price {
  font-size: 16px;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.mk-prev {
  margin-top: 5px;
  font-size: 12px;
  color: #9aa0aa;
  font-variant-numeric: tabular-nums;
}

.col-change {
  text-align: right;
  min-width: 0;
}

.mk-rate {
  font-size: 15px;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.mk-amount {
  margin-top: 5px;
  font-size: 12px;
  font-variant-numeric: tabular-nums;
}
</style>
