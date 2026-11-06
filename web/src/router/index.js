import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  { path: '/', name: 'home', component: () => import('@/views/Home.vue'), meta: { tab: true, titleKey: 'home' } },
  { path: '/market', name: 'market', component: () => import('@/views/Market.vue'), meta: { tab: true, titleKey: 'market' } },
  { path: '/follow', name: 'follow', component: () => import('@/views/Follow.vue'), meta: { tab: true, titleKey: 'follow' } },
  { path: '/position', name: 'position', component: () => import('@/views/Position.vue'), meta: { tab: true, titleKey: 'position', auth: true } },
  { path: '/my', name: 'my', component: () => import('@/views/Profile.vue'), meta: { tab: true, titleKey: 'my' } },

  { path: '/login', name: 'login', component: () => import('@/views/Login.vue'), meta: { titleKey: 'login' } },
  { path: '/register', name: 'register', component: () => import('@/views/Register.vue'), meta: { titleKey: 'register' } },
  { path: '/forgot', name: 'forgot', component: () => import('@/views/Forgot.vue'), meta: { titleKey: 'forgot_password' } },

  { path: '/detail/:code', name: 'detail', component: () => import('@/views/StockDetail.vue'), meta: { titleKey: 'market' } },
  { path: '/buy/:code', name: 'buy', component: () => import('@/views/Buy.vue'), meta: { titleKey: 'buy', auth: true } },
  { path: '/orders', name: 'orders', component: () => import('@/views/Orders.vue'), meta: { titleKey: 'my_orders', auth: true } },
  { path: '/ipo', name: 'ipo', component: () => import('@/views/Ipo.vue'), meta: { titleKey: 'ipo' } },
  { path: '/ipo/:id', name: 'ipo-apply', component: () => import('@/views/IpoApply.vue'), meta: { titleKey: 'ipo_apply', auth: true } },

  { path: '/wallet/:currency', name: 'wallet', component: () => import('@/views/Wallet.vue'), meta: { titleKey: 'balance', auth: true } },
  { path: '/deposit', name: 'deposit', component: () => import('@/views/Deposit.vue'), meta: { titleKey: 'deposit', auth: true } },
  { path: '/withdraw-bank', name: 'withdraw-bank', component: () => import('@/views/WithdrawBank.vue'), meta: { titleKey: 'withdraw_bank', auth: true } },
  { path: '/withdraw-crypto', name: 'withdraw-crypto', component: () => import('@/views/WithdrawCrypto.vue'), meta: { titleKey: 'withdraw_crypto', auth: true } },
  { path: '/transfer', name: 'transfer', component: () => import('@/views/Transfer.vue'), meta: { titleKey: 'transfer', auth: true } },
  { path: '/records', name: 'records', component: () => import('@/views/Records.vue'), meta: { titleKey: 'records', auth: true } },

  { path: '/verify', name: 'verify', component: () => import('@/views/Verify.vue'), meta: { titleKey: 'verification', auth: true } },
  { path: '/paypwd', name: 'paypwd', component: () => import('@/views/PayPassword.vue'), meta: { titleKey: 'set_pay_password', auth: true } },
  { path: '/support', name: 'support', component: () => import('@/views/Support.vue'), meta: { titleKey: 'support' } },
  { path: '/settings', name: 'settings', component: () => import('@/views/Settings.vue'), meta: { titleKey: 'settings' } },
  { path: '/banks', name: 'banks', component: () => import('@/views/BankCards.vue'), meta: { titleKey: 'bank_card', auth: true } },
  { path: '/wallets', name: 'wallets', component: () => import('@/views/CryptoWallets.vue'), meta: { titleKey: 'crypto_wallet', auth: true } },

  { path: '/:pathMatch(.*)*', redirect: '/' }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to) => {
  const token = localStorage.getItem('stock_token')
  if (to.meta.auth && !token) {
    return { path: '/login', query: { redirect: to.fullPath } }
  }
  return true
})

export default router
