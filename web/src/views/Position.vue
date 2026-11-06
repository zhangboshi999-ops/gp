<template>
  <div class="page">
    <van-nav-bar :title="$t('position')" />

    <div style="padding: 12px">
      <div class="asset-card">
        <div class="label">{{ $t('market_value') }}(JPY)</div>
        <div class="value">{{ money(totalMarket) }}</div>
        <div class="sub">
          <span>{{ $t('profit') }} {{ signed(totalProfit) }}</span>
        </div>
      </div>

      <div class="card">
        <div v-for="item in list" :key="item.id" style="padding: 12px 0; border-bottom: 1px solid var(--border)">
          <div class="row-between">
            <div>
              <div style="font-weight: 600">{{ item.name }}</div>
              <div class="muted">{{ item.code }} · {{ item.currency }}</div>
            </div>
            <van-button size="small" type="primary" plain @click="$router.push(`/buy/${item.code}?side=sell`)">
              {{ $t('sell') }}
            </van-button>
          </div>
          <div class="pos-grid">
            <div><span class="muted">{{ $t('quantity') }}</span><b>{{ item.quantity }}/{{ item.available }}</b></div>
            <div><span class="muted">{{ $t('cost') }}</span><b>{{ price(item.cost_price, item.currency) }}</b></div>
            <div><span class="muted">{{ $t('price') }}</span><b>{{ price(item.current_price, item.currency) }}</b></div>
            <div><span class="muted">{{ $t('market_value') }}</span><b>{{ money(item.market_value, item.currency) }}</b></div>
          </div>
          <div class="row-between" style="margin-top: 6px">
            <span class="muted">{{ $t('profit') }}</span>
            <span :class="trendClass(item.profit)">
              {{ signed(item.profit, item.currency) }}（{{ rate(item.profit_rate) }}）
            </span>
          </div>
        </div>
        <EmptyState v-if="!list.length" icon="gold-coin-o" />
      </div>
    </div>
  </div>
</template>

<script setup>
import EmptyState from '@/components/EmptyState.vue'
import { computed, onMounted, ref } from 'vue'
import { api } from '@/api'
import { money, price, rate, signed, trendClass } from '@/utils/format'

const list = ref([])
const rateMap = { JPY: 1, USD: 150, USDT: 150 }

const totalMarket = computed(() =>
  list.value.reduce((sum, item) => sum + item.market_value * (rateMap[item.currency] || 1), 0)
)
const totalProfit = computed(() =>
  list.value.reduce((sum, item) => sum + item.profit * (rateMap[item.currency] || 1), 0)
)

async function load() {
  try {
    list.value = await api.positions()
  } catch (e) {
    list.value = []
  }
}

onMounted(load)
</script>

<style scoped>
.pos-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 6px 16px;
  margin-top: 8px;
  font-size: 13px;
}

.pos-grid > div {
  display: flex;
  justify-content: space-between;
}
</style>
