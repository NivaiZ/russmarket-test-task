<script setup lang="ts">
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useMediaQuery } from '@/shared/lib'
import { menuIcon, Toaster } from '@/shared/ui'
import SideNav from './SideNav.vue'

const route = useRoute()
const collapsed = ref(false)
const menuOpen = ref(false)
const drawer = ref<HTMLElement | null>(null)
const isCompact = useMediaQuery('(max-width: 1199.98px)')

watch(isCompact, (compact) => {
  if (!compact) {
    menuOpen.value = false
  }
})

watch(
  () => route.fullPath,
  () => {
    menuOpen.value = false
  },
)

watch(menuOpen, async (open) => {
  document.body.style.overflow = open ? 'hidden' : ''
  if (!open) {
    return
  }

  await nextTick()
  drawer.value?.querySelector<HTMLElement>('a')?.focus()
})

function onKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape') {
    menuOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('keydown', onKeydown)
})

onUnmounted(() => {
  document.body.style.overflow = ''
  document.removeEventListener('keydown', onKeydown)
})
</script>

<template>
  <div class="shell" :class="{ collapsed: collapsed && !isCompact, compact: isCompact }">
    <aside v-if="!isCompact" class="menu">
      <SideNav collapsible :collapsed="collapsed" @toggle="collapsed = !collapsed" />
    </aside>

    <header v-else class="mobile-bar">
      <button
        class="menu-toggle"
        type="button"
        aria-label="Открыть меню"
        :aria-expanded="menuOpen"
        @click="menuOpen = true"
      >
        <img :src="menuIcon" alt="" width="24" height="24" />
      </button>
      <span class="mobile-title">CRM</span>
    </header>

    <Teleport to="body">
      <Transition name="overlay">
        <button
          v-if="isCompact && menuOpen"
          class="overlay"
          type="button"
          aria-label="Закрыть меню"
          @click="menuOpen = false"
        />
      </Transition>
      <Transition name="drawer">
        <aside v-if="isCompact && menuOpen" ref="drawer" class="menu drawer" aria-label="Навигация">
          <SideNav @navigate="menuOpen = false" />
        </aside>
      </Transition>
    </Teleport>

    <main class="content">
      <RouterView />
    </main>
    <Toaster />
  </div>
</template>

<style scoped>
.shell {
  display: flex;
  align-items: flex-start;
  gap: 30px;
  min-height: 100vh;
  padding-right: 30px;
  background: #f2f3f7;
}

.shell.compact {
  flex-direction: column;
  gap: 0;
  padding-right: 0;
}

.menu {
  position: sticky;
  top: 0;
  display: flex;
  flex: none;
  flex-direction: column;
  align-self: flex-start;
  width: 280px;
  height: 100vh;
  padding: 16px 16px 16px 14px;
  overflow-x: hidden;
  overflow-y: auto;
  border-right: 1px solid #dedee3;
  background: #f2f3f7;
  transition:
    width 0.2s cubic-bezier(0.22, 0.61, 0.36, 1),
    padding 0.2s cubic-bezier(0.22, 0.61, 0.36, 1);
}

.collapsed .menu {
  width: 72px;
  padding-right: 14px;
}

.drawer {
  position: fixed;
  z-index: 41;
  top: 0;
  bottom: auto;
  left: 0;
  height: 100dvh;
  border-right: 0;
  border-radius: 0 16px 16px 0;
  box-shadow: 0 8px 24px rgb(16 16 18 / 12%);
}

.overlay {
  position: fixed;
  z-index: 40;
  inset: 0;
  border: 0;
  background: rgb(5 5 6 / 24%);
}

.mobile-bar {
  position: sticky;
  z-index: 20;
  top: 0;
  display: flex;
  align-items: center;
  gap: 4px;
  width: 100%;
  height: 56px;
  padding: 0 16px 0 8px;
  background: #f2f3f7;
}

.menu-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border: 0;
  border-radius: 8px;
  background: transparent;
}

.menu-toggle:hover {
  background: #e7e8ee;
}

.menu-toggle:active {
  background: #cdcdd6;
}

.mobile-title {
  font-size: 20px;
  font-weight: 600;
  line-height: 24px;
  letter-spacing: -0.3px;
}

.content {
  flex: 1;
  width: 100%;
  min-width: 0;
  padding: 30px 0;
}

.compact .content {
  padding: 8px 16px 32px;
}

.overlay-enter-active,
.overlay-leave-active,
.drawer-enter-active,
.drawer-leave-active {
  transition: transform 0.2s cubic-bezier(0.22, 0.61, 0.36, 1), opacity 0.2s cubic-bezier(0.22, 0.61, 0.36, 1);
}

.overlay-enter-from,
.overlay-leave-to {
  opacity: 0;
}

.drawer-enter-from,
.drawer-leave-to {
  transform: translate3d(-100%, 0, 0);
}

@media (prefers-reduced-motion: reduce) {
  .menu,
  .overlay-enter-active,
  .overlay-leave-active,
  .drawer-enter-active,
  .drawer-leave-active {
    transition: none;
  }
}
</style>
