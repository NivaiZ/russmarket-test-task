<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import {
  ScheduleGroup,
  createSchedule,
  fetchSchedules,
  type Schedule,
  type ScheduleStatus,
} from '@/entities/schedule'
import { useMediaQuery } from '@/shared/lib'
import { chevronDownIcon, closeIcon, plusIcon, searchFieldIcon, Sheet, useToast } from '@/shared/ui'

const router = useRouter()
const search = ref('')
const status = ref<ScheduleStatus | ''>('')
const statusOpen = ref(false)
const statusRoot = ref<HTMLElement | null>(null)
const schedules = ref<Schedule[]>([])
const publishedOpen = ref(true)
const draftOpen = ref(false)
const creating = ref(false)
const loading = ref(true)
const isPhone = useMediaQuery('(max-width: 767.98px)')
const { push } = useToast()

const statusOptions: { value: ScheduleStatus | ''; label: string }[] = [
  { value: '', label: 'Все' },
  { value: 'published', label: 'Опубликован' },
  { value: 'draft', label: 'Черновик' },
]

const statusLabel = computed(
  () => statusOptions.find((option) => option.value === status.value)?.label ?? 'Статус',
)

const published = computed(() => schedules.value.filter((item) => item.status === 'published'))
const drafts = computed(() => schedules.value.filter((item) => item.status === 'draft'))
const noSearchResults = computed(() => {
  if (search.value.trim() === '') {
    return false
  }

  const publishedEmpty = status.value === 'draft' || published.value.length === 0
  const draftsEmpty = status.value === 'published' || drafts.value.length === 0
  return publishedEmpty && draftsEmpty
})

async function load(): Promise<void> {
  try {
    schedules.value = await fetchSchedules(search.value, status.value)
    if (search.value.trim() !== '') {
      publishedOpen.value = true
      draftOpen.value = true
    }
  } catch {
    push('Не удалось загрузить графики', 'error')
  } finally {
    loading.value = false
  }
}

function chooseStatus(value: ScheduleStatus | ''): void {
  status.value = value
  statusOpen.value = false
}

function onDocumentClick(event: MouseEvent): void {
  if (isPhone.value) {
    return
  }

  if (!statusRoot.value?.contains(event.target as Node)) {
    statusOpen.value = false
  }
}

function onDocumentKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape') {
    statusOpen.value = false
  }
}

async function create(): Promise<void> {
  creating.value = true
  try {
    const schedule = await createSchedule('Новый график')
    push('График создан')
    await router.push({ name: 'schedule', params: { slug: schedule.slug } })
  } catch {
    push('Не удалось создать график', 'error')
    creating.value = false
  }
}

watch([search, status], () => {
  void load()
})

onMounted(() => {
  document.addEventListener('click', onDocumentClick)
  document.addEventListener('keydown', onDocumentKeydown)
  void load()
})

onUnmounted(() => {
  document.removeEventListener('click', onDocumentClick)
  document.removeEventListener('keydown', onDocumentKeydown)
})
</script>

<template>
  <header class="heading">
      <div>
        <h1>Все графики</h1>
        <p>Создавайте, редактируйте и публикуйте графики по точкам региона</p>
      </div>
      <button class="create" type="button" :disabled="creating" @click="create">
        <img :src="plusIcon" alt="" width="24" height="24" />
        Создать график
      </button>
    </header>

    <div class="filters">
      <label class="search">
        <img :src="searchFieldIcon" alt="" width="24" height="24" />
        <input id="schedule-search" v-model="search" name="schedule-search" type="search" placeholder="Поиск по названию" aria-label="Поиск по названию" />
        <button v-if="search" class="clear" type="button" aria-label="Очистить" @click.stop="search = ''">
          <img :src="closeIcon" alt="" width="24" height="24" />
        </button>
      </label>
      <div ref="statusRoot" class="select">
        <button
          class="select-button"
          type="button"
          :aria-expanded="statusOpen"
          aria-controls="schedule-status-menu"
          @click="statusOpen = !statusOpen"
        >
          {{ status === '' ? 'Статус' : statusLabel }}
          <img :class="{ open: statusOpen }" :src="chevronDownIcon" alt="" width="24" height="24" />
        </button>
        <ul v-if="statusOpen && !isPhone" id="schedule-status-menu" class="menu">
          <li v-for="option in statusOptions" :key="option.label">
            <button type="button" @click="chooseStatus(option.value)">
              {{ option.label }}
            </button>
          </li>
        </ul>
      </div>
    </div>

    <div v-if="loading && schedules.length === 0" class="groups" aria-hidden="true">
      <div v-for="card in 2" :key="card" class="skeleton-card">
        <span class="skeleton line"></span>
        <span class="skeleton line short"></span>
      </div>
    </div>
    <div v-else-if="noSearchResults" class="empty-search" role="status">
      <strong>Ничего не найдено</strong>
      <span>Измените запрос и повторите поиск</span>
    </div>
    <div v-else class="groups">
      <ScheduleGroup
        v-if="status !== 'draft'"
        title="Опубликован"
        :items="published"
        :open="publishedOpen"
        @toggle="publishedOpen = !publishedOpen"
      />
      <ScheduleGroup
        v-if="status !== 'published'"
        title="Черновик"
        :items="drafts"
        :open="draftOpen"
        @toggle="draftOpen = !draftOpen"
      />
    </div>

    <Sheet v-if="isPhone" v-model="statusOpen">
      <template #header>Статус</template>
      <ul class="sheet-options">
        <li v-for="option in statusOptions" :key="option.label">
          <button
            type="button"
            :class="{ current: option.value === status }"
            :aria-current="option.value === status ? 'true' : undefined"
            @click="chooseStatus(option.value)"
          >
            {{ option.label }}
          </button>
        </li>
      </ul>
    </Sheet>
</template>

<style scoped>
.heading {
  display: flex;
  gap: 40px;
  align-items: center;
}

.heading h1 {
  margin: 0;
  font-size: 32px;
  font-weight: 500;
  line-height: 32px;
  letter-spacing: -1px;
}

.heading p {
  margin: 4px 0 0;
  color: #737378;
  font-size: 16px;
  line-height: 24px;
}

.create {
  display: flex;
  flex: none;
  gap: 4px;
  align-items: center;
  margin-left: auto;
  padding: 12px 16px;
  border: 0;
  border-radius: 8px;
  background: #5b7dec;
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  line-height: 24px;
}

.create:hover:not(:disabled) {
  background: #1c4ae5;
}

.create:active:not(:disabled) {
  background: #163bb7;
}

.create:disabled {
  background: #f2f3f7;
  color: #c6c6cd;
  opacity: 1;
  cursor: default;
}

.create:disabled img {
  opacity: 0.45;
}

.filters {
  display: flex;
  gap: 8px;
  margin-top: 24px;
}

.empty-search {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-top: 24px;
  padding: 32px;
  border-radius: 16px;
  background: #fff;
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

.search {
  display: flex;
  gap: 10px;
  align-items: center;
  width: 398px;
  height: 48px;
  padding: 12px;
  border: 1px solid transparent;
  border-radius: 12px;
  background: #fff;
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
  line-height: 1.4;
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

.select {
  position: relative;
}

.select-button {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 160px;
  height: 48px;
  padding: 8px 12px;
  border: 0;
  border-radius: 8px;
  background: #fff;
  font-size: 16px;
  line-height: 1.4;
}

.select-button img {
  transition: transform 0.2s cubic-bezier(0.22, 0.61, 0.36, 1);
}

.select-button img.open {
  transform: rotate(180deg);
}

@media (prefers-reduced-motion: reduce) {
  .select-button img {
    transition: none;
  }
}

.menu {
  position: absolute;
  z-index: 2;
  top: 50px;
  left: 0;
  display: flex;
  flex-direction: column;
  width: 160px;
  margin: 0;
  padding: 0;
  overflow: hidden;
  list-style: none;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 8px 24px rgb(16 16 18 / 12%);
}

.menu button {
  width: 100%;
}

.menu button {
  padding: 10px 12px;
  border: 0;
  background: #fff;
  text-align: left;
}

.menu button:hover {
  background: #f2f3f7;
}

.groups {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 8px;
}

.skeleton-card {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 24px;
  border-radius: 16px;
  background: #fff;
}

.skeleton-card .line {
  width: 240px;
  height: 24px;
}

.skeleton-card .short {
  width: 140px;
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
  background: transparent;
  font-size: 16px;
  line-height: 24px;
  text-align: left;
}

.sheet-options button.current,
.sheet-options button:hover {
  background: #f2f3f7;
}

@media (max-width: 767.98px) {
  .heading {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }

  .heading h1 {
    font-size: 28px;
  }

  .create {
    justify-content: center;
    margin-left: 0;
  }

  .filters {
    flex-direction: column;
  }

  .search,
  .select,
  .select-button {
    width: 100%;
  }
}

</style>
