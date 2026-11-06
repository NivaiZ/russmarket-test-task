<script setup lang="ts">
import type { Schedule } from '../model/types'
import { calendarIcon, markerIcon, usersIcon } from '@/shared/ui'

defineProps<{
  schedule: Schedule
}>()

function formatDate(value: string): string {
  const parts = value.split('-')
  const month = parts[1]
  const day = parts[2]
  if (!month || !day) {
    return value
  }

  return `${day}.${month}`
}

const statusLabel = {
  published: 'Опубликован',
  draft: 'Черновик',
} as const
</script>

<template>
  <RouterLink class="card" :to="{ name: 'schedule', params: { slug: schedule.slug } }">
    <div class="top">
      <span class="address">
        <img :src="markerIcon" alt="" width="16" height="16" />
        {{ schedule.address }}
      </span>
      <span class="badge" :class="schedule.status">{{ statusLabel[schedule.status] }}</span>
    </div>
    <h3 class="name">{{ schedule.name }}</h3>
    <div class="progress">
      <div class="progress-text">
        <span>Прогресс</span>
        <span>{{ schedule.progress }}%</span>
      </div>
      <div
        class="track"
        role="progressbar"
        :aria-valuenow="schedule.progress"
        aria-valuemin="0"
        aria-valuemax="100"
        :aria-label="`Прогресс заполнения ${schedule.progress}%`"
      >
        <div class="fill" :style="{ width: `${schedule.progress}%` }" />
      </div>
    </div>
    <div class="footer">
      <span>
        <img :src="usersIcon" alt="" width="16" height="16" />
        {{ schedule.employees_count }} чел.
      </span>
      <span>
        <img :src="calendarIcon" alt="" width="16" height="16" />
        {{ formatDate(schedule.on_date) }}
      </span>
    </div>
  </RouterLink>
</template>

<style scoped>
.card {
  display: flex;
  flex-direction: column;
  gap: 12px;
  width: 348px;
  height: 220px;
  padding: 16px;
  border-radius: 12px;
  background: #f8f9fb;
}

.card:hover,
.card:focus-visible {
  box-shadow: inset 0 0 0 1px #dedee3;
}

.top {
  display: flex;
  gap: 16px;
  align-items: flex-start;
}

.address {
  display: flex;
  flex: 1;
  gap: 4px;
  align-items: center;
  min-width: 0;
  font-size: 12px;
  line-height: 16px;
}

.badge {
  padding: 4px 8px 6px;
  border-radius: 9999px;
  color: #fff;
  font-size: 12px;
  font-weight: 600;
  line-height: 16px;
  white-space: nowrap;
}

.badge.published {
  background: #3e87ff;
}

.badge.draft {
  background: #242426;
}

.name {
  flex: 1;
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  line-height: 24px;
  letter-spacing: -0.3px;
}

.progress {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.progress-text {
  display: flex;
  justify-content: space-between;
  font-size: 16px;
  line-height: 24px;
}

.track {
  height: 4px;
  overflow: hidden;
  border-radius: 4px;
  background: #e6e6e9;
}

.fill {
  height: 100%;
  border-radius: 4px;
  background: #242426;
}

.footer {
  display: flex;
  justify-content: space-between;
  padding-top: 12px;
  color: #737378;
  font-size: 12px;
  line-height: 16px;
}

.footer span {
  display: flex;
  gap: 4px;
  align-items: center;
}

@media (max-width: 767.98px) {
  .card {
    width: 100%;
    height: auto;
  }
}
</style>
