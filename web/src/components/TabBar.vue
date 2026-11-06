<template>
  <van-tabbar v-model="active" route active-color="#2f6bff" inactive-color="#8a91a0" fixed safe-area-inset-bottom>
    <van-tabbar-item replace to="/">
      <template #icon="{ active: on }">
        <van-icon :name="on ? 'wap-home' : 'home-o'" :class="['tab-icon', { active: on }]" />
      </template>
      {{ $t('home') }}
    </van-tabbar-item>
    <van-tabbar-item replace to="/follow">
      <template #icon="{ active: on }">
        <van-icon :name="on ? 'friends' : 'friends-o'" :class="['tab-icon', { active: on }]" />
      </template>
      {{ $t('follow') }}
    </van-tabbar-item>
    <van-tabbar-item replace to="/market">
      <template #icon="{ active: on }">
        <van-icon :name="on ? 'graphic' : 'chart-trending-o'" :class="['tab-icon', { active: on }]" />
      </template>
      {{ $t('market') }}
    </van-tabbar-item>
    <van-tabbar-item replace to="/position">
      <template #icon="{ active: on }">
        <van-icon :name="on ? 'gold-coin' : 'gold-coin-o'" :class="['tab-icon', { active: on }]" />
      </template>
      {{ $t('position') }}
    </van-tabbar-item>
    <van-tabbar-item replace to="/my">
      <template #icon="{ active: on }">
        <van-icon :name="on ? 'user' : 'user-o'" :class="['tab-icon', { active: on }]" />
      </template>
      {{ $t('my') }}
    </van-tabbar-item>
  </van-tabbar>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const tabPaths = ['/', '/follow', '/market', '/position', '/my']
const active = ref(Math.max(tabPaths.indexOf(route.path), 0))

watch(
  () => route.path,
  (path) => {
    const index = tabPaths.indexOf(path)
    if (index >= 0) {
      active.value = index
    }
  }
)
</script>

<style scoped>
.tab-icon {
  font-size: 22px;
  transition: transform 0.18s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.tab-icon.active {
  transform: translateY(-1px) scale(1.08);
}

:deep(.van-tabbar-item--active) {
  font-weight: 600;
}
</style>
