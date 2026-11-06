<script setup lang="ts">
import { shiftHours } from '@/shared/lib'
import { plusBlueIcon as plusIcon } from '@/shared/ui'
import type { Shift, ShiftTemplate } from '../model/types'

defineProps<{
  templates: ShiftTemplate[]
  selectedCount: number
  editing: Shift | null
  customStart: string
  customEnd: string
  loading?: boolean
  flat?: boolean
}>()

const emit = defineEmits<{
  'apply-template': [template: ShiftTemplate]
  'apply-custom': []
  'custom-start': [value: string]
  'custom-end': [value: string]
  'edit-start': [value: string]
  'edit-end': [value: string]
  'save-edit': []
  'delete-edit': []
}>()

function onDragStart(event: DragEvent, templateId: number): void {
  event.dataTransfer?.setData('text/plain', `template:${templateId}`)
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'copy'
  }
}
</script>

<template>
  <aside class="panel" :class="{ flat }">
    <div v-if="loading" class="skeleton-panel" aria-hidden="true">
      <span class="skeleton title"></span>
      <span v-for="item in 3" :key="item" class="skeleton card"></span>
      <span class="skeleton title"></span>
      <span class="skeleton field"></span>
      <span class="skeleton field"></span>
    </div>
    <template v-else>
    <section v-if="editing" class="block">
      <h2>Смена</h2>
      <div class="times">
        <label>
          Начало
          <input
            id="edit-start"
            name="edit-start"
            :value="editing.starts_at"
            type="time"
            @input="emit('edit-start', ($event.target as HTMLInputElement).value)"
          />
        </label>
        <label>
          Конец
          <input
            id="edit-end"
            name="edit-end"
            :value="editing.ends_at"
            type="time"
            @input="emit('edit-end', ($event.target as HTMLInputElement).value)"
          />
        </label>
      </div>
      <button class="action" type="button" @click="emit('save-edit')">Сохранить время</button>
      <button class="danger" type="button" @click="emit('delete-edit')">Удалить смену</button>
    </section>

    <section class="block">
      <h2>Шаблоны смен</h2>
      <button
        v-for="template in templates"
        :key="template.id"
        class="template"
        type="button"
        draggable="true"
        :style="{ borderLeftColor: template.border_color }"
        @click="emit('apply-template', template)"
        @dragstart="onDragStart($event, template.id)"
      >
        <span>
          <strong>{{ template.name }}</strong>
          <span>{{ template.starts_at }} - {{ template.ends_at }} · {{ shiftHours(template.starts_at, template.ends_at) }}</span>
        </span>
        <span class="key">{{ template.hotkey }}</span>
      </button>
    </section>

    <section class="block">
      <h2>Своя смена</h2>
      <div class="times">
        <label>
          Начало
          <input id="custom-start" name="custom-start" :value="customStart" type="time" @input="emit('custom-start', ($event.target as HTMLInputElement).value)" />
        </label>
        <label>
          Конец
          <input id="custom-end" name="custom-end" :value="customEnd" type="time" @input="emit('custom-end', ($event.target as HTMLInputElement).value)" />
        </label>
      </div>
      <button class="action" type="button" :disabled="selectedCount === 0" @click="emit('apply-custom')">
        <img :src="plusIcon" alt="" width="24" height="24" />
        Поставить в выделенные
      </button>
    </section>
    </template>
  </aside>
</template>

<style scoped>
.panel {
  display: flex;
  flex-direction: column;
  gap: 24px;
  width: 335px;
  flex: none;
  padding: 16px;
  border-radius: 12px;
  background: #fff;
}

.panel.flat {
  width: auto;
  padding: 0;
  border-radius: 0;
  background: transparent;
}

.block {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

h2 {
  margin: 0;
  font-size: 16px;
  font-weight: 400;
  line-height: 24px;
}

.template {
  display: flex;
  gap: 8px;
  align-items: flex-start;
  width: 100%;
  padding: 8px 8px 8px 16px;
  border: 0;
  border-left: 3px solid;
  border-radius: 8px;
  background: #f2f3f7;
  cursor: grab;
  text-align: left;
}

.template strong,
.template span {
  display: block;
}

.template span span {
  font-size: 16px;
  line-height: 24px;
}

.key {
  margin-left: auto;
  padding: 2px 8px;
  border-radius: 4px;
  background: #fff;
  font-size: 12px;
  font-weight: 600;
  line-height: 16px;
}

.times {
  display: flex;
  gap: 8px;
}

label {
  display: flex;
  flex: 1;
  flex-direction: column;
  gap: 4px;
  color: #737378;
  font-size: 12px;
  line-height: 16px;
}

input {
  height: 48px;
  padding: 8px 16px;
  border: 0;
  border-radius: 8px;
  background: #f2f3f7;
  color: #737378;
  font-size: 16px;
}

input[type='time']::-webkit-calendar-picker-indicator {
  display: none;
  -webkit-appearance: none;
}

.action,
.danger {
  display: flex;
  gap: 4px;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 12px 16px;
  border: 0;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  line-height: 24px;
}

.action {
  background: #e8edfc;
  color: #1c4ae5;
}

.action:hover:not(:disabled) {
  background: #d2dbfa;
}

.action:active:not(:disabled) {
  background: #bbc9f7;
}

.action:disabled {
  background: #f2f3f7;
  color: #c1c1c3;
  opacity: 1;
  cursor: default;
}

.action:disabled img {
  opacity: 0.45;
}

.danger {
  background: #fbe5e5;
  color: #dc2626;
}

.skeleton-panel {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.skeleton-panel .title {
  width: 140px;
  height: 24px;
}

.skeleton-panel .card {
  height: 64px;
}

.skeleton-panel .field {
  height: 48px;
}
</style>
