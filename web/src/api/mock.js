// 演示数据：后端接口不可用（未启动/网络异常）时前端仍能完整浏览
const seedStocks = [
  { id: 1, code: '7203', name: 'トヨタ自動車', market: 'TSE', type: 'stock', currency: 'JPY', price: 3850, lot_size: 100, is_hot: 1 },
  { id: 2, code: '6758', name: 'ソニーグループ', market: 'TSE', type: 'stock', currency: 'JPY', price: 3450, lot_size: 100, is_hot: 1 },
  { id: 3, code: '9984', name: 'ソフトバンクグループ', market: 'TSE', type: 'stock', currency: 'JPY', price: 11200, lot_size: 100, is_hot: 1 },
  { id: 4, code: '8306', name: '三菱UFJフィナンシャル', market: 'TSE', type: 'stock', currency: 'JPY', price: 1980, lot_size: 100, is_hot: 1 },
  { id: 5, code: '6501', name: '日立製作所', market: 'TSE', type: 'stock', currency: 'JPY', price: 3980, lot_size: 100, is_hot: 1 },
  { id: 6, code: '9432', name: '日本電信電話', market: 'TSE', type: 'stock', currency: 'JPY', price: 148, lot_size: 100, is_hot: 1 },
  { id: 7, code: '6861', name: 'キーエンス', market: 'TSE', type: 'stock', currency: 'JPY', price: 68500, lot_size: 100, is_hot: 0 },
  { id: 8, code: '4063', name: '信越化学工業', market: 'TSE', type: 'stock', currency: 'JPY', price: 4920, lot_size: 100, is_hot: 0 },
  { id: 9, code: '8035', name: '東京エレクトロン', market: 'TSE', type: 'stock', currency: 'JPY', price: 23500, lot_size: 100, is_hot: 0 },
  { id: 10, code: '6098', name: 'リクルートHD', market: 'TSE', type: 'stock', currency: 'JPY', price: 9100, lot_size: 100, is_hot: 0 },
  { id: 11, code: '7974', name: '任天堂', market: 'TSE', type: 'stock', currency: 'JPY', price: 11200, lot_size: 100, is_hot: 0 },
  { id: 12, code: '4568', name: '第一三共', market: 'TSE', type: 'stock', currency: 'JPY', price: 4380, lot_size: 100, is_hot: 0 },
  { id: 13, code: 'AAPL', name: 'Apple Inc.', market: 'NASDAQ', type: 'stock', currency: 'USD', price: 228.5, lot_size: 1, is_hot: 1 },
  { id: 14, code: 'TSLA', name: 'Tesla, Inc.', market: 'NASDAQ', type: 'stock', currency: 'USD', price: 341.2, lot_size: 1, is_hot: 0 },
  { id: 15, code: 'NVDA', name: 'NVIDIA Corporation', market: 'NASDAQ', type: 'stock', currency: 'USD', price: 178.9, lot_size: 1, is_hot: 1 },
  { id: 16, code: 'MSFT', name: 'Microsoft Corporation', market: 'NASDAQ', type: 'stock', currency: 'USD', price: 428.6, lot_size: 1, is_hot: 0 },
  { id: 17, code: 'AMZN', name: 'Amazon.com, Inc.', market: 'NASDAQ', type: 'stock', currency: 'USD', price: 205.4, lot_size: 1, is_hot: 0 },
  { id: 18, code: 'BTC', name: 'Bitcoin', market: 'CRYPTO', type: 'crypto', currency: 'USD', price: 68420.5, lot_size: 1, is_hot: 1 },
  { id: 19, code: 'ETH', name: 'Ethereum', market: 'CRYPTO', type: 'crypto', currency: 'USD', price: 3245.8, lot_size: 1, is_hot: 1 }
]

const sectorNames = ['自動車', '電機', '情報通信', '金融', '素材', '小売', '医薬品', '暗号資産']

function decorate(stock, index = 0) {
  const seed = stock.code.charCodeAt(0) + stock.code.length + index
  const rate = (((seed * 37) % 500) - 250) / 100
  const prevClose = +(stock.price / (1 + rate / 100)).toFixed(4)
  return {
    ...stock,
    prev_close: prevClose,
    open: +(prevClose * (1 + rate / 400)).toFixed(4),
    high: +(Math.max(stock.price, prevClose) * 1.006).toFixed(4),
    low: +(Math.min(stock.price, prevClose) * 0.994).toFixed(4),
    change_amount: +(stock.price - prevClose).toFixed(4),
    change_rate: +rate.toFixed(2),
    volume: (seed * 971) % 9000000 + 120000,
    turnover: +(stock.price * ((seed * 971) % 9000000 + 120000)).toFixed(2),
    market_cap: +(stock.price * ((seed * 7919) % 9000000 + 500000)).toFixed(2),
    pe_ratio: +(((seed * 7) % 300) / 10 + 8).toFixed(2),
    pb_ratio: +(((seed * 3) % 50) / 10 + 0.6).toFixed(2),
    dividend_yield: +(((seed * 11) % 45) / 10).toFixed(2),
    turnover_rate: +(((seed * 13) % 60) / 10 + 0.3).toFixed(2),
    amplitude: +(((seed * 17) % 50) / 10 + 0.5).toFixed(2),
    high_52w: +(stock.price * 1.32).toFixed(4),
    low_52w: +(stock.price * 0.68).toFixed(4),
    sector: sectorNames[seed % sectorNames.length],
    source: 'demo',
    favorite: 0
  }
}

function buildKline(code, limit = 90) {
  const stock = seedStocks.find((item) => item.code === code)
  const base = stock ? stock.price : 1000
  const rows = []
  let close = base * 0.86
  const now = Date.now()
  for (let i = limit; i >= 1; i--) {
    const open = close
    const seed = Math.sin(i * (code.length + 3)) * 0.02
    close = open * (1 + seed + (Math.random() - 0.5) * 0.02)
    rows.push({
      time: Math.floor((now - i * 86400000) / 1000),
      date: new Date(now - i * 86400000).toISOString().slice(0, 10),
      open: +open.toFixed(4),
      high: +(Math.max(open, close) * (1 + Math.random() * 0.01)).toFixed(4),
      low: +(Math.min(open, close) * (1 - Math.random() * 0.01)).toFixed(4),
      close: +close.toFixed(4),
      volume: Math.floor(50000 + Math.random() * 3000000)
    })
  }
  if (rows.length) {
    rows[rows.length - 1].close = base
  }
  return rows
}

const mockPositions = [
  { id: 1, user_id: 0, stock_id: 1, code: '7203', name: 'トヨタ自動車', currency: 'JPY', quantity: 200, available: 200, cost_price: 3780, current_price: 3850, market_value: 770000, cost_amount: 756000, profit: 14000, profit_rate: 1.85 },
  { id: 2, user_id: 0, stock_id: 13, code: 'AAPL', name: 'Apple Inc.', currency: 'USD', quantity: 50, available: 50, cost_price: 210.4, current_price: 228.5, market_value: 11425, cost_amount: 10520, profit: 905, profit_rate: 8.6 },
  { id: 3, user_id: 0, stock_id: 18, code: 'BTC', name: 'Bitcoin', currency: 'USD', quantity: 1, available: 1, cost_price: 61000, current_price: 68420.5, market_value: 68420.5, cost_amount: 61000, profit: 7420.5, profit_rate: 12.16 }
]

const mockTraders = [
  { id: 1, name: '佐藤 健一', avatar: '', country: '日本', roi_rate: 38.62, win_rate: 71.5, followers: 12480, total_profit: 1286500, min_amount: 50000, intro: '大型株を中心とした長期投資が得意です。' },
  { id: 2, name: '鈴木 美咲', avatar: '', country: '日本', roi_rate: 24.18, win_rate: 66.2, followers: 8630, total_profit: 742300, min_amount: 30000, intro: '半導体セクターに強みがあります。' },
  { id: 3, name: 'Michael Chen', avatar: '', country: '美国', roi_rate: 52.74, win_rate: 74.8, followers: 23170, total_profit: 3260800, min_amount: 100000, intro: 'Focus on US tech stocks.' },
  { id: 4, name: '田中 雄大', avatar: '', country: '日本', roi_rate: 15.36, win_rate: 61.4, followers: 4120, total_profit: 286400, min_amount: 10000, intro: '中小型成長株を発掘します。' }
]

const mockIpos = [
  { id: 1, code: '5580', name: 'クラウドテック株式会社', market: 'TSE', issue_price: 980, total_shares: 1200000, min_quantity: 100, max_quantity: 5000, status: 'ongoing', intro: 'クラウドテック株式会社の新規上場に伴う公募・売出しです。' },
  { id: 2, code: '4071', name: 'グリーンエナジー株式会社', market: 'TSE', issue_price: 2300, total_shares: 800000, min_quantity: 100, max_quantity: 3000, status: 'ongoing', intro: '再生可能エネルギー事業を展開する企業です。' },
  { id: 3, code: '9212', name: 'ネクストモビリティ株式会社', market: 'TSE', issue_price: 1500, total_shares: 2000000, min_quantity: 100, max_quantity: 8000, status: 'upcoming', intro: '次世代モビリティサービスを提供します。' }
]

const mockSupports = [
  { id: 1, type: 'faq', title: '入金はどのくらいで反映されますか？', content: '銀行振込は営業日30分以内、暗号資産はネットワーク承認後に反映されます。', lang: 'ja' },
  { id: 2, type: 'faq', title: '出金の手数料はいくらですか？', content: '銀行出金は一律330円、暗号資産はネットワーク手数料のみです。', lang: 'ja' },
  { id: 3, type: 'notice', title: 'システムメンテナンスのお知らせ', content: '毎週日曜 03:00-04:00 はシステムメンテナンスのため取引を停止します。', lang: 'ja' },
  { id: 4, type: 'notice', title: '新規上場銘柄の取扱い開始', content: 'クラウドテック株式会社の新規公開株式の取扱いを開始しました。', lang: 'ja' }
]

const mockIndexes = [
  { id: 101, code: 'N225', name: '日経平均', currency: 'JPY', price: 39850.2, change_amount: 285.4, change_rate: 0.72, volume: 0 },
  { id: 102, code: 'TPX', name: 'TOPIX', currency: 'JPY', price: 2746.8, change_amount: -6.3, change_rate: -0.23, volume: 0 },
  { id: 103, code: 'SPX', name: 'S&P500', currency: 'USD', price: 5821.4, change_amount: 12.6, change_rate: 0.22, volume: 0 },
  { id: 104, code: 'NDX', name: 'NASDAQ100', currency: 'USD', price: 20364.5, change_amount: -48.2, change_rate: -0.24, volume: 0 },
  { id: 105, code: 'DJI', name: 'NYダウ', currency: 'USD', price: 42380.1, change_amount: 96.7, change_rate: 0.23, volume: 0 }
]

const mockAccounts = [
  { currency: 'JPY', balance: 862400, frozen: 0, total: 862400, total_deposit: 1200000, total_withdraw: 200000 },
  { currency: 'USD', balance: 5240.5, frozen: 0, total: 5240.5, total_deposit: 8000, total_withdraw: 1200 },
  { currency: 'USDT', balance: 980.25, frozen: 0, total: 980.25, total_deposit: 1000, total_withdraw: 0 }
]

export function mockData(path, params = {}) {
  switch (path) {
    case 'market/lists': {
      let list = seedStocks.map(decorate)
      if (params.market) {
        list = list.filter((item) => item.market === params.market)
      }
      if (params.type) {
        list = list.filter((item) => item.type === params.type)
      }
      if (params.keyword) {
        const keyword = String(params.keyword).toLowerCase()
        list = list.filter((item) => item.code.toLowerCase().includes(keyword) || item.name.toLowerCase().includes(keyword))
      }
      if (params.sort === 'gainers') {
        list.sort((a, b) => b.change_rate - a.change_rate)
      } else if (params.sort === 'losers') {
        list.sort((a, b) => a.change_rate - b.change_rate)
      } else if (params.sort === 'turnover') {
        list.sort((a, b) => b.turnover - a.turnover)
      }
      return { total: list.length, page: 1, limit: list.length, list }
    }
    case 'market/indexes':
      return mockIndexes.map((item) => ({ ...item, type: 'index', lot_size: 1 }))
    case 'market/hot':
      return seedStocks.filter((item) => item.is_hot).map(decorate)
    case 'market/groups':
      return [
        { key: 'all', name: 'すべて' },
        { key: 'TSE', name: '東証' },
        { key: 'NASDAQ', name: '米国株' },
        { key: 'CRYPTO', name: '暗号資産' }
      ]
    case 'market/detail': {
      const index = seedStocks.findIndex((item) => item.code === params.code)
      if (index < 0) {
        return null
      }
      return { ...decorate(seedStocks[index], index), holding: 0 }
    }
    case 'market/kline':
      return buildKline(params.code, params.limit || 90)
    case 'trade/positions':
      return mockPositions
    case 'trade/orders':
      return { total: 0, list: [] }
    case 'follow/traders':
      return mockTraders
    case 'follow/myfollows':
      return []
    case 'follow/favorites':
      return seedStocks.slice(0, 4).map(decorate)
    case 'ipo/lists':
      return mockIpos
    case 'ipo/myapplies':
      return []
    case 'support/lists':
      return mockSupports.filter((item) => !params.type || item.type === params.type)
    case 'fund/accounts':
      return mockAccounts
    case 'fund/records':
      return { total: 0, list: [] }
    case 'fund/banks':
      return []
    case 'fund/wallets':
      return []
    case 'user/info':
      return {
        id: 0,
        username: 'demo',
        nickname: 'Demo User',
        avatar: '',
        kyc_status: 0,
        has_paypwd: 0,
        accounts: mockAccounts,
        total_asset: 1864320,
        total_balance: 1134520,
        market_value: 729800
      }
    default:
      return undefined
  }
}
