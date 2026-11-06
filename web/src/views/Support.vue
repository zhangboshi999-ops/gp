<template>
  <div class="page">
    <van-nav-bar :title="$t('support')" left-arrow @click-left="$router.back()" />

    <div style="padding: 12px">
      <div class="card contact">
        <div class="row-between">
          <div>
            <div style="font-size: 16px; font-weight: 600">24時間カスタマーサポート</div>
            <div class="muted">support@sample-securities.jp</div>
          </div>
          <van-icon name="service-o" size="28" color="#2f6bff" />
        </div>
      </div>

      <van-tabs v-model:active="type" @change="load">
        <van-tab :title="$t('faq')" name="faq" />
        <van-tab :title="$t('notice')" name="notice" />
      </van-tabs>

      <div class="card" style="margin-top: 12px">
        <van-collapse v-model="activeNames">
          <van-collapse-item v-for="item in list" :key="item.id" :title="item.title" :name="item.id">
            {{ item.content }}
          </van-collapse-item>
        </van-collapse>
        <EmptyState v-if="!list.length" icon="service-o" />
      </div>
    </div>
  </div>
</template>

<script setup>
import EmptyState from '@/components/EmptyState.vue'
import { onMounted, ref } from 'vue'
import { api } from '@/api'

const type = ref('faq')
const list = ref([])
const activeNames = ref([])

async function load() {
  try {
    list.value = await api.supportList({ type: type.value })
  } catch (e) {
    list.value = []
  }
}

onMounted(load)
</script>

<style scoped>
.contact {
  background: linear-gradient(135deg, #eef3ff, #ffffff);
}
</style>
