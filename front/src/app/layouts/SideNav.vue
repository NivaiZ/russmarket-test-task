<script setup lang="ts">
import { bellIcon, calendarIcon, collapseIcon, homeIcon, shiftsIcon } from '@/shared/ui'

defineProps<{
  collapsed?: boolean
  collapsible?: boolean
}>()

const emit = defineEmits<{
  toggle: []
  navigate: []
}>()
</script>

<template>
  <div class="side" :class="{ collapsed }">
    <div class="menu-top">
      <div class="brand">
        <span v-if="!collapsed" class="brand-title">CRM</span>
        <button class="icon-button" type="button" aria-label="Уведомления">
          <img :src="bellIcon" alt="" width="24" height="24" />
        </button>
      </div>
      <nav aria-label="Основное меню" @click="emit('navigate')">
        <ul class="nav">
          <li>
            <RouterLink class="nav-item" to="/" active-class="" exact-active-class="router-link-active">
              <img :src="homeIcon" alt="" width="24" height="24" />
              <span class="label">Главная</span>
            </RouterLink>
          </li>
          <li>
            <RouterLink class="nav-item" to="/schedules">
              <img :src="shiftsIcon" alt="" width="24" height="24" />
              <span class="label">Смены</span>
            </RouterLink>
          </li>
          <li>
            <RouterLink class="nav-item" to="/ui-kit">
              <img :src="calendarIcon" alt="" width="24" height="24" />
              <span class="label">UI Kit</span>
            </RouterLink>
          </li>
        </ul>
      </nav>
    </div>
    <button
      v-if="collapsible"
      class="nav-item collapse"
      type="button"
      :aria-label="collapsed ? 'Развернуть меню' : 'Свернуть меню'"
      @click="emit('toggle')"
    >
      <img :src="collapseIcon" alt="" width="24" height="24" />
      <span class="label">Свернуть меню</span>
    </button>
  </div>
</template>

<style scoped>
.side {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
  min-height: 0;
}

.menu-top {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 40px;
}

.brand-title {
  font-size: 20px;
  font-weight: 600;
  line-height: 24px;
  letter-spacing: -0.3px;
}

.icon-button {
  display: flex;
  flex: none;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  margin-left: auto;
  padding: 8px;
  border: 0;
  border-radius: 8px;
  background: #f2f3f7;
  transition: margin-left 0.2s cubic-bezier(0.22, 0.61, 0.36, 1);
}

.collapsed .brand {
  justify-content: center;
}

.collapsed .icon-button {
  margin-left: 0;
}

.nav {
  display: flex;
  flex-direction: column;
  gap: 2px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  height: 40px;
  padding: 8px;
  overflow: hidden;
  border: 0;
  border-radius: 8px;
  background: transparent;
  color: #101012;
  font-size: 16px;
  font-weight: 600;
  line-height: 24px;
  text-align: left;
  transition:
    gap 0.2s cubic-bezier(0.22, 0.61, 0.36, 1),
    background-color 0.2s cubic-bezier(0.22, 0.61, 0.36, 1);
}

.nav-item img {
  flex: none;
}

.label {
  overflow: hidden;
  max-width: 180px;
  white-space: nowrap;
  opacity: 1;
  transition:
    max-width 0.2s cubic-bezier(0.22, 0.61, 0.36, 1),
    opacity 0.15s cubic-bezier(0.22, 0.61, 0.36, 1);
}

.collapsed .label {
  max-width: 0;
  opacity: 0;
}

.collapsed .nav-item {
  gap: 0;
  width: 40px;
}

.nav-item.router-link-active {
  background: #fff;
}

.collapse {
  width: 100%;
}

.collapse img {
  transition: transform 0.2s cubic-bezier(0.22, 0.61, 0.36, 1);
}

.collapsed .collapse img {
  transform: scaleX(-1);
}

@media (prefers-reduced-motion: reduce) {
  .label,
  .icon-button,
  .nav-item,
  .collapse img {
    transition: none;
  }
}
</style>
