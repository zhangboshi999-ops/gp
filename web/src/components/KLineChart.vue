<template>
  <div class="kline-wrap">
    <div v-if="!items.length" class="kline-empty">{{ emptyText }}</div>

    <template v-else>
      <div class="kline-chart" :style="{ height: height + 'px' }">
        <svg :viewBox="`0 0 ${width} ${height}`" preserveAspectRatio="none" :style="{ width: '100%', height: height + 'px' }">
          <defs>
            <linearGradient :id="gradId" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" :stop-color="lineColor" stop-opacity="0.26" />
              <stop offset="100%" :stop-color="lineColor" stop-opacity="0" />
            </linearGradient>
          </defs>

          <!-- 横向网格 -->
          <line
            v-for="(y, index) in gridLines"
            :key="'g' + index"
            :x1="padding"
            :x2="width - padding"
            :y1="y"
            :y2="y"
            stroke="#f0f1f5"
            stroke-width="1"
          />

          <!-- 最新价参考线 -->
          <line
            v-if="type !== 'line'"
            :x1="padding"
            :x2="width - padding"
            :y1="y(latest)"
            :y2="y(latest)"
            :stroke="lastColor"
            stroke-width="1"
            stroke-dasharray="3 3"
            opacity="0.55"
          />

          <!-- 分时折线 -->
          <template v-if="type === 'line'">
            <path :d="areaPath" :fill="`url(#${gradId})`" />
            <path :d="linePath" :fill="none" :stroke="lineColor" stroke-width="1.6" stroke-linejoin="round" />
          </template>

          <!-- 蜡烛图 -->
          <template v-if="type !== 'line'">
            <template v-for="(item, index) in items" :key="index">
              <line
                :x1="x(index)"
                :x2="x(index)"
                :y1="y(item.high)"
                :y2="y(item.low)"
                :stroke="item.close >= item.open ? upColor : downColor"
                stroke-width="1"
              />
              <rect
                :x="x(index) - candleWidth / 2"
                :y="y(Math.max(item.open, item.close))"
                :width="candleWidth"
                :height="Math.max(Math.abs(y(item.open) - y(item.close)), 1)"
                :fill="item.close >= item.open ? upColor : downColor"
              />
              <rect
                :x="x(index) - candleWidth / 2"
                :y="volumeY(item.volume)"
                :width="candleWidth"
                :height="Math.max(chartHeight + 8 - volumeY(item.volume), 0.5)"
                :fill="item.close >= item.open ? upColor : downColor"
                opacity="0.35"
              />
            </template>
          </template>

          <!-- 均线 MA5 / MA10 / MA20 -->
          <template v-if="type !== 'line' && showMa">
            <polyline
              v-for="line in maLines"
              :key="line.name"
              :points="line.points"
              fill="none"
              :stroke="line.color"
              stroke-width="1"
            />
          </template>
        </svg>

        <!-- 纵轴刻度（HTML 层，避免被 SVG 拉伸变形） -->
        <span class="axis num" :style="{ top: percent(y(max)) + '%' }">{{ fmt(max) }}</span>
        <span class="axis num" :style="{ top: percent(y(min)) + '%' }">{{ fmt(min) }}</span>

        <!-- 最新价标签 -->
        <span v-if="type !== 'line'" class="last-tag num" :class="lastTrend" :style="{ top: percent(y(latest)) + '%' }">
          {{ fmt(latest) }}
        </span>
      </div>

      <div v-if="type !== 'line' && showMa && maLatest.length" class="ma-legend">
        <span v-for="item in maLatest" :key="item.name" :style="{ color: item.color }">
          {{ item.name }} {{ item.value }}
        </span>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  data: { type: Array, default: () => [] },
  type: { type: String, default: 'candle' },
  width: { type: Number, default: 360 },
  height: { type: Number, default: 200 },
  padding: { type: Number, default: 4 },
  showMa: { type: Boolean, default: true },
  emptyText: { type: String, default: '暂无数据' }
})

const upColor = '#f5455c'
const downColor = '#14b97a'
const gradId = 'kline-grad-' + Math.random().toString(36).slice(2, 8)

const chartHeight = computed(() => (props.type === 'line' ? props.height - 8 : props.height * 0.74))
const items = computed(() => props.data.slice(-60))

const max = computed(() => {
  if (!items.value.length) return 1
  return Math.max(...items.value.map((item) => item.high))
})
const min = computed(() => {
  if (!items.value.length) return 0
  return Math.min(...items.value.map((item) => item.low))
})
const maxVolume = computed(() => {
  if (!items.value.length) return 1
  return Math.max(...items.value.map((item) => item.volume))
})

const latest = computed(() => (items.value.length ? items.value[items.value.length - 1].close : 0))
const lastItem = computed(() => (items.value.length ? items.value[items.value.length - 1] : null))
const lastColor = computed(() => {
  if (props.type === 'line') return lineColor.value
  return lastItem.value && lastItem.value.close >= lastItem.value.open ? upColor : downColor
})
const lastTrend = computed(() => (lastColor.value === upColor ? 'up' : 'down'))

const candleWidth = computed(() => {
  const count = Math.max(items.value.length, 1)
  return Math.max(((props.width - props.padding * 2) / count) * 0.62, 1)
})

function x(index) {
  const count = Math.max(items.value.length, 1)
  const usable = props.width - props.padding * 2
  return props.padding + (usable / Math.max(count - 1, 1)) * index
}

function y(value) {
  const range = max.value - min.value || 1
  return 4 + ((max.value - value) / range) * (chartHeight.value - 8)
}

function percent(value) {
  return (value / props.height) * 100
}

function fmt(value) {
  const num = Number(value) || 0
  return num >= 1000 ? num.toFixed(0) : num.toFixed(2)
}

function volumeY(value) {
  const usable = props.height - chartHeight.value - 12
  return chartHeight.value + 8 + usable * (1 - value / maxVolume.value)
}

const gridLines = computed(() => {
  const count = 4
  return Array.from({ length: count + 1 }, (_, i) => 4 + ((chartHeight.value - 8) / count) * i)
})

const linePoints = computed(() => items.value.map((item, index) => `${x(index)},${y(item.close)}`))

const linePath = computed(() => (linePoints.value.length ? 'M' + linePoints.value.join(' L') : ''))

const areaPath = computed(() => {
  if (!linePoints.value.length) return ''
  const base = chartHeight.value
  return `M${x(0)},${base} L${linePoints.value.join(' L')} L${x(items.value.length - 1)},${base} Z`
})

const lineColor = computed(() => {
  const list = items.value
  if (list.length < 2) return upColor
  return list[list.length - 1].close >= list[0].close ? upColor : downColor
})

/* ============ 均线 MA5 / MA10 / MA20 ============ */

const maConfig = [
  { name: 'MA5', period: 5, color: '#f5a623' },
  { name: 'MA10', period: 10, color: '#2f6bff' },
  { name: 'MA20', period: 20, color: '#b45bd6' }
]

const maSeries = computed(() => {
  const closes = items.value.map((item) => item.close)
  return maConfig.map((config) => {
    const values = closes.map((_, index) => {
      if (index + 1 < config.period) {
        return null
      }
      const slice = closes.slice(index + 1 - config.period, index + 1)
      return slice.reduce((sum, value) => sum + value, 0) / config.period
    })
    return { ...config, values }
  })
})

const maLines = computed(() =>
  maSeries.value.map((series) => ({
    name: series.name,
    color: series.color,
    points: series.values
      .map((value, index) => (value === null ? null : `${x(index)},${y(value)}`))
      .filter(Boolean)
      .join(' ')
  }))
)

const maLatest = computed(() =>
  maSeries.value
    .map((series) => {
      const last = [...series.values].reverse().find((value) => value !== undefined && value !== null)
      return last === undefined ? null : { name: series.name, color: series.color, value: last.toFixed(2) }
    })
    .filter(Boolean)
)
</script>

<style scoped>
.kline-chart {
  position: relative;
}

.axis {
  position: absolute;
  left: 2px;
  transform: translateY(-50%);
  font-size: 9px;
  color: #8a91a0;
  background: rgba(255, 255, 255, 0.75);
  padding: 0 2px;
  border-radius: 2px;
}

.last-tag {
  position: absolute;
  right: 2px;
  transform: translateY(-50%);
  font-size: 9px;
  font-weight: 600;
  padding: 1px 4px;
  border-radius: 3px;
  color: #fff;
}

.last-tag.up {
  background: #f5455c;
}

.last-tag.down {
  background: #14b97a;
}
</style>
