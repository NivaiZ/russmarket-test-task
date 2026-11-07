import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { fetchSchedule, updateScheduleStatus, type Schedule } from '@/entities/schedule'
import { addDays, monthDays, parseIso, periodLabel, startOfDay, toIso, useMediaQuery, weekDays } from '@/shared/lib'
import { ApiError, isAbortError } from '@/shared/api'
import { useToast } from '@/shared/ui'
import {
  assignShifts,
  clearShifts,
  deleteShift,
  fetchEmployees,
  fetchShifts,
  fetchTemplates,
  moveShift,
  updateShiftTime,
} from '../api/board'
import type { Employee, Shift, ShiftCell, ShiftTemplate } from './types'

export function useShiftBoard() {
  const route = useRoute()
  const router = useRouter()
  const schedule = ref<Schedule | null>(null)
  const employees = ref<Employee[]>([])
  const templates = ref<ShiftTemplate[]>([])
  const shifts = ref<Shift[]>([])
  const search = ref('')
  const mode = ref<'week' | 'month'>('week')
  const cursor = ref(startOfDay(new Date()))
  const selected = ref<string[]>([])
  const editing = ref<Shift | null>(null)
  const customStart = ref('09:00')
  const customEnd = ref('17:00')
  const dayMenu = ref('')
  const templateMenu = ref(false)
  const dragOverKey = ref('')
  const saving = ref(false)
  const loading = ref(true)
  const panelOpen = ref(false)
  const isPhone = useMediaQuery('(max-width: 767.98px)')
  const { push } = useToast()
  let shiftsAbort: AbortController | null = null
  let shiftsTicket = 0

  const days = computed(() => (mode.value === 'week' ? weekDays(cursor.value) : monthDays(cursor.value)))
  const visibleEmployees = computed(() => {
    const needle = search.value.trim().toLowerCase()
    if (needle === '') {
      return employees.value
    }

    return employees.value.filter((employee) => employee.name.toLowerCase().includes(needle))
  })
  const period = computed(() => periodLabel(cursor.value, mode.value))

  function fail(error: unknown, fallback: string): void {
    if (isAbortError(error)) {
      return
    }

    push(error instanceof ApiError ? error.message : fallback, 'error')
  }

  function cellKey(employeeId: number, date: string): string {
    return `${employeeId}|${date}`
  }

  function selectedCells(): ShiftCell[] {
    return selected.value.map((key) => {
      const [employeeId, onDate] = key.split('|')
      return { employee_id: Number(employeeId), on_date: onDate ?? '' }
    })
  }

  function cellsForDay(date: string): ShiftCell[] {
    return visibleEmployees.value.map((employee) => ({
      employee_id: employee.id,
      on_date: date,
    }))
  }

  async function loadBoard(): Promise<void> {
    loading.value = true
    const key = String(route.params.slug ?? '')
    if (key === '') {
      loading.value = false
      push('График не найден', 'error')
      return
    }

    try {
      const [loadedSchedule, loadedEmployees, loadedTemplates] = await Promise.all([
        fetchSchedule(key),
        fetchEmployees(),
        fetchTemplates(),
      ])
      if (loadedSchedule.slug !== key) {
        await router.replace({ name: 'schedule', params: { slug: loadedSchedule.slug } })
        return
      }

      cursor.value = parseIso(loadedSchedule.on_date)
      schedule.value = loadedSchedule
      employees.value = loadedEmployees
      templates.value = loadedTemplates
      await loadShifts()
      loading.value = false
    } catch (error) {
      loading.value = false
      fail(error, 'Не удалось открыть график')
    }
  }

  async function loadShifts(): Promise<void> {
    if (!schedule.value) {
      return
    }

    const first = days.value[0]
    const last = days.value[days.value.length - 1]
    if (!first || !last) {
      return
    }

    shiftsAbort?.abort()
    const controller = new AbortController()
    shiftsAbort = controller
    const ticket = ++shiftsTicket
    const scheduleId = schedule.value.id

    try {
      const loaded = await fetchShifts(scheduleId, toIso(first), toIso(last), controller.signal)
      if (ticket !== shiftsTicket) {
        return
      }

      shifts.value = loaded
    } catch (error) {
      if (isAbortError(error) || ticket !== shiftsTicket) {
        return
      }

      throw error
    }
  }

  function toggleCell(employeeId: number, date: string): void {
    editing.value = null
    dayMenu.value = ''
    const key = cellKey(employeeId, date)
    selected.value = selected.value.includes(key)
      ? selected.value.filter((item) => item !== key)
      : [...selected.value, key]
  }

  function openShift(shift: Shift): void {
    editing.value = { ...shift }
    dayMenu.value = ''
    if (isPhone.value) {
      panelOpen.value = true
    }
  }

  function closePhoneSheets(): void {
    if (!isPhone.value) {
      return
    }

    panelOpen.value = false
    templateMenu.value = false
    dayMenu.value = ''
  }

  async function applyTemplate(template: ShiftTemplate, cells = selectedCells()): Promise<void> {
    if (!schedule.value || cells.length === 0) {
      if (cells.length === 0) {
        push('Сначала выделите ячейки', 'error')
      }
      return
    }

    try {
      const created = await assignShifts(
        schedule.value.id,
        cells,
        template.starts_at,
        template.ends_at,
        template.id,
      )
      mergeShifts(created)
      selected.value = []
      dayMenu.value = ''
      templateMenu.value = false
      closePhoneSheets()
      push('Смена назначена')
    } catch (error) {
      fail(error, 'Не удалось назначить смену')
    }
  }

  function assignDay(template: ShiftTemplate): void {
    void applyTemplate(template, cellsForDay(dayMenu.value))
  }

  async function applyCustom(): Promise<void> {
    if (!schedule.value || selected.value.length === 0) {
      return
    }
    if (customStart.value === customEnd.value) {
      push('Время окончания должно отличаться от времени начала', 'error')
      return
    }

    try {
      const created = await assignShifts(
        schedule.value.id,
        selectedCells(),
        customStart.value,
        customEnd.value,
        null,
      )
      mergeShifts(created)
      selected.value = []
      templateMenu.value = false
      closePhoneSheets()
      push('Смена назначена')
    } catch (error) {
      fail(error, 'Не удалось назначить смену')
    }
  }

  function mergeShifts(created: Shift[]): void {
    const ids = new Set(created.map((shift) => `${shift.employee_id}|${shift.on_date}`))
    shifts.value = [
      ...shifts.value.filter((shift) => !ids.has(`${shift.employee_id}|${shift.on_date}`)),
      ...created,
    ]
  }

  async function onDrop(employeeId: number, date: string, payload: string): Promise<void> {
    const [kind, rawId] = payload.split(':')
    const id = Number(rawId)
    if (!schedule.value || !Number.isInteger(id)) {
      return
    }

    try {
      if (kind === 'template') {
        const template = templates.value.find((item) => item.id === id)
        if (template) {
          await applyTemplate(template, [{ employee_id: employeeId, on_date: date }])
        }
        return
      }

      if (kind === 'shift') {
        const moved = await moveShift(id, employeeId, date)
        shifts.value = shifts.value.filter((shift) => shift.id !== id)
        mergeShifts([moved])
        push('Смена перенесена')
      }
    } catch (error) {
      fail(error, 'Не удалось перенести смену')
    }
  }

  async function saveEdit(): Promise<void> {
    if (!editing.value) {
      return
    }
    if (editing.value.starts_at === editing.value.ends_at) {
      push('Время окончания должно отличаться от времени начала', 'error')
      return
    }

    try {
      const updated = await updateShiftTime(
        editing.value.id,
        editing.value.starts_at,
        editing.value.ends_at,
      )
      mergeShifts([updated])
      editing.value = null
      closePhoneSheets()
      push('Время смены сохранено')
    } catch (error) {
      fail(error, 'Не удалось сохранить смену')
    }
  }

  async function removeEdit(): Promise<void> {
    if (!editing.value) {
      return
    }

    try {
      await deleteShift(editing.value.id)
      shifts.value = shifts.value.filter((shift) => shift.id !== editing.value?.id)
      editing.value = null
      closePhoneSheets()
      push('Смена удалена')
    } catch (error) {
      fail(error, 'Не удалось удалить смену')
    }
  }

  async function setStatus(status: 'draft' | 'published'): Promise<void> {
    if (!schedule.value) {
      return
    }

    saving.value = true
    try {
      schedule.value = await updateScheduleStatus(schedule.value.id, status)
      push(status === 'published' ? 'График опубликован' : 'График сохранён')
    } catch (error) {
      fail(error, 'Не удалось сохранить график')
    } finally {
      saving.value = false
    }
  }

  async function clearSelected(): Promise<void> {
    if (!schedule.value) {
      return
    }

    const keys = new Set(selected.value)
    const targets = shifts.value.filter((shift) => keys.has(`${shift.employee_id}|${shift.on_date}`))
    if (targets.length === 0) {
      push('В выделенных ячейках нет смен', 'error')
      return
    }

    try {
      await clearShifts(
        schedule.value.id,
        targets.map((shift) => shift.id),
      )
      const ids = new Set(targets.map((shift) => shift.id))
      shifts.value = shifts.value.filter((shift) => !ids.has(shift.id))
      push(targets.length === 1 ? 'Смена удалена' : 'Смены удалены')
    } catch (error) {
      fail(error, 'Не удалось очистить смены')
    }
  }

  function dismissSelection(): void {
    selected.value = []
    templateMenu.value = false
  }

  function movePeriod(step: number): void {
    cursor.value =
      mode.value === 'week'
        ? addDays(cursor.value, step * 7)
        : new Date(cursor.value.getFullYear(), cursor.value.getMonth() + step, 1)
  }

  function onKeydown(event: KeyboardEvent): void {
    if (event.ctrlKey || event.altKey || event.metaKey) {
      return
    }

    const target = event.target
    if (
      target instanceof HTMLInputElement ||
      target instanceof HTMLTextAreaElement ||
      target instanceof HTMLSelectElement ||
      (target instanceof HTMLElement && target.isContentEditable)
    ) {
      return
    }

    const hotkey = Number(event.key)
    const template = templates.value.find((item) => item.hotkey === hotkey)
    if (template && selected.value.length > 0) {
      void applyTemplate(template)
    }
  }

  function onDaySheet(value: boolean): void {
    if (value === false) {
      dayMenu.value = ''
    }
  }

  watch(days, () => {
    if (!schedule.value || loading.value) {
      return
    }

    void loadShifts().catch((error: unknown) => {
      fail(error, 'Не удалось загрузить смены')
    })
  })

  watch(
    () => route.params.slug,
    () => {
      void loadBoard()
    },
  )

  watch(isPhone, (phone) => {
    if (!phone) {
      panelOpen.value = false
    }
  })

  onMounted(() => {
    window.addEventListener('keydown', onKeydown)
    void loadBoard()
  })

  onUnmounted(() => {
    shiftsAbort?.abort()
    window.removeEventListener('keydown', onKeydown)
  })

  return {
    schedule,
    templates,
    shifts,
    search,
    mode,
    selected,
    editing,
    customStart,
    customEnd,
    dayMenu,
    templateMenu,
    dragOverKey,
    saving,
    loading,
    panelOpen,
    isPhone,
    days,
    visibleEmployees,
    period,
    toggleCell,
    openShift,
    applyTemplate,
    assignDay,
    applyCustom,
    onDrop,
    saveEdit,
    removeEdit,
    setStatus,
    clearSelected,
    dismissSelection,
    movePeriod,
    onDaySheet,
  }
}
