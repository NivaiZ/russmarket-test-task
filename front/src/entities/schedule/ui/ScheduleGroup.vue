<script setup lang="ts">
import { useId } from 'vue'
import type { Schedule } from '../model/types'
import { chevronDownIcon } from '@/shared/ui'
import ScheduleCard from './ScheduleCard.vue'

const panelId = useId()

defineProps<{
  title: string
  items: Schedule[]
  open: boolean
}>()

const emit = defineEmits<{
  toggle: []
}>()

const motion = 'height 0.2s cubic-bezier(0.22, 0.61, 0.36, 1)'

function reducedMotion(): boolean {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches
}

function onEnter(element: Element, done: () => void): void {
  const node = element as HTMLElement
  if (reducedMotion()) {
    done()
    return
  }

  node.style.height = '0px'
  node.style.overflow = 'hidden'
  const target = node.scrollHeight
  if (target === 0) {
    done()
    return
  }

  requestAnimationFrame(() => {
    node.style.transition = motion
    node.style.height = `${target}px`
    node.addEventListener('transitionend', () => done(), { once: true })
  })
}

function onAfterEnter(element: Element): void {
  const node = element as HTMLElement
  node.style.height = ''
  node.style.overflow = ''
  node.style.transition = ''
}

function onLeave(element: Element, done: () => void): void {
  const node = element as HTMLElement
  if (reducedMotion()) {
    done()
    return
  }

  node.style.height = `${node.scrollHeight}px`
  node.style.overflow = 'hidden'
  requestAnimationFrame(() => {
    node.style.transition = motion
    node.style.height = '0px'
    node.addEventListener('transitionend', () => done(), { once: true })
  })
}
</script>

<template>
  <section class="group" :aria-labelledby="`${panelId}-title`">
    <h2 :id="`${panelId}-title`" class="heading">
      <button
        class="header"
        type="button"
        :aria-expanded="open"
        :aria-controls="panelId"
        @click="emit('toggle')"
      >
        <span class="title">
          {{ title }}
          <span class="count">{{ items.length }}</span>
        </span>
        <span class="chevron">
          <img :class="{ open }" :src="chevronDownIcon" alt="" width="24" height="24" />
        </span>
      </button>
    </h2>
    <Transition :css="false" @enter="onEnter" @after-enter="onAfterEnter" @leave="onLeave">
      <div v-if="open" :id="panelId" class="panel">
        <p v-if="items.length === 0" class="empty">
          <strong>Ничего не найдено</strong>
          <span>В этом разделе пока нет графиков</span>
        </p>
        <ul v-else class="list">
          <li v-for="schedule in items" :key="schedule.id">
            <ScheduleCard :schedule="schedule" />
          </li>
        </ul>
      </div>
    </Transition>
  </section>
</template>

<style scoped>
.group {
  overflow: hidden;
  border-radius: 16px;
  background: #fff;
}

.heading {
  margin: 0;
  font: inherit;
}

.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 16px 24px;
  border: 0;
  background: #fff;
  font: inherit;
}

.title {
  display: flex;
  gap: 10px;
  align-items: center;
  font-size: 20px;
  font-weight: 600;
  line-height: 24px;
  letter-spacing: -0.3px;
}

.count {
  padding: 2px 8px;
  border-radius: 99px;
  background: #f2f3f7;
  font-size: 12px;
  font-weight: 400;
  line-height: 16px;
}

.chevron {
  display: flex;
  padding: 4px;
  border-radius: 30px;
  background: #f2f3f7;
}

.chevron img {
  transition: transform 0.2s cubic-bezier(0.22, 0.61, 0.36, 1);
}

.chevron img.open {
  transform: rotate(180deg);
}

@media (prefers-reduced-motion: reduce) {
  .chevron img {
    transition: none;
  }
}

.list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin: 0;
  padding: 4px 16px 16px;
  list-style: none;
}

.list li {
  display: flex;
}

@media (max-width: 767.98px) {
  .list li {
    width: 100%;
  }
}

.empty {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin: 0;
  padding: 4px 24px 20px;
}

.empty strong {
  font-size: 16px;
  font-weight: 600;
  line-height: 24px;
}

.empty span {
  color: #737378;
  font-size: 14px;
  line-height: 20px;
}
</style>
