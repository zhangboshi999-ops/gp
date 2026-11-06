<template>
  <div class="page">
    <van-nav-bar :title="$t('follow')" />

    <van-tabs v-model:active="active" @change="load">
      <van-tab :title="$t('trader')" name="traders" />
      <van-tab :title="$t('my_follow')" name="follows" />
      <van-tab :title="$t('my_favorite')" name="favorites" />
    </van-tabs>

    <!-- 交易员 -->
    <div v-if="active === 'traders'" style="padding: 12px">
      <div class="card" v-for="item in traders" :key="item.id">
        <div class="row-between">
          <div style="display: flex; align-items: center; gap: 10px">
            <van-image
              round
              width="44"
              height="44"
              :src="item.avatar || ''"
              error-icon="user-o"
            >
              <template #error>
                <div class="avatar-fallback">{{ item.name.slice(0, 1) }}</div>
              </template>
            </van-image>
            <div>
              <div style="font-weight: 600">{{ item.name }}</div>
              <div class="muted">{{ item.country }} · {{ $t('followers') }} {{ item.followers }}</div>
            </div>
          </div>
          <van-button size="small" type="primary" @click="openFollow(item)">{{ $t('follow') }}</van-button>
        </div>
        <div class="trader-stats">
          <div>
            <div class="up" style="font-size: 18px; font-weight: 700">{{ item.roi_rate }}%</div>
            <div class="muted">{{ $t('roi') }}</div>
          </div>
          <div>
            <div style="font-size: 18px; font-weight: 700">{{ item.win_rate }}%</div>
            <div class="muted">{{ $t('win_rate') }}</div>
          </div>
          <div>
            <div style="font-size: 18px; font-weight: 700">{{ money(item.total_profit) }}</div>
            <div class="muted">{{ $t('profit') }}</div>
          </div>
        </div>
        <div class="muted" style="margin-top: 8px">{{ item.intro }}</div>
      </div>
    </div>

    <!-- 我的跟单 -->
    <div v-else-if="active === 'follows'" style="padding: 12px">
      <div class="card">
        <div v-for="item in follows" :key="item.id" class="row-between" style="padding: 10px 0">
          <div>
            <div style="font-weight: 600">{{ item.trader_name }}</div>
            <div class="muted">{{ $t('amount') }} {{ money(item.amount) }} · {{ $t('profit') }} {{ money(item.profit) }}</div>
          </div>
          <van-button size="small" plain @click="unfollow(item.trader_id)">{{ $t('cancel') }}</van-button>
        </div>
        <EmptyState v-if="!follows.length" icon="friends-o" />
      </div>
    </div>

    <!-- 自选 -->
    <div v-else style="padding: 12px">
      <div class="card">
        <StockRow v-for="item in favorites" :key="item.id" :item="item" />
        <EmptyState v-if="!favorites.length" icon="friends-o" />
      </div>
    </div>

    <!-- 跟单金额 -->
    <van-dialog
      v-model:show="dialogShow"
      :title="$t('follow')"
      show-cancel-button
      @confirm="confirmFollow"
    >
      <div style="padding: 16px">
        <div class="muted">{{ current.name }} · {{ $t('roi') }} {{ current.roi_rate }}%</div>
        <van-field
          v-model="amount"
          type="number"
          :label="$t('amount')"
          :placeholder="`${$t('amount')} >= ${current.min_amount}`"
        />
      </div>
    </van-dialog>
  </div>
</template>

<script setup>
import EmptyState from '@/components/EmptyState.vue'
import { onMounted, ref } from 'vue'
import { showToast } from 'vant'
import StockRow from '@/components/StockRow.vue'
import { api } from '@/api'
import { money } from '@/utils/format'
import { t } from '@/locales'

const active = ref('traders')
const traders = ref([])
const follows = ref([])
const favorites = ref([])
const dialogShow = ref(false)
const amount = ref('')
const current = ref({})

async function load() {
  if (active.value === 'traders') {
    try {
      traders.value = await api.traders()
    } catch (e) {
      traders.value = []
    }
  } else if (active.value === 'follows') {
    try {
      follows.value = await api.myFollows()
    } catch (e) {
      follows.value = []
    }
  } else {
    try {
      favorites.value = await api.favorites()
    } catch (e) {
      favorites.value = []
    }
  }
}

function openFollow(item) {
  current.value = item
  amount.value = String(item.min_amount || 10000)
  dialogShow.value = true
}

async function confirmFollow() {
  try {
    await api.follow({ trader_id: current.value.id, amount: amount.value })
    showToast(t('operation_success'))
  } catch (e) {
    showToast(e.message || t('network_error'))
  }
}

async function unfollow(traderId) {
  try {
    await api.unfollow({ trader_id: traderId })
    showToast(t('operation_success'))
    load()
  } catch (e) {
    showToast(e.message || t('network_error'))
  }
}

onMounted(load)
</script>

<style scoped>
.trader-stats {
  display: flex;
  justify-content: space-around;
  text-align: center;
  margin-top: 12px;
  padding-top: 10px;
  border-top: 1px solid var(--border);
}

.avatar-fallback {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: #eef3ff;
  color: #2f6bff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}
</style>
