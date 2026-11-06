const weekdays = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб']
const months = [
  'январь',
  'февраль',
  'март',
  'апрель',
  'май',
  'июнь',
  'июль',
  'август',
  'сентябрь',
  'октябрь',
  'ноябрь',
  'декабрь',
]

export function startOfDay(date: Date): Date {
  return new Date(date.getFullYear(), date.getMonth(), date.getDate())
}

export function addDays(date: Date, days: number): Date {
  const next = startOfDay(date)
  next.setDate(next.getDate() + days)
  return next
}

export function parseIso(value: string): Date {
  const [year, month, day] = value.split('-').map(Number)
  return new Date(year || 0, (month || 1) - 1, day || 1)
}

export function toIso(date: Date): string {
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${date.getFullYear()}-${month}-${day}`
}

export function weekdayLabel(date: Date): string {
  return weekdays[date.getDay()] ?? ''
}

export function weekDays(anchor: Date): Date[] {
  const start = startOfDay(anchor)
  const mondayOffset = (start.getDay() + 6) % 7
  const monday = addDays(start, -mondayOffset)
  return Array.from({ length: 7 }, (_, index) => addDays(monday, index))
}

export function monthDays(anchor: Date): Date[] {
  const start = new Date(anchor.getFullYear(), anchor.getMonth(), 1)
  const total = new Date(anchor.getFullYear(), anchor.getMonth() + 1, 0).getDate()
  return Array.from({ length: total }, (_, index) => addDays(start, index))
}

export function periodLabel(anchor: Date, mode: 'week' | 'month'): string {
  if (mode === 'month') {
    const month = months[anchor.getMonth()] ?? ''
    return `${month} ${anchor.getFullYear()}`
  }

  const days = weekDays(anchor)
  const first = days[0]
  const last = days[6]
  if (!first || !last) {
    return ''
  }

  return `${first.getDate()}–${last.getDate()} ${months[last.getMonth()] ?? ''}`
}

export function shiftHours(startsAt: string, endsAt: string): string {
  const start = timeToMinutes(startsAt)
  const end = timeToMinutes(endsAt)
  if (start === null || end === null) {
    return ''
  }

  let minutes = end - start
  if (minutes < 0) {
    minutes += 24 * 60
  }

  return `${Math.round(minutes / 60)} ч`
}

function timeToMinutes(value: string): number | null {
  const parts = value.split(':')
  const hours = Number(parts[0])
  const minutes = Number(parts[1])
  if (!Number.isInteger(hours) || !Number.isInteger(minutes)) {
    return null
  }

  return hours * 60 + minutes
}
