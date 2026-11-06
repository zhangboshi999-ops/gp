<template>
  <div class="stock-row pressable" @click="$router.push(`/detail/${item.code}`)">
    <div style="flex: 1; min-width: 0">
      <div class="name">
        {{ item.name }}
        <span class="tag-market">{{ marketName }}</span>
      </div>
      <div class="code">{{ item.code }} · {{ item.currency }}</div>
    </div>
    <div style="text-align: right">
      <div class="price" :class="trendClass(item.change_rate)">{{ price(item.price, item.currency) }}</div>
      <div class="rate">
        <span class="rate-badge" :class="trendClass(item.change_rate)">{{ rate(item.change_rate) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { price, rate, trendClass } from '@/utils/format'

const props = defineProps({
  item: { type: Object, required: true }
})

const marketNames = { TSE: '東証', NASDAQ: 'NASDAQ', NY: 'NYSE', CRYPTO: 'CRYPTO' }

const marketName = computed(() => marketNames[props.item.market] || props.item.market)
</script>
