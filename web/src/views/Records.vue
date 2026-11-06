<template>
  <div class="page">
    <van-nav-bar :title="$t('records')" left-arrow @click-left="$router.back()" />

    <van-tabs v-model:active="type" @change="load">
      <van-tab :title="$t('all')" name="" />
      <van-tab :title="$t('deposit')" name="deposit" />
      <van-tab :title="$t('withdraw')" name="withdraw" />
      <van-tab :title="$t('transfer')" name="transfer" />
    </van-tabs>

    <div class="card" style="margin-top: 12px">
      <div v-for="item in list" :key="item.id" class="row-between" style="padding: 10px 0; border-bottom: 1px solid var(--border)">
        <div>
          <div style="font-weight: 600">
            {{ typeText(item.type) }}
            <span class="muted" style="font-weight: normal">· {{ item.method }}</span>
          </div>
          <div class="muted">{{ dateTime(item.createtime) }} · {{ item.sn }}</div>
        </div>
        <div style="text-align: right">
          <div :class="item.type === 'withdraw' ? 'down' : 'up'">
            {{ item.type === 'withdraw' ? '-' : '+' }}{{ money(item.amount, item.currency) }}
          </div>
          <van-tag :type="statusType(item.status)">{{ statusText(item.status) }}</van-tag>
        </div>
      </div>
      <EmptyState v-if="!list.length" icon="todo-list-o" />
    </div>
  </div>
</template>

<script setup>
import EmptyState from '@/components/EmptyState.vue'
import { onMounted, ref } from 'vue'
import { api } from '@/api'
import { dateTime, money } from '@/utils/format'
import { t } from '@/locales'

const type = ref('')
const list = ref([])

const statusMap = {
  pending: { text: 'pending', tag: 'warning' },
  success: { text: 'success', tag: 'success' },
  rejected: { text: 'rejected', tag: 'danger' }
}

function typeText(value) {
  return t(
    value === 'deposit'
      ? 'deposit'
      : value === 'withdraw'
        ? 'withdraw'
        : value === 'exchange'
          ? 'exchange'
          : 'transfer'
  )
}

function statusText(value) {
  return t((statusMap[value] || statusMap.pending).text)
}

function statusType(value) {
  return (statusMap[value] || statusMap.pending).tag
}

async function load() {
  try {
    const data = await api.records({ type: type.value })
    list.value = data.list || []
  } catch (e) {
    list.value = []
  }
}

onMounted(load)
</script>
