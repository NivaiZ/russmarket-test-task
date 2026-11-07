<script setup lang="ts">
import { closeIcon, searchIcon } from '@/shared/ui'

defineProps<{
  period: string
  loading: boolean
  mode: 'week' | 'month'
  phone: boolean
}>()

const search = defineModel<string>('search', { required: true })

const emit = defineEmits<{
  move: [step: number]
  mode: [mode: 'week' | 'month']
  'open-panel': []
}>()
</script>

<template>
  <div class="toolbar">
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
        <button type="button" aria-label="Предыдущий период" @click="emit('move', -1)">‹</button>
        <span>{{ period }}</span>
        <button type="button" aria-label="Следующий период" @click="emit('move', 1)">›</button>
      </template>
    </div>
    <button v-if="phone" class="templates" type="button" @click="emit('open-panel')">Шаблоны</button>
    <div class="tabs">
      <button type="button" :class="{ active: mode === 'month' }" @click="emit('mode', 'month')">
        Месяц
      </button>
      <button type="button" :class="{ active: mode === 'week' }" @click="emit('mode', 'week')">
        Неделя
      </button>
    </div>
  </div>
</template>

<style scoped>
.toolbar {
  display: flex;
  gap: 8px;
  align-items: center;
  margin-bottom: 16px;
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

.period-bone {
  width: 160px;
  height: 32px;
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

@media (max-width: 960px) {
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
}
</style>
