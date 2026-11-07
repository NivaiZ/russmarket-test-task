<script setup lang="ts">
import { chevronLeftIcon, Sheet } from '@/shared/ui'
import { useShiftBoard } from '../model/useShiftBoard'
import BoardToolbar from './BoardToolbar.vue'
import EditorShiftPanel from './EditorShiftPanel.vue'
import SelectionBar from './SelectionBar.vue'
import ShiftGrid from './ShiftGrid.vue'

const {
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
} = useShiftBoard()
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
          <SelectionBar
            v-if="selected.length > 0"
            v-model:template-menu="templateMenu"
            :selected="selected"
            :templates="templates"
            :phone="isPhone"
            @apply="applyTemplate"
            @open-panel="panelOpen = true"
            @clear="clearSelected"
            @dismiss="dismissSelection"
          />
          <BoardToolbar
            v-else
            v-model:search="search"
            :period="period"
            :loading="loading"
            :mode="mode"
            :phone="isPhone"
            @move="movePeriod"
            @mode="mode = $event"
            @open-panel="panelOpen = true"
          />

          <div v-if="dayMenu && !isPhone" class="day-menu">
            <p>Назначить на {{ dayMenu }}</p>
            <button
              v-for="template in templates"
              :key="template.id"
              type="button"
              @click="assignDay(template)"
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

        <EditorShiftPanel
          v-model:open="panelOpen"
          :phone="isPhone"
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

      <Sheet v-if="isPhone" :model-value="dayMenu !== ''" @update:model-value="onDaySheet">
        <template #header>Назначить на {{ dayMenu }}</template>
        <ul class="sheet-options">
          <li v-for="template in templates" :key="template.id">
            <button type="button" @click="assignDay(template)">
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
