<script setup lang="ts">
import { computed } from 'vue'
import { actionClearIcon, actionDismissIcon, actionTemplateIcon, Sheet } from '@/shared/ui'
import type { ShiftTemplate } from '../model/types'

const props = defineProps<{
  selected: string[]
  templates: ShiftTemplate[]
  phone: boolean
}>()

const templateMenu = defineModel<boolean>('templateMenu', { default: false })

const emit = defineEmits<{
  apply: [template: ShiftTemplate]
  'open-panel': []
  clear: []
  dismiss: []
}>()

const employeeCount = computed(() => new Set(props.selected.map((key) => key.split('|')[0])).size)
const dayCount = computed(() => new Set(props.selected.map((key) => key.split('|')[1])).size)

function countLabel(count: number, one: string, few: string, many: string): string {
  const mod100 = count % 100
  const mod10 = count % 10
  const word =
    mod100 > 10 && mod100 < 20 ? many : mod10 === 1 ? one : mod10 > 1 && mod10 < 5 ? few : many
  return `${count} ${word}`
}
</script>

<template>
  <div class="selection">
    <div class="selection-meta">
      <span>Выделено:</span>
      <b>{{ countLabel(selected.length, 'ячейка', 'ячейки', 'ячеек') }}</b>
      <span>
        {{ countLabel(employeeCount, 'сотрудник', 'сотрудника', 'сотрудников') }}
        · {{ dayCount }} дн.
      </span>
    </div>
    <div class="selection-actions">
      <div class="menu-wrap">
        <button type="button" @click="templateMenu = !templateMenu">
          <img :src="actionTemplateIcon" alt="" width="16" height="16" />
          Применить шаблон
        </button>
        <div v-if="templateMenu && !phone" class="template-menu">
          <button v-for="template in templates" :key="template.id" type="button" @click="emit('apply', template)">
            {{ template.name }}
          </button>
        </div>
      </div>
      <button v-if="phone" type="button" @click="emit('open-panel')">Шаблоны</button>
      <button type="button" @click="emit('clear')">
        <img :src="actionClearIcon" alt="" width="16" height="16" />
        Очистить
      </button>
      <button type="button" @click="emit('dismiss')">
        <img :src="actionDismissIcon" alt="" width="16" height="16" />
        Снять
      </button>
    </div>
  </div>

  <Sheet v-if="phone" v-model="templateMenu">
    <template #header>Применить шаблон</template>
    <ul class="sheet-options">
      <li v-for="template in templates" :key="template.id">
        <button type="button" @click="emit('apply', template)">
          {{ template.name }}
        </button>
      </li>
    </ul>
  </Sheet>
</template>

<style scoped>
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
  color: #101012;
  font-size: 16px;
  line-height: 24px;
  text-align: left;
}

.sheet-options button:hover {
  background: #e7e8ee;
}
</style>
