import { ref } from 'vue'

/**
 * 实时行情 WebSocket 客户端
 *
 * 链路：web ← ws://{host}/ws ← socket/server.js ← Redis stock:stream ← php think quote:tick
 * 未启动实时服务时静默降级，页面照常使用 HTTP 接口的数据。
 */
const WS_URL = (() => {
  const conf = import.meta.env.VITE_WS_URL
  if (conf) return conf
  const proto = location.protocol === 'https:' ? 'wss' : 'ws'
  return `${proto}://${location.host}/ws`
})()

const listeners = new Map() // code -> Set<fn>
const wanted = new Set() // 需要订阅的标的（重连后自动恢复）

let socket = null
let retry = 0
let reconnectTimer = null
let heartbeatTimer = null

export const connected = ref(false)

function send(payload) {
  if (socket && socket.readyState === WebSocket.OPEN) {
    socket.send(JSON.stringify(payload))
  }
}

function dispatch(message) {
  const list = listeners.get(message.code)
  if (!list) return
  list.forEach((fn) => {
    try {
      fn(message)
    } catch (e) {
      // 单个回调异常不影响其它订阅
    }
  })
}

function scheduleReconnect() {
  clearTimeout(reconnectTimer)
  const delay = Math.min(1000 * 2 ** retry, 15000)
  retry += 1
  reconnectTimer = setTimeout(connect, delay)
}

function connect() {
  if (socket && (socket.readyState === WebSocket.OPEN || socket.readyState === WebSocket.CONNECTING)) {
    return
  }
  let ws
  try {
    ws = new WebSocket(WS_URL)
  } catch (e) {
    scheduleReconnect()
    return
  }
  socket = ws

  ws.onopen = () => {
    connected.value = true
    retry = 0
    if (wanted.size) {
      send({ cmd: 'sub', codes: [...wanted] })
    }
    clearInterval(heartbeatTimer)
    heartbeatTimer = setInterval(() => send({ cmd: 'ping' }), 25000)
  }

  ws.onmessage = (event) => {
    let message
    try {
      message = JSON.parse(event.data)
    } catch (e) {
      return
    }
    if (message.code) {
      dispatch(message)
    }
  }

  ws.onclose = () => {
    connected.value = false
    socket = null
    clearInterval(heartbeatTimer)
    if (wanted.size) {
      scheduleReconnect()
    }
  }

  ws.onerror = () => {
    // onclose 会接管重连
  }
}

/**
 * 订阅某只标的的实时行情
 * @param {string} code
 * @param {(msg:{type:string,code:string,quote?:object,bar?:object,bars?:array}) => void} handler
 */
export function subscribe(code, handler) {
  if (!code) return
  if (!listeners.has(code)) {
    listeners.set(code, new Set())
  }
  listeners.get(code).add(handler)
  wanted.add(code)

  connect()
  send({ cmd: 'sub', codes: [code] })
}

/**
 * 取消订阅
 */
export function unsubscribe(code, handler) {
  const set = listeners.get(code)
  if (set && handler) {
    set.delete(handler)
  }
  if (!set || set.size === 0) {
    listeners.delete(code)
    wanted.delete(code)
    send({ cmd: 'unsub', codes: [code] })
  }
}

export default { subscribe, unsubscribe, connected }
