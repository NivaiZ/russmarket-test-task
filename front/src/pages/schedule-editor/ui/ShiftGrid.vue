<script setup lang="ts">
import { shiftHours, toIso, weekdayLabel } from '@/shared/lib'
import { plusCellIcon } from '@/shared/ui'
import type { Employee, Shift } from '../model/types'

const props = defineProps<{
  employees: Employee[]
  days: Date[]
  shifts: Shift[]
  selected: string[]
  mode: 'week' | 'month'
  dragOverKey: string
}>()

const emit = defineEmits<{
  'toggle-cell': [employeeId: number, date: string]
  'open-shift': [shift: Shift]
  'open-day': [date: string]
  drop: [employeeId: number, date: string, payload: string]
  'drag-over': [key: string]
}>()

function cellKey(employeeId: number, date: string): string {
  return `${employeeId}|${date}`
}

function safeAvatar(avatar: string): string {
  return /^\/avatars\/[A-Za-z0-9._-]+$/.test(avatar) ? avatar : ''
}

function shiftAt(employeeId: number, date: Date): Shift | undefined {
  const iso = toIso(date)
  return props.shifts.find((shift) => shift.employee_id === employeeId && shift.on_date === iso)
}

function dayText(day: Date): string {
  return `${weekdayLabel(day)} ${day.getDate()}`
}

function coverageCount(day: Date): number {
  const iso = toIso(day)
  return props.shifts.filter((shift) => shift.on_date === iso).length
}

function hourLabel(value: string): string {
  const [hours = '0', minutes] = value.split(':')
  const hour = hours.padStart(2, '0')
  if (!minutes || minutes === '00') {
    return hour
  }

  return `${hour}:${minutes}`
}

function rangeLabel(startsAt: string, endsAt: string): string {
  return `${hourLabel(startsAt)} - ${hourLabel(endsAt)}`
}

function isDark(color: string): boolean {
  const hex = color.replace('#', '')
  if (!/^[0-9a-fA-F]{6}$/.test(hex)) {
    return false
  }

  const red = Number.parseInt(hex.slice(0, 2), 16)
  const green = Number.parseInt(hex.slice(2, 4), 16)
  const blue = Number.parseInt(hex.slice(4, 6), 16)
  return (red * 299 + green * 587 + blue * 114) / 1000 < 150
}

function readableAccent(color: string): string {
  const hex = color.replace('#', '')
  if (!/^[0-9a-fA-F]{6}$/.test(hex)) {
    return color
  }

  let red = Number.parseInt(hex.slice(0, 2), 16)
  let green = Number.parseInt(hex.slice(2, 4), 16)
  let blue = Number.parseInt(hex.slice(4, 6), 16)
  while ((red * 299 + green * 587 + blue * 114) / 1000 > 140) {
    red = Math.round(red * 0.72)
    green = Math.round(green * 0.72)
    blue = Math.round(blue * 0.72)
  }

  return `#${[red, green, blue].map((channel) => channel.toString(16).padStart(2, '0')).join('')}`
}

function chipStyle(shift: Shift): Record<string, string> {
  const dark = isDark(shift.background_color)
  return {
    background: shift.background_color,
    color: dark ? '#fff' : '#101012',
    '--chip-accent': dark ? '#fff' : readableAccent(shift.border_color),
  }
}

function onDragStart(event: DragEvent, payload: string): void {
  event.dataTransfer?.setData('text/plain', payload)
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'copyMove'
  }
}

function onDragLeave(event: DragEvent, key: string): void {
  const next = event.relatedTarget
  if (next instanceof Node && event.currentTarget instanceof Node && event.currentTarget.contains(next)) {
    return
  }

  if (props.dragOverKey === key) {
    emit('drag-over', '')
  }
}

function onDrop(event: DragEvent, employeeId: number, date: Date): void {
  event.preventDefault()
  const payload = event.dataTransfer?.getData('text/plain') ?? ''
  emit('drag-over', '')
  if (payload !== '') {
    emit('drop', employeeId, toIso(date), payload)
  }
}
</script>

<template>
  <div class="scroll" tabindex="0" role="region" aria-label="Таблица смен">
    <table class="grid" :style="{ '--days': days.length }">
      <caption class="sr-only">График смен по сотрудникам и дням</caption>
      <colgroup>
        <col class="name-col" />
        <col v-for="day in days" :key="toIso(day)" />
      </colgroup>
      <thead>
        <tr>
          <th class="corner" scope="col">Сотрудник</th>
          <th v-for="day in days" :key="toIso(day)" class="day-head" scope="col">
            <button
              class="day"
              type="button"
              :aria-label="`Назначить смену на ${dayText(day)}`"
              @click="emit('open-day', toIso(day))"
            >
              <template v-if="mode === 'month'">
                <span>{{ weekdayLabel(day) }}</span>
                <strong>{{ day.getDate() }}</strong>
              </template>
              <template v-else>{{ dayText(day) }}</template>
            </button>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="employee in employees" :key="employee.id">
          <th class="person" scope="row">
            <span class="person-inner">
              <img
                v-if="safeAvatar(employee.avatar)"
                class="avatar"
                :src="safeAvatar(employee.avatar)"
                alt=""
                width="40"
                height="40"
              />
              <span v-else class="avatar" aria-hidden="true"></span>
              <span class="identity">
                <span class="name">{{ employee.name }}</span>
                <span class="meta">{{ employee.role }} · {{ employee.weekly_hours }} ч</span>
              </span>
            </span>
          </th>
          <td
            v-for="day in days"
            :key="cellKey(employee.id, toIso(day))"
            class="cell"
            :class="{
              selected: selected.includes(cellKey(employee.id, toIso(day))),
              over: dragOverKey === cellKey(employee.id, toIso(day)),
            }"
            @dragover.prevent="emit('drag-over', cellKey(employee.id, toIso(day)))"
            @dragleave="onDragLeave($event, cellKey(employee.id, toIso(day)))"
            @drop="onDrop($event, employee.id, day)"
          >
            <button
              v-if="shiftAt(employee.id, day)"
              class="chip"
              :class="{ month: mode === 'month' }"
              type="button"
              draggable="true"
              :style="chipStyle(shiftAt(employee.id, day)!)"
              :aria-label="`Смена ${employee.name}, ${shiftAt(employee.id, day)?.starts_at}–${shiftAt(employee.id, day)?.ends_at}`"
              @click="emit('open-shift', shiftAt(employee.id, day)!)"
              @dragstart="onDragStart($event, `shift:${shiftAt(employee.id, day)?.id}`)"
            >
              <span v-if="mode === 'week'" class="chip-time">
                {{ rangeLabel(shiftAt(employee.id, day)!.starts_at, shiftAt(employee.id, day)!.ends_at) }}
              </span>
              <span class="chip-hours">
                {{ shiftHours(shiftAt(employee.id, day)!.starts_at, shiftAt(employee.id, day)!.ends_at) }}
              </span>
            </button>
            <button
              v-else
              class="empty"
              type="button"
              :aria-pressed="selected.includes(cellKey(employee.id, toIso(day)))"
              :aria-label="`Выбрать ячейку, ${employee.name}, ${dayText(day)}`"
              @click="emit('toggle-cell', employee.id, toIso(day))"
            >
              <img
                v-if="selected.includes(cellKey(employee.id, toIso(day)))"
                :src="plusCellIcon"
                alt=""
                width="20"
                height="20"
              />
            </button>
          </td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <th class="coverage-label" scope="row">Покрытие</th>
          <td
            v-for="day in days"
            :key="`coverage-${toIso(day)}`"
            class="coverage"
            :class="{ filled: coverageCount(day) > 0 }"
            :aria-label="`Покрытие, ${dayText(day)}: ${coverageCount(day)}`"
          >
            {{ coverageCount(day) }}
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
</template>

<style scoped>
.scroll {
  overflow-x: scroll;
  scrollbar-gutter: stable;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  margin: -1px;
  padding: 0;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.grid {
  width: 100%;
  min-width: calc(220px + var(--days) * 72px);
  border-collapse: separate;
  border-spacing: 0;
  table-layout: fixed;
}

.name-col {
  width: 220px;
}

.corner,
.day,
.coverage-label,
.person,
.cell,
.coverage {
  font-weight: inherit;
  vertical-align: middle;
}

.corner,
.day,
.coverage-label {
  padding: 12px;
  border-bottom: 1px solid #dedee3;
  color: #737378;
  font-size: 16px;
  line-height: 24px;
  text-align: center;
}

.corner,
.coverage-label {
  text-align: left;
}

.day-head {
  padding: 0;
  border-bottom: 1px solid #dedee3;
}

.day {
  width: 100%;
  min-width: 72px;
  border: 0;
  background: transparent;
  color: inherit;
  font: inherit;
}

.day span,
.day strong {
  display: block;
  font-weight: 400;
}

.person {
  min-height: 72px;
  padding: 12px 12px 12px 8px;
  text-align: left;
}

.person-inner {
  display: flex;
  gap: 8px;
  align-items: center;
  min-width: 0;
}

.avatar {
  flex: none;
  width: 40px;
  height: 40px;
  object-fit: cover;
  border: 0.5px solid #f2f3f7;
  border-radius: 4px;
  background: #e7e8ee;
}

.identity {
  min-width: 0;
  flex: 1;
}

.name,
.meta {
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.name {
  font-size: 16px;
  line-height: 24px;
}

.meta {
  color: #737378;
  font-size: 12px;
  line-height: 16px;
}

.cell {
  min-width: 72px;
  height: 72px;
  padding: 4px;
}

.cell.selected .empty,
.cell.over .empty {
  border-style: solid;
  border-color: #5b7dec;
  background: #ebeffd;
}

.empty {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 64px;
  border: 1px dashed #dedee3;
  border-radius: 8px;
  background: transparent;
}

.chip {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 64px;
  padding: 4px;
  border: 0;
  border-radius: 8px;
  cursor: grab;
}

.chip-time {
  font-size: 16px;
  line-height: 24px;
}

.chip-hours {
  color: var(--chip-accent);
  font-size: 12px;
  line-height: 16px;
}

.chip.month .chip-hours {
  font-size: 16px;
  font-weight: 600;
  line-height: 24px;
}

.coverage-label,
.coverage {
  border-top: 1px solid #dedee3;
  border-bottom: 0;
}

.coverage {
  padding: 12px;
  background: #fbe5e5;
  color: #dc2626;
  font-size: 16px;
  line-height: 24px;
  text-align: center;
}

.coverage.filled {
  background: #e7eefc;
  color: #1c4ae5;
}

@media (max-width: 767.98px) {
  .grid {
    min-width: calc(168px + var(--days) * 56px);
  }

  .name-col {
    width: 168px;
  }

  .day,
  .cell {
    min-width: 56px;
  }

  .avatar {
    width: 32px;
    height: 32px;
  }
}
</style>
