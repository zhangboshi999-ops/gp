<template>
  <div class="page">
    <van-nav-bar :title="$t('my_orders')" left-arrow @click-left="$router.back()" />

    <van-tabs v-model:active="status" @change="load">
      <van-tab :title="$t('all')" name="" />
      <van-tab :title="$t('pending')" name="pending" />
      <van-tab :title="$t('success')" name="filled" />
      <van-tab :title="$t('cancel')" name="canceled" />
    </van-tabs>

    <div class="card" style="margin-top: 12px">
      <div v-for="item in list" :key="item.id" style="padding: 12px 0; border-bottom: 1px solid var(--border)">
        <div class="row-between">
          <div>
            <div style="font-weight: 600">
              {{ item.name }}
              <van-tag :type="item.side === 'buy' ? 'danger' : 'success'">
                {{ item.side === 'buy' ? $t('buy') : $t('sell') }}
              </van-tag>
            </div>
            <div class="muted">{{ item.code }} · {{ item.type === 'market' ? $t('market_order') : $t('limit_order') }}</div>
          </div>
          <div style="text-align: right">
            <div>{{ price(item.price, item.currency) }}</div>
            <div class="muted">{{ item.filled_quantity }}/{{ item.quantity }}</div>
          </div>
        </div>
        <div class="row-between" style="margin-top: 6px">
          <span class="muted">{{ item.order_no }}</span>
          <span>
            <van-tag :type="statusType(item.status)">{{ statusText(item.status) }}</van-tag>
            <van-button
              v-if="item.status === 'pending'"
              size="mini"
              plain
              style="margin-left: 8px"
              @click="cancel(item.id)"
            >
              {{ $t('cancel') }}
            </van-button>
          </span>
        </div>
      </div>
      <EmptyState v-if="!list.length" icon="orders-o" />
    </div>
  </div>
</template>

<script setup>
import EmptyState from '@/components/EmptyState.vue'
import { onMounted, ref } from 'vue'
import { showToast } from 'vant'
import { api } from '@/api'
import { price } from '@/utils/format'
import { t } from '@/locales'

const status = ref('')
const list = ref([])

const statusMap = {
  pending: { text: 'pending', type: 'warning' },
  partial: { text: 'pending', type: 'warning' },
  filled: { text: 'success', type: 'success' },
  canceled: { text: 'cancel', type: 'default' },
  rejected: { text: 'rejected', type: 'danger' }
}

function statusText(value) {
  return t((statusMap[value] || statusMap.pending).text)
}

function statusType(value) {
  return (statusMap[value] || statusMap.pending).type
}

async function load() {
  try {
    const data = await api.orders({ status: status.value })
    list.value = data.list || []
  } catch (e) {
    list.value = []
  }
}

async function cancel(id) {
  try {
    await api.orderCancel({ id })
    showToast(t('operation_success'))
    load()
  } catch (e) {
    showToast(e.message || t('network_error'))
  }
}

onMounted(load)
</script>
