<script setup lang="ts">
import { ref } from 'vue'
import {
  actionClearIcon,
  actionDismissIcon,
  actionEditIcon,
  actionTemplateIcon,
  avatarGradient,
  closeIcon,
  plusCellIcon,
  searchFieldIcon,
} from '@/shared/ui'

const tabs = [0, 1, 2, 3, 4, 5]
const selectedTab = ref(0)
const grayQuery = ref('')
const whiteQuery = ref('')

const neutralKinds = ['primary', 'secondary', 'tertiary', 'quaternary', 'quinary'] as const
const brandKinds = ['primary', 'secondary', 'tertiary'] as const
const states = ['default', 'hover', 'active', 'disable'] as const

const shifts = [
  { tone: 'cornflower', time: '08 - 18', hours: '10 ч' },
  { tone: 'mint', time: '08 - 18', hours: '10 ч' },
  { tone: 'lavanda', time: '08 - 18', hours: '10 ч' },
  { tone: 'brand', time: '08 - 18', hours: '10 ч' },
]

const templates = [
  { tone: 'cornflower', key: '1' },
  { tone: 'mint', key: '2' },
  { tone: 'lavanda', key: '4' },
]
</script>

<template>
  <div class="kit">
    <header class="intro">
      <h1>UI Kit</h1>
      <p>Вкладки, кнопки, ячейки графика, поиск и бейджи.</p>
    </header>

    <section class="card">
      <h2>Вкладки</h2>
      <div class="tabs" role="tablist">
        <button
          v-for="tab in tabs"
          :key="tab"
          class="tab"
          :class="{ on: selectedTab === tab }"
          type="button"
          role="tab"
          :aria-selected="selectedTab === tab"
          @click="selectedTab = tab"
        >
          Название
        </button>
      </div>
      <div class="state-grid">
        <span class="caption">Primary</span>
        <span class="tab primary">Название</span>
        <span class="tab primary hover">Название</span>
        <span class="tab primary on">Название</span>
        <span class="caption">Secondary</span>
        <span class="tab secondary">Название</span>
        <span class="tab secondary hover">Название</span>
        <span class="tab secondary on">Название</span>
      </div>
    </section>

    <section class="card">
      <h2>Кнопки</h2>
      <div class="matrices">
        <div>
          <h3>Neutral</h3>
          <div class="matrix neutral-matrix">
            <span></span>
            <span v-for="kind in neutralKinds" :key="kind" class="caption">{{ kind }}</span>
            <template v-for="state in states" :key="state">
              <span class="caption">{{ state }}</span>
              <button
                v-for="kind in neutralKinds"
                :key="kind"
                class="btn"
                :class="['neutral', kind, state]"
                type="button"
                :disabled="state === 'disable'"
              >
                Кнопка
              </button>
            </template>
          </div>
        </div>
        <div>
          <h3>Brand</h3>
          <div class="matrix brand-matrix">
            <span></span>
            <span v-for="kind in brandKinds" :key="kind" class="caption">{{ kind }}</span>
            <template v-for="state in states" :key="state">
              <span class="caption">{{ state }}</span>
              <button
                v-for="kind in brandKinds"
                :key="kind"
                class="btn"
                :class="['brand', kind, state]"
                type="button"
                :disabled="state === 'disable'"
              >
                Кнопка
              </button>
            </template>
          </div>
        </div>
      </div>
    </section>

    <section class="card">
      <h2>Ячейки таблицы</h2>
      <div class="cell-board">
        <div class="cell-col">
          <span class="caption">Неделя</span>
          <div class="cell wide"><span class="empty"></span></div>
          <div class="cell wide active"><img :src="plusCellIcon" alt="" width="20" height="20" /></div>
          <div class="cell wide">
            <span class="chip cornflower">
              <span>08 - 18</span>
              <small>10 ч</small>
            </span>
          </div>
        </div>
        <div class="cell-col">
          <span class="caption">Месяц</span>
          <div class="cell"><span class="empty"></span></div>
          <div class="cell active"><img :src="plusCellIcon" alt="" width="20" height="20" /></div>
          <div class="cell">
            <span class="chip cornflower month"><strong>10 ч</strong></span>
          </div>
        </div>
      </div>
    </section>

    <section class="card">
      <h2>Смена</h2>
      <div class="shift-board">
        <span class="caption">Неделя</span>
        <span class="caption">Месяц</span>
        <span class="caption">Шаблоны смен</span>
        <template v-for="(shift, index) in shifts" :key="shift.tone">
          <span class="chip" :class="shift.tone">
            <span>{{ shift.time }}</span>
            <small>{{ shift.hours }}</small>
          </span>
          <span class="chip month" :class="shift.tone">
            <strong>{{ shift.hours }}</strong>
          </span>
          <span v-if="templates[index]" class="template" :class="templates[index].tone">
            <span>
              <strong>Название</strong>
              <small>07:00 - 15:00 · 8 ч</small>
            </span>
            <b>{{ templates[index].key }}</b>
          </span>
          <span v-else></span>
        </template>
      </div>
    </section>

    <section class="card">
      <h2>Сотрудник</h2>
      <div class="agent">
        <span class="avatar">
          <img :src="avatarGradient" alt="" width="40" height="40" />
          <span>ME</span>
        </span>
        <span>
          <strong>Сергеев Олег</strong>
          <small>агент · 24 ч</small>
        </span>
      </div>
    </section>

    <section class="card">
      <h2>Поиск</h2>
      <div class="search-board">
        <div>
          <span class="caption">Серые</span>
          <label class="search gray">
            <img :src="searchFieldIcon" alt="" width="24" height="24" />
            <input id="kit-search-gray" v-model="grayQuery" name="kit-search-gray" type="search" placeholder="Поиск по ФИО" aria-label="Поиск по ФИО" />
            <button v-if="grayQuery" class="clear" type="button" aria-label="Очистить" @click.stop="grayQuery = ''">
              <img :src="closeIcon" alt="" width="24" height="24" />
            </button>
          </label>
          <div class="search gray hover">
            <img :src="searchFieldIcon" alt="" width="24" height="24" />
            <span>Поиск по ФИО</span>
          </div>
          <div class="search gray filled">
            <img :src="searchFieldIcon" alt="" width="24" height="24" />
            <span>Пишем что-то</span>
            <img :src="closeIcon" alt="" width="24" height="24" />
          </div>
        </div>
        <div>
          <span class="caption">Белый</span>
          <label class="search white">
            <img :src="searchFieldIcon" alt="" width="24" height="24" />
            <input id="kit-search-white" v-model="whiteQuery" name="kit-search-white" type="search" placeholder="Поиск по ФИО" aria-label="Поиск по ФИО" />
            <button v-if="whiteQuery" class="clear" type="button" aria-label="Очистить" @click.stop="whiteQuery = ''">
              <img :src="closeIcon" alt="" width="24" height="24" />
            </button>
          </label>
          <div class="search white hover">
            <img :src="searchFieldIcon" alt="" width="24" height="24" />
            <span>Поиск по ФИО</span>
          </div>
          <div class="search white filled">
            <img :src="searchFieldIcon" alt="" width="24" height="24" />
            <span>Пишем что-то</span>
            <img :src="closeIcon" alt="" width="24" height="24" />
          </div>
        </div>
      </div>
    </section>

    <section class="card">
      <h2>Быстрые действия</h2>
      <div class="selection">
        <div class="selection-meta">
          <span>Выделено:</span>
          <b>6 ячеек</b>
          <span>2 сотрудника(-ов) · 4 дн.</span>
        </div>
        <div class="selection-actions">
          <button type="button"><img :src="actionTemplateIcon" alt="" width="16" height="16" />Применить шаблон</button>
          <button type="button"><img :src="actionEditIcon" alt="" width="16" height="16" />Изменить</button>
          <button type="button"><img :src="actionClearIcon" alt="" width="16" height="16" />Очистить</button>
          <button type="button"><img :src="actionDismissIcon" alt="" width="16" height="16" />Снять</button>
        </div>
      </div>
    </section>

    <section class="card">
      <h2>Бейджи</h2>
      <div class="badges">
        <span class="badge blue">Badge</span>
        <span class="badge neutral">Badge</span>
      </div>
    </section>
  </div>
</template>

<style scoped>
.kit {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.intro h1,
.card h2,
.matrices h3 {
  margin: 0;
  font-weight: 600;
}

.intro h1 {
  font-size: 32px;
  line-height: 32px;
  letter-spacing: -1px;
}

.intro p,
.caption {
  color: #737378;
}

.intro p {
  margin: 8px 0 0;
}

.card {
  padding: 24px;
  border-radius: 12px;
  background: #fff;
}

.card h2 {
  margin-bottom: 16px;
  font-size: 20px;
  line-height: 24px;
}

.caption {
  font-size: 14px;
  font-weight: 600;
  line-height: 20px;
  text-transform: capitalize;
}

.tabs {
  display: flex;
  gap: 0;
  width: fit-content;
  max-width: 100%;
  padding: 2px;
  border-radius: 12px;
  background: #e7e8ee;
}

.state-grid {
  display: grid;
  grid-template-columns: 88px repeat(3, max-content);
  gap: 12px 16px;
  align-items: center;
  margin-top: 16px;
}

.tab {
  padding: 12px 24px;
  border: 0;
  border-radius: 10px;
  background: transparent;
  color: #101012;
  font-size: 16px;
  font-weight: 600;
  line-height: 24px;
}

.tabs .tab:hover:not(.on) {
  background: #f8f9fb;
}

.tab.on {
  background: #fff;
}

.tab.primary {
  background: #e7e8ee;
}

.tab.primary.hover {
  background: #f8f9fb;
}

.tab.primary.on {
  background: #fff;
}

.tab.secondary {
  padding-top: 8px;
  padding-bottom: 8px;
  background: #f2f3f7;
}

.tab.secondary.hover {
  background: #e7e8ee;
}

.tab.secondary.on {
  background: #3c3c43;
  color: #fff;
}

.matrices {
  display: flex;
  flex-wrap: wrap;
  gap: 32px;
}

.matrices h3 {
  margin-bottom: 12px;
  font-size: 16px;
}

.matrix {
  display: grid;
  gap: 12px 16px;
  align-items: center;
}

.neutral-matrix {
  grid-template-columns: 72px repeat(5, max-content);
}

.brand-matrix {
  grid-template-columns: 72px repeat(3, max-content);
}

.btn {
  min-width: 154px;
  padding: 12px 16px;
  border: 1px solid transparent;
  border-radius: 8px;
  background: transparent;
  color: #101012;
  font-size: 16px;
  font-weight: 600;
  line-height: 24px;
}

.btn:disabled {
  opacity: 1;
  cursor: default;
}

.neutral.primary.default { background: #242426; color: #fff; }
.neutral.primary.hover { background: #3c3c43; color: #fff; }
.neutral.primary.active { background: #595961; color: #fff; }
.neutral.primary.disable,
.brand.primary.disable { background: #f2f3f7; color: #c6c6cd; }

.neutral.secondary.disable,
.brand.secondary.disable { background: #f2f3f7; color: #c1c1c3; }

.brand.primary.default { background: #5b7dec; color: #fff; }
.brand.primary.hover { background: #1c4ae5; color: #fff; }
.brand.primary.active { background: #163bb7; color: #fff; }

.neutral.secondary.default { background: #f2f3f7; }
.neutral.secondary.hover { background: #e7e8ee; }
.neutral.secondary.active { background: #cdcdd6; }
.neutral.secondary.disable,
.brand.secondary.disable,
.neutral.tertiary.disable,
.brand.tertiary.disable,
.neutral.quaternary.disable,
.neutral.quinary.disable { color: #c1c1c3; }

.brand.secondary.default { background: #e8edfc; color: #1c4ae5; }
.brand.secondary.hover { background: #d2dbfa; color: #1c4ae5; }
.brand.secondary.active { background: #bbc9f7; color: #1c4ae5; }

.neutral.tertiary.default { border-color: #101012; }
.neutral.tertiary.hover { border-color: #101012; background: #f8f9fb; }
.neutral.tertiary.active { border-color: #101012; background: #e7e8ee; }
.neutral.tertiary.disable,
.brand.tertiary.disable { border-color: #bcbcc2; }

.brand.tertiary.default { border-color: #5b7dec; color: #1c4ae5; }
.brand.tertiary.hover { border-color: #5b7dec; background: #e8edfc; color: #1c4ae5; }
.brand.tertiary.active { border-color: #5b7dec; background: #d2dbfa; color: #1c4ae5; }

.neutral.quaternary.default { border-color: #dedee3; }
.neutral.quaternary.hover { border-color: #dedee3; background: #f8f9fb; }
.neutral.quaternary.active { border-color: #dedee3; background: #e7e8ee; }
.neutral.quaternary.disable { border-color: #dedee3; }

.neutral.quinary { border-radius: 11px; }
.neutral.quinary.default,
.neutral.quinary.disable { background: #fff; }
.neutral.quinary.hover { background: #e7e8ee; }
.neutral.quinary.active { background: #cdcdd6; }

.cell-board {
  display: flex;
  gap: 24px;
  align-items: flex-start;
}

.shift-board {
  display: grid;
  grid-template-columns: 110px 80px 240px;
  gap: 16px 24px;
  align-items: center;
  width: fit-content;
}

.cell-col {
  display: flex;
  flex-direction: column;
  gap: 12px;
  align-items: center;
}

.cell {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 64px;
  height: 72px;
  padding: 4px;
  border-radius: 8px;
}

.cell.wide {
  width: 110px;
}

.cell.active {
  border: 1px solid #5b7dec;
  background: #ebeffd;
}

.empty {
  width: 100%;
  height: 100%;
  border: 1px dashed #dedee3;
  border-radius: 8px;
}

.chip {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 98px;
  height: 64px;
  border-radius: 8px;
  font-size: 16px;
  line-height: 24px;
}

.chip.month {
  width: 64px;
  font-weight: 600;
}

.chip small {
  font-size: 12px;
  line-height: 16px;
}

.chip.cornflower { background: #e8edfc; }
.chip.mint { background: #e0f8f7; }
.chip.lavanda { background: #e9deff; }
.chip.brand { background: #5b7dec; color: #fff; }
.chip.cornflower small,
.chip.cornflower.month strong { color: #5b7dec; }
.chip.mint small,
.chip.mint.month strong { color: #009f98; }
.chip.lavanda small,
.chip.lavanda.month strong { color: #8040ff; }

.template {
  display: flex;
  gap: 8px;
  align-items: flex-start;
  width: 220px;
  padding: 8px 8px 8px 16px;
  border-radius: 8px;
  border-left: 3px solid #bbc9f7;
  background: #f2f3f7;
}

.template span {
  display: flex;
  flex: 1;
  flex-direction: column;
  min-width: 0;
}

.template strong,
.template small {
  font-size: 16px;
  font-weight: 400;
  line-height: 24px;
}

.template strong {
  font-weight: 600;
}

.template b {
  padding: 2px 8px;
  border-radius: 4px;
  background: #fff;
  font-size: 12px;
  line-height: 16px;
}

.template.mint { border-left-color: #b2eeeb; }
.template.lavanda { border-left-color: #c8adff; }

.agent {
  display: flex;
  gap: 8px;
  align-items: center;
  width: 220px;
  height: 72px;
  padding: 12px 12px 12px 8px;
}

.avatar {
  position: relative;
  flex: none;
  width: 40px;
  height: 40px;
  overflow: hidden;
  border: 0.5px solid #f2f3f7;
  border-radius: 4px;
  background: #e7e8ee;
}

.avatar img {
  display: block;
  width: 40px;
  height: 40px;
  object-fit: cover;
}

.avatar span {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  line-height: 24px;
}

.agent strong,
.agent small {
  display: block;
}

.agent strong {
  font-size: 16px;
  font-weight: 400;
  line-height: 24px;
}

.agent small {
  color: #737378;
  font-size: 12px;
  line-height: 16px;
}

.search-board {
  display: flex;
  flex-wrap: wrap;
  gap: 32px;
}

.search-board > div {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.search {
  display: flex;
  gap: 10px;
  align-items: center;
  width: 398px;
  max-width: 100%;
  height: 48px;
  padding: 12px;
  border: 1px solid transparent;
  border-radius: 12px;
  color: #737378;
  font-size: 16px;
  line-height: 24px;
}

@media (max-width: 767.98px) {
  .intro h1 {
    font-size: 28px;
  }

  .card {
    padding: 16px;
    overflow-x: auto;
  }

  .search {
    width: 100%;
    max-width: 398px;
  }
}

.search.gray { background: #f2f3f7; }
.search.white { background: #fff; }
.search:hover,
.search:focus-within,
.search.hover { border-color: #bcbcc2; }
.search.filled,
.search:focus-within { color: #101012; }
.search.filled img:last-child { margin-left: auto; }

.search input {
  flex: 1;
  min-width: 0;
  width: auto;
  padding: 0;
  border: 0;
  outline: none;
  background: transparent;
  color: #101012;
  font: inherit;
}

.clear {
  display: flex;
  flex: none;
  margin-left: auto;
  padding: 0;
  border: 0;
  background: transparent;
}

.search input::placeholder { color: #737378; }

.selection {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: center;
  justify-content: space-between;
  max-width: 997px;
  padding: 8px 12px;
  border-radius: 8px;
  background: #3c3c43;
  color: #fff;
  font-size: 14px;
  line-height: 20px;
}

.selection-meta,
.selection-actions,
.badges {
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

.selection button {
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

.badge {
  padding: 4px 8px 6px;
  border-radius: 999px;
  color: #fff;
  font-size: 12px;
  font-weight: 600;
  line-height: 16px;
}

.badge.blue { background: #3e87ff; }
.badge.neutral { background: #242426; }
</style>
