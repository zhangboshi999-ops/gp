import { defineStore } from 'pinia'
import { api, getToken, setToken } from '@/api'

export const useUserStore = defineStore('user', {
  state: () => ({
    token: getToken(),
    info: null,
    loaded: false
  }),
  getters: {
    isLogin: (state) => !!state.token,
    kycStatus: (state) => (state.info ? state.info.kyc_status : 0),
    hasPayPwd: (state) => (state.info ? !!state.info.has_paypwd : false)
  },
  actions: {
    async login(data) {
      const result = await api.login(data)
      this.token = result.userinfo.token
      setToken(this.token)
      await this.fetchInfo()
      return result
    },
    async register(data) {
      const result = await api.register(data)
      this.token = result.userinfo.token
      setToken(this.token)
      await this.fetchInfo()
      return result
    },
    async fetchInfo() {
      if (!this.token) {
        return null
      }
      try {
        this.info = await api.userInfo()
      } catch (error) {
        // 后端不可用时使用默认的游客资料
        this.info = null
      }
      this.loaded = true
      return this.info
    },
    logout() {
      this.token = ''
      this.info = null
      setToken('')
    }
  }
})
