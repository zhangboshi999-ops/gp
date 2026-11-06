/**
 * 实时行情 WebSocket 服务
 *
 * 数据链路：
 *   php think quote:tick  --PUBLISH-->  Redis(stock:stream)  --SUBSCRIBE-->  本服务  --WS-->  浏览器
 *
 * 客户端协议（JSON）：
 *   下行  {"type":"welcome","ts":1690000000}
 *         {"type":"snapshot","code":"7203","quote":{...},"bar":{...},"bars":[...]}
 *         {"type":"bar","code":"7203","quote":{...},"bar":{...}}
 *         {"type":"pong","ts":...}
 *         {"type":"error","msg":"..."}
 *   上行  {"cmd":"sub","codes":["7203"]}
 *         {"cmd":"unsub","codes":["7203"]}
 *         {"cmd":"ping"}
 */
const http = require('http')
const { WebSocketServer } = require('ws')
const { createClient } = require('redis')

const PORT = Number(process.env.WS_PORT || 9502)
const REDIS_URL = process.env.REDIS_URL || 'redis://127.0.0.1:6379'
const CHANNEL = 'stock:stream'
const ACTIVE_KEY = 'stock:active'
const SNAPSHOT_LIMIT = 240

const client = createClient({ url: REDIS_URL })
const subscriber = client.duplicate()

client.on('error', (err) => console.error('[redis] error:', err.message))
subscriber.on('error', (err) => console.error('[redis-sub] error:', err.message))

const server = http.createServer((req, res) => {
  if (req.url === '/health') {
    res.writeHead(200, { 'Content-Type': 'application/json' })
    res.end(JSON.stringify({ ok: true, clients: wss.clients.size, ts: Date.now() }))
    return
  }
  res.writeHead(426, { 'Content-Type': 'text/plain; charset=utf-8' })
  res.end('WebSocket only: ws://' + (req.headers.host || ('127.0.0.1:' + PORT)) + '/ws\n')
})

const wss = new WebSocketServer({ server, path: '/ws' })

function send(ws, payload) {
  if (ws.readyState === ws.OPEN) {
    ws.send(JSON.stringify(payload))
  }
}

async function sendSnapshot(ws, code) {
  const [quoteRaw, barRaw, barsRaw] = await Promise.all([
    client.get(`stock:quote:${code}`),
    client.get(`stock:bar:${code}`),
    client.lRange(`stock:bars:${code}`, 0, SNAPSHOT_LIMIT - 1)
  ])
  send(ws, {
    type: 'snapshot',
    code,
    quote: quoteRaw ? JSON.parse(quoteRaw) : null,
    bar: barRaw ? JSON.parse(barRaw) : null,
    bars: barsRaw.map((item) => JSON.parse(item)).reverse()
  })
}

wss.on('connection', (ws) => {
  ws.isAlive = true
  ws.codes = new Set()

  ws.on('pong', () => {
    ws.isAlive = true
  })

  send(ws, { type: 'welcome', ts: Date.now() })

  ws.on('message', async (raw) => {
    let msg
    try {
      msg = JSON.parse(raw.toString())
    } catch (e) {
      send(ws, { type: 'error', msg: 'invalid json' })
      return
    }

    const codes = Array.isArray(msg.codes) ? msg.codes.filter(Boolean).map(String) : []

    if (msg.cmd === 'ping') {
      send(ws, { type: 'pong', ts: Date.now() })
      return
    }

    if (msg.cmd === 'sub') {
      for (const code of codes) {
        ws.codes.add(code)
        await client.sAdd(ACTIVE_KEY, code)
        await sendSnapshot(ws, code)
      }
      return
    }

    if (msg.cmd === 'unsub') {
      for (const code of codes) {
        ws.codes.delete(code)
        await client.sRem(ACTIVE_KEY, code)
      }
      return
    }

    send(ws, { type: 'error', msg: 'unknown cmd' })
  })

  ws.on('close', async () => {
    for (const code of ws.codes) {
      await client.sRem(ACTIVE_KEY, code).catch(() => {})
    }
  })
})

// 心跳：30 秒无响应则断开
const heartbeat = setInterval(() => {
  wss.clients.forEach((ws) => {
    if (ws.isAlive === false) {
      ws.terminate()
      return
    }
    ws.isAlive = false
    ws.ping()
  })
}, 30000)

async function main() {
  await client.connect()
  await subscriber.connect()
  await subscriber.subscribe(CHANNEL, (message) => {
    let payload
    try {
      payload = JSON.parse(message)
    } catch (e) {
      return
    }
    wss.clients.forEach((ws) => {
      if (ws.codes && ws.codes.has(payload.code)) {
        send(ws, payload)
      }
    })
  })

  server.listen(PORT, () => {
    console.log(`[socket] listening ws://127.0.0.1:${PORT}/ws  (redis ${REDIS_URL})`)
  })
}

async function shutdown() {
  console.log('\n[socket] shutting down...')
  clearInterval(heartbeat)
  wss.close()
  await subscriber.quit().catch(() => {})
  await client.quit().catch(() => {})
  process.exit(0)
}

process.on('SIGINT', shutdown)
process.on('SIGTERM', shutdown)

main().catch((err) => {
  console.error('[socket] 启动失败:', err.message)
  process.exit(1)
})
