<template>
  <div class="empty">
    <div class="empty-icon">
      <van-icon :name="icon" />
    </div>
    <div>{{ text || $t('no_data') }}</div>
    <div v-if="tips" class="muted" style="margin-top: 4px">{{ tips }}</div>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  icon: { type: String, default: 'bar-chart-o' },
  text: { type: String, default: '' },
  tips: { type: String, default: '' }
})
</script>
