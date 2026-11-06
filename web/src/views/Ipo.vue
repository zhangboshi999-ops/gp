<template>
  <div class="page">
    <van-nav-bar :title="$t('ipo')" left-arrow @click-left="$router.back()" />

    <div style="padding: 12px">
      <div class="card">
        <div class="card-title">
          <span>{{ $t('ipo') }}</span>
          <span class="muted">{{ $t('issue_price') }}</span>
        </div>
        <div
          v-for="item in list"
          :key="item.id"
          class="row-between"
          style="padding: 12px 0; border-bottom: 1px solid var(--border)"
          @click="$router.push(`/ipo/${item.id}`)"
        >
          <div>
            <div style="font-weight: 600">{{ item.name }}</div>
            <div class="muted">
              {{ item.code }} · {{ $t('issue_price') }} {{ price(item.issue_price, 'JPY') }} ·
              {{ item.total_shares.toLocaleString() }}
            </div>
          </div>
          <van-tag :type="item.status === 'ongoing' ? 'danger' : item.status === 'listed' ? 'success' : 'default'">
            {{ statusText(item.status) }}
          </van-tag>
        </div>
        <EmptyState v-if="!list.length" icon="fire-o" />
      </div>

      <div class="card">
        <div class="card-title">{{ $t('ipo_apply') }}</div>
        <div v-for="item in applies" :key="item.id" class="row-between" style="padding: 8px 0">
          <div>
            <div>{{ item.name }}</div>
            <div class="muted">{{ item.quantity }} · {{ money(item.amount, 'JPY') }}</div>
          </div>
          <van-tag :type="item.status === 'won' ? 'success' : 'default'">{{ item.status }}</van-tag>
        </div>
        <EmptyState v-if="!applies.length" icon="fire-o" />
      </div>
    </div>
  </div>
</template>

<script setup>
import EmptyState from '@/components/EmptyState.vue'
import { onMounted, ref } from 'vue'
import { api } from '@/api'
import { money, price } from '@/utils/format'
import { t } from '@/locales'

const list = ref([])
const applies = ref([])

const statusMap = {
  upcoming: 'pending',
  ongoing: 'pending',
  closed: 'success',
  listed: 'success'
}

function statusText(value) {
  return t(statusMap[value] || 'pending')
}

async function load() {
  try {
    list.value = await api.ipoList()
  } catch (e) {
    list.value = []
  }
  try {
    applies.value = await api.myApplies()
  } catch (e) {
    applies.value = []
  }
}

onMounted(load)
</script>
