export const CURRENCY_SYMBOL = {
  JPY: '¥',
  USD: '$',
  USDT: '₮'
}

export function symbol(currency) {
  return CURRENCY_SYMBOL[currency] || ''
}

/**
 * 金额格式化（千分位）
 */
export function money(value, currency = '', digits = 2) {
  const num = Number(value || 0)
  const text = num.toLocaleString('en-US', {
    minimumFractionDigits: digits,
    maximumFractionDigits: digits
  })
  return (currency ? symbol(currency) : '') + text
}

/**
 * 价格格式化：小数位根据价格大小自适应
 */
export function price(value, currency = '') {
  const num = Number(value || 0)
  const digits = num >= 1000 ? 0 : num >= 1 ? 2 : 4
  return money(num, currency, digits)
}

/**
 * 涨跌额（带正负号）
 */
export function signed(value, currency = '', digits = 2) {
  const num = Number(value || 0)
  const text = money(Math.abs(num), currency, digits)
  return (num > 0 ? '+' : num < 0 ? '-' : '') + text
}

export function rate(value) {
  const num = Number(value || 0)
  return (num > 0 ? '+' : '') + num.toFixed(2) + '%'
}

/**
 * 涨跌配色：涨红跌绿（日股/美股习惯相反，这里用涨绿跌红）
 */
export function trendClass(value) {
  const num = Number(value || 0)
  if (num > 0) {
    return 'up'
  }
  if (num < 0) {
    return 'down'
  }
  return 'flat'
}

export function dateTime(timestamp) {
  if (!timestamp) {
    return '-'
  }
  const date = new Date(Number(timestamp) * 1000)
  const pad = (n) => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}`
}

export function date(timestamp) {
  if (!timestamp) {
    return '-'
  }
  const d = new Date(Number(timestamp) * 1000)
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
}

export function volume(value) {
  const num = Number(value || 0)
  if (num >= 1e8) {
    return (num / 1e8).toFixed(2) + '億'
  }
  if (num >= 1e4) {
    return (num / 1e4).toFixed(2) + '万'
  }
  return String(num)
}
