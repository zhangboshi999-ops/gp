<template>
  <div class="page">
    <van-nav-bar :title="side === 'buy' ? $t('buy') : $t('sell')" left-arrow @click-left="$router.back()" />

    <div style="padding: 12px">
      <div class="card">
        <div class="row-between">
          <div>
            <div style="font-size: 17px; font-weight: 600">{{ stock.name }}</div>
            <div class="muted">{{ stock.code }} · {{ stock.market }}</div>
          </div>
          <div style="text-align: right">
            <div style="font-size: 20px; font-weight: 700" :class="trendClass(stock.change_rate)">
              {{ price(stock.price, stock.currency) }}
            </div>
            <div class="muted">{{ rate(stock.change_rate) }}</div>
          </div>
        </div>
      </div>

      <div class="card">
        <van-tabs v-model:active="orderType" type="card">
          <van-tab :title="$t('market_order')" name="market" />
          <van-tab :title="$t('limit_order')" name="limit" />
        </van-tabs>

        <div v-if="orderType === 'limit'" style="margin-top: 12px">
          <van-field v-model="inputPrice" type="number" :label="$t('price')" :placeholder="$t('price')">
            <template #button>
              <van-button size="small" plain @click="inputPrice = String(stock.price)">最新</van-button>
            </template>
          </van-field>
          <div class="quick-price">
            <span v-for="item in priceSteps" :key="item" @click="inputPrice = item">{{ item }}</span>
          </div>
        </div>
        <div v-else class="muted" style="padding: 12px 0">
          {{ $t('market_order') }}：{{ price(stock.price, stock.currency) }}
        </div>

        <van-field :label="$t('quantity')" style="margin-top: 6px">
          <template #input>
            <van-stepper
              v-model="quantity"
              :step="lotSize"
              :min="0"
              :max="maxQuantity"
              integer
              input-width="76px"
              button-size="26px"
            />
          </template>
        </van-field>
        <div class="quick-price">
          <span v-for="item in quantitySteps" :key="item" @click="quantity = item">{{ item }}</span>
        </div>

        <van-cell-group inset style="margin: 0">
          <van-cell :title="$t('available')" :value="availableText" />
          <van-cell :title="$t('total')" :value="totalText" />
          <van-cell :title="$t('fee')" :value="feeText" />
        </van-cell-group>
      </div>
    </div>

    <div class="submit-bar">
      <van-button block :type="side === 'buy' ? 'danger' : 'primary'" :disabled="!quantity" @click="showConfirm = true">
        {{ $t('confirm_shares') }}
      </van-button>
    </div>

    <!-- 确认股份 -->
    <van-popup v-model:show="showConfirm" position="bottom" round safe-area-inset-bottom>
      <div style="padding: 18px">
        <h3 style="margin: 0 0 12px; text-align: center">{{ $t('confirm_shares') }}</h3>
        <van-cell :title="stock.name" :value="side === 'buy' ? $t('buy') : $t('sell')" />
        <van-cell :title="$t('price')" :value="price(dealPrice, stock.currency)" />
        <van-cell :title="$t('quantity')" :value="quantity + ' ' + $t('quantity')" />
        <van-cell :title="$t('total')" :value="totalText" />
        <van-cell :title="$t('fee')" :value="feeText" />

        <van-field
          v-model="payPassword"
          type="password"
          maxlength="6"
          :label="$t('pay_password')"
          :placeholder="$t('pay_password')"
        />

        <div style="display: flex; gap: 10px; margin-top: 14px">
          <van-button block plain @click="showConfirm = false">{{ $t('cancel') }}</van-button>
          <van-button block type="primary" :loading="loading" @click="submit">{{ $t('confirm') }}</van-button>
        </div>
      </div>
    </van-popup>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { showToast } from 'vant'
import { api } from '@/api'
import { useUserStore } from '@/store/user'
import { money, price, rate, trendClass } from '@/utils/format'
import { t } from '@/locales'

const route = useRoute()
const router = useRouter()
const user = useUserStore()
const code = route.params.code
const side = route.query.side === 'sell' ? 'sell' : 'buy'

const stock = ref({})
const orderType = ref('market')
const inputPrice = ref('')
const quantity = ref(0)
const payPassword = ref('')
const showConfirm = ref(false)
const loading = ref(false)
const holding = ref(0)

const lotSize = computed(() => Number(stock.value.lot_size || 1))
const dealPrice = computed(() =>
  orderType.value === 'market' ? Number(stock.value.price || 0) : Number(inputPrice.value || 0)
)
const total = computed(() => dealPrice.value * quantity.value)
const fee = computed(() => total.value * 0.0005)
const maxQuantity = computed(() => (side === 'sell' ? Math.max(holding.value, 0) : 100000))

const currency = computed(() => stock.value.currency || 'JPY')
const totalText = computed(() => money(total.value, currency.value))
const feeText = computed(() => money(fee.value, currency.value))

const priceSteps = computed(() => {
  const base = Number(stock.value.price || 0)
  const unit = base >= 1000 ? 10 : base >= 100 ? 1 : 0.1
  return [0.99, 0.995, 1, 1.005, 1.01].map((rate) => +(base * rate).toFixed(2)).map((v) => String(v))
})
const quantitySteps = computed(() => [lotSize.value, lotSize.value * 5, lotSize.value * 10, lotSize.value * 20])

const availableText = computed(() => {
  if (side === 'sell') {
    return `${holding.value} ${$t('quantity')}`
  }
  const account = (user.info?.accounts || []).find((item) => item.currency === currency.value)
  return money(account ? account.balance : 0, currency.value)
})

async function load() {
  if (user.isLogin && !user.info) {
    await user.fetchInfo()
  }
  try {
    stock.value = await api.marketDetail({ code })
    inputPrice.value = String(stock.value.price || '')
    holding.value = stock.value.holding || 0
    quantity.value = Number(stock.value.lot_size || 1)
  } catch (e) {
    showToast(e.message || t('network_error'))
  }
}

async function submit() {
  if (!quantity.value) {
    showToast(t('quantity'))
    return
  }
  if (payPassword.value.length !== 6) {
    showToast(t('pay_password'))
    return
  }
  loading.value = true
  try {
    const payload = {
      code,
      side,
      type: orderType.value,
      price: orderType.value === 'limit' ? inputPrice.value : '',
      quantity: quantity.value,
      pay_password: payPassword.value
    }
    const result = side === 'buy' ? await api.orderCreate(payload) : await api.sell({
      stock_id: stock.value.id,
      quantity: quantity.value,
      pay_password: payPassword.value
    })
    showConfirm.value = false
    showToast(t('operation_success'))
    router.push('/orders')
  } catch (e) {
    showToast(e.message || t('network_error'))
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.quick-price {
  display: flex;
  gap: 8px;
  margin: 8px 0 4px;
  flex-wrap: wrap;
}

.quick-price span {
  background: #f2f4f8;
  border-radius: 6px;
  padding: 4px 10px;
  font-size: 12px;
  color: #4a5160;
}
</style>
