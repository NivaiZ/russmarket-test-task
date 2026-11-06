<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { fetchSchedule, updateScheduleStatus, type Schedule } from '@/entities/schedule'
import { addDays, monthDays, parseIso, periodLabel, startOfDay, toIso, useMediaQuery, weekDays } from '@/shared/lib'
import {
  actionClearIcon,
  actionDismissIcon,
  actionTemplateIcon,
  chevronLeftIcon,
  closeIcon,
  searchIcon,
  Sheet,
  useToast,
} from '@/shared/ui'
import { assignShifts, deleteShift, fetchEmployees, fetchShifts, fetchTemplates, moveShift, updateShiftTime } from '../api/board'
import type { Employee, Shift, ShiftTemplate } from '../model/types'
import ShiftGrid from './ShiftGrid.vue'
import ShiftPanel from './ShiftPanel.vue'

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

const days = computed(() => (mode.value === 'week' ? weekDays(cursor.value) : monthDays(cursor.value)))
const visibleEmployees = computed(() => {
  const needle = search.value.trim().toLowerCase()
  if (needle === '') {
    return employees.value
  }

  return employees.value.filter((employee) => employee.name.toLowerCase().includes(needle))
})
const period = computed(() => periodLabel(cursor.value, mode.value))
const selectedEmployeeCount = computed(
  () => new Set(selected.value.map((key) => key.split('|')[0])).size,
)
const selectedDayCount = computed(() => new Set(selected.value.map((key) => key.split('|')[1])).size)

function countLabel(count: number, one: string, few: string, many: string): string {
  const mod100 = count % 100
  const mod10 = count % 10
  const word =
    mod100 > 10 && mod100 < 20 ? many : mod10 === 1 ? one : mod10 > 1 && mod10 < 5 ? few : many
  return `${count} ${word}`
}

function cellKey(employeeId: number, date: string): string {
  return `${employeeId}|${date}`
}

function selectedCells() {
  return selected.value.map((key) => {
    const [employeeId, onDate] = key.split('|')
    return { employee_id: Number(employeeId), on_date: onDate ?? '' }
  })
}

async function loadBoard(): Promise<void> {
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
  } catch {
    loading.value = false
    push('Не удалось открыть график', 'error')
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

  shifts.value = await fetchShifts(schedule.value.id, toIso(first), toIso(last))
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
  } catch {
    push('Не удалось назначить смену', 'error')
  }
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
  } catch {
    push('Не удалось назначить смену', 'error')
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
  } catch {
    push('Не удалось перенести смену', 'error')
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
    const updated = await updateShiftTime(editing.value.id, editing.value.starts_at, editing.value.ends_at)
    mergeShifts([updated])
    editing.value = null
    closePhoneSheets()
    push('Время смены сохранено')
  } catch {
    push('Не удалось сохранить смену', 'error')
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
  } catch {
    push('Не удалось удалить смену', 'error')
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
  } catch {
    push('Не удалось сохранить график', 'error')
  } finally {
    saving.value = false
  }
}

async function clearSelected(): Promise<void> {
  const keys = new Set(selected.value)
  const targets = shifts.value.filter((shift) => keys.has(`${shift.employee_id}|${shift.on_date}`))
  if (targets.length === 0) {
    push('В выделенных ячейках нет смен', 'error')
    return
  }

  try {
    await Promise.all(targets.map((shift) => deleteShift(shift.id)))
    const ids = new Set(targets.map((shift) => shift.id))
    shifts.value = shifts.value.filter((shift) => !ids.has(shift.id))
    push(targets.length === 1 ? 'Смена удалена' : 'Смены удалены')
  } catch {
    push('Не удалось очистить смены', 'error')
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
  if (event.target instanceof HTMLInputElement) {
    return
  }

  const hotkey = Number(event.key)
  const template = templates.value.find((item) => item.hotkey === hotkey)
  if (template && selected.value.length > 0) {
    void applyTemplate(template)
  }
}

watch(days, () => {
  void loadShifts()
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

function onDaySheet(value: boolean): void {
  if (value === false) {
    dayMenu.value = ''
  }
}

onMounted(() => {
  window.addEventListener('keydown', onKeydown)
  void loadBoard()
})

onUnmounted(() => {
  window.removeEventListener('keydown', onKeydown)
})
</script>

<template>
  <div class="page">
      <header class="heading">
        <div>
          <RouterLink class="back" to="/schedules">
            <img :src="chevronLeftIcon" alt="" width="24" height="24" />
            Графики смен
          </RouterLink>
          <h1>{{ schedule?.name ?? 'График' }}</h1>
        </div>
        <div class="actions">
          <button class="publish" type="button" :disabled="saving || loading" @click="setStatus('published')">
            Опубликовать
          </button>
          <button class="save" type="button" :disabled="saving || loading" @click="setStatus('draft')">
            Сохранить
          </button>
        </div>
      </header>
      <p v-if="schedule" class="status">
        {{ schedule.status === 'published' ? 'Опубликован' : 'Черновик' }}
      </p>

      <div class="workspace">
        <section class="board">
          <div v-if="selected.length > 0" class="selection">
            <div class="selection-meta">
              <span>Выделено:</span>
              <b>{{ countLabel(selected.length, 'ячейка', 'ячейки', 'ячеек') }}</b>
              <span>
                {{ countLabel(selectedEmployeeCount, 'сотрудник', 'сотрудника', 'сотрудников') }}
                · {{ selectedDayCount }} дн.
              </span>
            </div>
            <div class="selection-actions">
              <div class="menu-wrap">
                <button type="button" @click="templateMenu = !templateMenu">
                  <img :src="actionTemplateIcon" alt="" width="16" height="16" />
                  Применить шаблон
                </button>
                <div v-if="templateMenu && !isPhone" class="template-menu">
                  <button
                    v-for="template in templates"
                    :key="template.id"
                    type="button"
                    @click="applyTemplate(template)"
                  >
                    {{ template.name }}
                  </button>
                </div>
              </div>
              <button v-if="isPhone" type="button" @click="panelOpen = true">Шаблоны</button>
              <button type="button" @click="clearSelected">
                <img :src="actionClearIcon" alt="" width="16" height="16" />
                Очистить
              </button>
              <button type="button" @click="dismissSelection">
                <img :src="actionDismissIcon" alt="" width="16" height="16" />
                Снять
              </button>
            </div>
          </div>
          <div v-else class="toolbar">
            <label class="search">
              <img :src="searchIcon" alt="" width="16" height="16" />
              <input
                id="employee-search"
                v-model="search"
                name="employee-search"
                type="search"
                placeholder="Поиск по ФИО"
                aria-label="Поиск по ФИО"
              />
              <button v-if="search" class="clear" type="button" aria-label="Очистить" @click.stop="search = ''">
                <img :src="closeIcon" alt="" width="24" height="24" />
              </button>
            </label>
            <div class="period">
              <template v-if="loading">
                <span class="skeleton period-bone"></span>
              </template>
              <template v-else>
                <button type="button" aria-label="Предыдущий период" @click="movePeriod(-1)">‹</button>
                <span>{{ period }}</span>
                <button type="button" aria-label="Следующий период" @click="movePeriod(1)">›</button>
              </template>
            </div>
            <button v-if="isPhone" class="templates" type="button" @click="panelOpen = true">Шаблоны</button>
            <div class="tabs">
              <button type="button" :class="{ active: mode === 'month' }" @click="mode = 'month'">
                Месяц
              </button>
              <button type="button" :class="{ active: mode === 'week' }" @click="mode = 'week'">
                Неделя
              </button>
            </div>
          </div>

          <div v-if="dayMenu && !isPhone" class="day-menu">
            <p>Назначить на {{ dayMenu }}</p>
            <button
              v-for="template in templates"
              :key="template.id"
              type="button"
              @click="
                applyTemplate(template, visibleEmployees.map((employee) => ({ employee_id: employee.id, on_date: dayMenu })))
              "
            >
              {{ template.name }}
            </button>
          </div>

          <div v-if="loading" class="skeleton-grid" aria-hidden="true">
            <div v-for="row in 6" :key="row" class="skeleton-row">
              <span class="skeleton person"></span>
              <span v-for="day in 7" :key="day" class="skeleton cell"></span>
            </div>
          </div>
          <div
            v-else-if="search.trim() !== '' && visibleEmployees.length === 0"
            class="empty-search"
            role="status"
          >
            <strong>Ничего не найдено</strong>
            <span>Измените запрос и повторите поиск</span>
          </div>
          <ShiftGrid
            v-else
            :employees="visibleEmployees"
            :days="days"
            :shifts="shifts"
            :selected="selected"
            :mode="mode"
            :drag-over-key="dragOverKey"
            @toggle-cell="toggleCell"
            @open-shift="openShift"
            @open-day="dayMenu = dayMenu === $event ? '' : $event"
            @drop="onDrop"
            @drag-over="dragOverKey = $event"
          />
        </section>

        <ShiftPanel
          v-if="!isPhone"
          :loading="loading"
          :templates="templates"
          :selected-count="selected.length"
          :editing="editing"
          :custom-start="customStart"
          :custom-end="customEnd"
          @apply-template="applyTemplate"
          @apply-custom="applyCustom"
          @custom-start="customStart = $event"
          @custom-end="customEnd = $event"
          @edit-start="editing && (editing.starts_at = $event)"
          @edit-end="editing && (editing.ends_at = $event)"
          @save-edit="saveEdit"
          @delete-edit="removeEdit"
        />
      </div>

      <Sheet v-if="isPhone" v-model="panelOpen">
        <template #header>Смена</template>
        <ShiftPanel
          flat
          :loading="loading"
          :templates="templates"
          :selected-count="selected.length"
          :editing="editing"
          :custom-start="customStart"
          :custom-end="customEnd"
          @apply-template="applyTemplate"
          @apply-custom="applyCustom"
          @custom-start="customStart = $event"
          @custom-end="customEnd = $event"
          @edit-start="editing && (editing.starts_at = $event)"
          @edit-end="editing && (editing.ends_at = $event)"
          @save-edit="saveEdit"
          @delete-edit="removeEdit"
        />
      </Sheet>

      <Sheet v-if="isPhone" v-model="templateMenu">
        <template #header>Применить шаблон</template>
        <ul class="sheet-options">
          <li v-for="template in templates" :key="template.id">
            <button type="button" @click="applyTemplate(template)">
              {{ template.name }}
            </button>
          </li>
        </ul>
      </Sheet>

      <Sheet v-if="isPhone" :model-value="dayMenu !== ''" @update:model-value="onDaySheet">
        <template #header>Назначить на {{ dayMenu }}</template>
        <ul class="sheet-options">
          <li v-for="template in templates" :key="template.id">
            <button
              type="button"
              @click="
                applyTemplate(template, visibleEmployees.map((employee) => ({ employee_id: employee.id, on_date: dayMenu })))
              "
            >
              {{ template.name }}
            </button>
          </li>
        </ul>
      </Sheet>
    </div>
</template>

<style scoped>
.page {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.heading {
  display: flex;
  gap: 24px;
  align-items: flex-end;
}

.back {
  display: inline-flex;
  gap: 12px;
  align-items: center;
  margin-bottom: 16px;
  font-size: 16px;
  line-height: 22px;
}

.back img {
  box-sizing: content-box;
  width: 24px;
  height: 24px;
  padding: 8px;
  border-radius: 999px;
  background: #fff;
}

h1 {
  margin: 0;
  font-size: 32px;
  font-weight: 500;
  line-height: 32px;
  letter-spacing: -1px;
}

.actions {
  display: flex;
  gap: 4px;
  margin-left: auto;
}

.publish,
.save {
  padding: 12px 16px;
  border: 0;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  line-height: 24px;
}

.publish {
  background: #e8edfc;
  color: #1c4ae5;
}

.publish:hover:not(:disabled) {
  background: #d2dbfa;
}

.publish:active:not(:disabled) {
  background: #bbc9f7;
}

.save {
  background: #5b7dec;
  color: #fff;
}

.save:hover:not(:disabled) {
  background: #1c4ae5;
}

.save:active:not(:disabled) {
  background: #163bb7;
}

.publish:disabled,
.save:disabled {
  background: #f2f3f7;
  opacity: 1;
  cursor: default;
}

.publish:disabled {
  color: #c1c1c3;
}

.save:disabled {
  color: #c6c6cd;
}

.status {
  margin: -8px 0 0;
  color: #737378;
}

.workspace {
  display: flex;
  gap: 8px;
  align-items: stretch;
}

.board {
  flex: 1;
  min-width: 0;
  padding: 12px;
  border-radius: 12px;
  background: #fff;
}

.toolbar {
  display: flex;
  gap: 8px;
  align-items: center;
  margin-bottom: 16px;
}

.empty-search {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  min-height: 240px;
  padding: 32px 16px;
  text-align: center;
}

.empty-search strong {
  font-size: 16px;
  font-weight: 600;
  line-height: 24px;
}

.empty-search span {
  color: #737378;
  font-size: 14px;
  line-height: 20px;
}

.selection {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: center;
  justify-content: space-between;
  min-height: 48px;
  margin-bottom: 16px;
  padding: 8px 12px;
  border-radius: 8px;
  background: #3c3c43;
  color: #fff;
  font-size: 14px;
  font-weight: 400;
  line-height: 20px;
}

.selection-meta,
.selection-actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.selection-meta {
  gap: 10px;
}

.selection b {
  padding: 2px 8px;
  border-radius: 99px;
  background: #595961;
  font-weight: 400;
}

.selection-actions button {
  display: inline-flex;
  gap: 4px;
  align-items: center;
  padding: 8px;
  border: 0;
  border-radius: 8px;
  background: transparent;
  color: #fff;
  font-size: 14px;
  line-height: 20px;
}

.selection-actions button:hover {
  background: #595961;
}

.menu-wrap {
  position: relative;
}

.template-menu {
  position: absolute;
  top: calc(100% + 4px);
  right: 0;
  z-index: 5;
  display: flex;
  flex-direction: column;
  min-width: 180px;
  padding: 4px;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 8px 24px rgb(16 16 18 / 12%);
}

.selection-actions .template-menu button {
  justify-content: flex-start;
  color: #101012;
}

.selection-actions .template-menu button:hover {
  background: #f2f3f7;
}

.search {
  display: flex;
  gap: 10px;
  align-items: center;
  width: 398px;
  height: 48px;
  padding: 12px;
  border: 1px solid transparent;
  border-radius: 12px;
  background: #f2f3f7;
}

.search:hover,
.search:focus-within {
  border-color: #bcbcc2;
}

.search input {
  flex: 1;
  min-width: 0;
  border: 0;
  outline: none;
  background: transparent;
  color: #101012;
  font-size: 16px;
}

.clear {
  display: flex;
  flex: none;
  margin-left: auto;
  padding: 0;
  border: 0;
  background: transparent;
}

.search input::placeholder {
  color: #737378;
}

.period {
  display: flex;
  gap: 8px;
  align-items: center;
  margin-left: auto;
  color: #101012;
}

.period button {
  width: 32px;
  height: 32px;
  border: 0;
  border-radius: 8px;
  background: #f2f3f7;
  font-size: 20px;
}

.period button:hover {
  background: #e7e8ee;
}

.period button:active {
  background: #cdcdd6;
}

.tabs {
  display: flex;
  width: 301px;
  height: 48px;
  padding: 2px;
  border-radius: 12px;
  background: #f2f3f7;
}

.tabs button {
  flex: 1;
  border: 0;
  border-radius: 10px;
  background: transparent;
  font-size: 16px;
  font-weight: 600;
}

.tabs button:hover:not(.active) {
  background: #e7e8ee;
}

.tabs button.active {
  background: #3c3c43;
  color: #fff;
}

.day-menu {
  display: flex;
  gap: 8px;
  align-items: center;
  margin-bottom: 8px;
}

.day-menu p {
  margin: 0;
}

.day-menu button {
  padding: 8px 12px;
  border: 0;
  border-radius: 8px;
  background: #f2f3f7;
}

.skeleton-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.skeleton-row {
  display: grid;
  grid-template-columns: 220px repeat(7, minmax(72px, 1fr));
  gap: 8px;
  align-items: center;
}

.skeleton-row .person {
  height: 40px;
}

.skeleton-row .cell {
  height: 64px;
}

.period-bone {
  width: 160px;
  height: 32px;
}

.templates {
  flex: none;
  height: 48px;
  padding: 0 16px;
  border: 0;
  border-radius: 12px;
  background: #e8edfc;
  color: #1c4ae5;
  font-size: 16px;
  font-weight: 600;
}

.templates:hover {
  background: #d2dbfa;
}

.templates:active {
  background: #bbc9f7;
}

.sheet-options {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.sheet-options button {
  width: 100%;
  padding: 12px;
  border: 0;
  border-radius: 8px;
  background: #f2f3f7;
  font-size: 16px;
  line-height: 24px;
  text-align: left;
}

.sheet-options button:hover {
  background: #e7e8ee;
}

@media (max-width: 960px) {
  .heading {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }

  .actions {
    margin-left: 0;
  }

  .publish,
  .save {
    flex: 1;
  }

  .toolbar {
    flex-wrap: wrap;
  }

  .search {
    width: 100%;
  }

  .period {
    margin-left: 0;
  }

  .tabs {
    width: auto;
    flex: 1;
    min-width: 180px;
  }

  .workspace {
    flex-direction: column;
  }
}

@media (max-width: 767.98px) {
  h1 {
    font-size: 28px;
  }
}
</style>
