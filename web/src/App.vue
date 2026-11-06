<template>
  <router-view />
  <TabBar v-if="showTab" />
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import TabBar from '@/components/TabBar.vue'
import { api } from '@/api'
import { useUserStore } from '@/store/user'
import { setEnabledLanguages } from '@/locales'

const route = useRoute()
const user = useUserStore()
const showTab = computed(() => !!route.meta.tab)

onMounted(() => {
  if (user.isLogin) {
    user.fetchInfo()
  }
  // 语言列表由后端 extra/lang.php 配置，拉取失败则展示本地全部语言
  api
    .langs()
    .then((data) => setEnabledLanguages((data.list || []).map((item) => item.code)))
    .catch(() => setEnabledLanguages([]))
})
</script>
