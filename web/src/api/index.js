import axios from 'axios'
import { localeState, langPack } from '@/locales'
import { mockData } from './mock'

const USE_MOCK = import.meta.env.VITE_USE_MOCK === 'true'
const BASE_URL = import.meta.env.VITE_API_BASE || '/api'

// 后端语言标识：由 locales/messages/*.js 的 pack 决定，新增语言无需改动这里
function currentLang() {
  return langPack(localeState.locale)
}

const instance = axios.create({
  baseURL: BASE_URL,
  timeout: 15000
})

instance.interceptors.request.use((config) => {
  config.headers['X-Lang'] = currentLang()
  return config
})

export function getToken() {
  return localStorage.getItem('stock_token') || ''
}

export function setToken(token) {
  if (token) {
    localStorage.setItem('stock_token', token)
  } else {
    localStorage.removeItem('stock_token')
  }
}

/**
 * 统一请求：后端异常时自动回退演示数据
 */
export async function request(path, params = {}, method = 'get') {
  if (USE_MOCK) {
    return mockData(path, params) ?? {}
  }
  try {
    const config = {
      url: '/' + path,
      method,
      headers: { token: getToken() }
    }
    if (method === 'get') {
      config.params = params
    } else {
      config.data = params
    }
    const response = await instance.request(config)
    const body = response.data
    if (body && body.code === 1) {
      return body.data
    }
    const error = new Error((body && body.msg) || 'error')
    error.code = body ? body.code : 0
    throw error
  } catch (error) {
    const data = mockData(path, params)
    if (data !== undefined) {
      return data
    }
    throw error
  }
}

/**
 * 上传文件（实名认证图片等）
 */
export async function uploadFile(file) {
  const formData = new FormData()
  formData.append('file', file)
  const response = await instance.post('/common/upload', formData, {
    headers: { token: getToken(), 'Content-Type': 'multipart/form-data' }
  })
  if (response.data && response.data.code === 1) {
    return response.data.data.url
  }
  throw new Error((response.data && response.data.msg) || 'upload failed')
}

export const api = {
  // 站点
  langs: () => request('index/langs'),

  // 会员
  login: (data) => request('user/login', data, 'post'),
  register: (data) => request('user/register', data, 'post'),
  resetpwd: (data) => request('user/resetpwd', data, 'post'),
  logout: () => request('user/logout', {}, 'post'),
  userInfo: () => request('user/info'),
  verify: (data) => request('user/verify', data, 'post'),
  myVerify: () => request('user/myverify'),

  // 行情
  marketGroups: () => request('market/groups'),
  marketList: (params) => request('market/lists', params),
  marketHot: () => request('market/hot'),
  marketIndexes: () => request('market/indexes'),
  marketDetail: (params) => request('market/detail', params),
  kline: (params) => request('market/kline', params),

  // 交易
  orderCreate: (data) => request('trade/create', data, 'post'),
  orderCancel: (data) => request('trade/cancel', data, 'post'),
  orders: (params) => request('trade/orders', params),
  positions: () => request('trade/positions'),
  sell: (data) => request('trade/sell', data, 'post'),

  // 资金
  accounts: () => request('fund/accounts'),
  deposit: (data) => request('fund/deposit', data, 'post'),
  withdraw: (data) => request('fund/withdraw', data, 'post'),
  transfer: (data) => request('fund/transfer', data, 'post'),
  exchangeRate: () => request('fund/rate'),
  exchange: (data) => request('fund/exchange', data, 'post'),
  records: (params) => request('fund/records', params),
  banks: () => request('fund/banks'),
  addBank: (data) => request('fund/addbank', data, 'post'),
  delBank: (data) => request('fund/delbank', data, 'post'),
  payee: () => request('fund/payee'),
  wallets: () => request('fund/wallets'),
  addWallet: (data) => request('fund/addwallet', data, 'post'),
  paypwd: (data) => request('fund/paypwd', data, 'post'),

  // 跟单 / 自选
  traders: () => request('follow/traders'),
  follow: (data) => request('follow/follow', data, 'post'),
  unfollow: (data) => request('follow/unfollow', data, 'post'),
  myFollows: () => request('follow/myfollows'),
  favorites: () => request('follow/favorites'),
  toggleFavorite: (data) => request('follow/toggle', data, 'post'),

  // 新股
  ipoList: () => request('ipo/lists'),
  ipoDetail: (params) => request('ipo/detail', params),
  ipoApply: (data) => request('ipo/apply', data, 'post'),
  myApplies: () => request('ipo/myapplies'),

  // 客服
  supportList: (params) => request('support/lists', params)
}

export default api
