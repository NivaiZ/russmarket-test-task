import { ref } from 'vue'

export type ToastTone = 'success' | 'error'

export type ToastItem = {
  id: number
  text: string
  tone: ToastTone
}

const items = ref<ToastItem[]>([])
const life = 3000
let nextId = 0

type Timer = {
  handle: number
  started: number
  remaining: number
}

const timers = new Map<number, Timer>()

function clearTimer(id: number): void {
  const timer = timers.get(id)
  if (!timer) {
    return
  }

  window.clearTimeout(timer.handle)
  timers.delete(id)
}

export function useToast() {
  function dismiss(id: number): void {
    clearTimer(id)
    items.value = items.value.filter((item) => item.id !== id)
  }

  function start(id: number, remaining: number): void {
    timers.set(id, {
      remaining,
      started: Date.now(),
      handle: window.setTimeout(() => dismiss(id), remaining),
    })
  }

  function push(text: string, tone: ToastTone = 'success'): void {
    const id = nextId + 1
    nextId = id
    items.value = [...items.value, { id, text, tone }]
    start(id, life)
  }

  function pause(id: number): void {
    const timer = timers.get(id)
    if (!timer) {
      return
    }

    const remaining = Math.max(timer.remaining - (Date.now() - timer.started), 0)
    window.clearTimeout(timer.handle)
    timers.set(id, { handle: 0, started: Date.now(), remaining })
  }

  function resume(id: number): void {
    const timer = timers.get(id)
    if (!timer || !items.value.some((item) => item.id === id)) {
      return
    }

    start(id, timer.remaining)
  }

  return { items, push, dismiss, pause, resume }
}
