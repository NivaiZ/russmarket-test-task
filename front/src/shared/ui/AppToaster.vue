<script setup lang="ts">
import { useToast } from './toast'

const { items, dismiss, pause, resume } = useToast()
</script>

<template>
  <div class="toaster" aria-live="polite">
    <TransitionGroup name="toast">
      <article
        v-for="item in items"
        :key="item.id"
        class="toast"
        :class="item.tone"
        role="status"
        @mouseenter="pause(item.id)"
        @mouseleave="resume(item.id)"
      >
        <svg v-if="item.tone === 'success'" class="icon" viewBox="0 0 24 24" aria-hidden="true">
          <path
            d="M5 12.5 9.5 17 19 7.5"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
        <svg v-else class="icon" viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="2" />
          <path d="M12 8v5M12 16.2h.01" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
        <p class="summary">{{ item.text }}</p>
        <button class="close" type="button" aria-label="Закрыть" @click="dismiss(item.id)">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path
              d="M7 7l10 10M17 7 7 17"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
            />
          </svg>
        </button>
      </article>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toaster {
  position: fixed;
  z-index: 80;
  top: 20px;
  right: 20px;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  pointer-events: none;
}

.toast {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  width: max-content;
  max-width: min(360px, calc(100vw - 40px));
  max-height: 8rem;
  margin: 0 0 8px;
  padding: 12px;
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 8px 24px rgb(16 16 18 / 12%);
  color: #101012;
  pointer-events: auto;
}

.success .icon {
  color: #5b7dec;
}

.error .icon {
  color: #dc2626;
}

.icon {
  flex: none;
  width: 20px;
  height: 20px;
  margin-top: 2px;
}

.summary {
  flex: 1;
  margin: 0;
  font-size: 16px;
  font-weight: 500;
  line-height: 24px;
}

.close {
  display: flex;
  flex: none;
  align-items: center;
  justify-content: center;
  width: 1.75rem;
  height: 1.75rem;
  margin-top: -0.15rem;
  padding: 0;
  border: 0;
  border-radius: 50%;
  background: transparent;
  color: #737378;
}

.close svg {
  width: 1rem;
  height: 1rem;
}

.close:hover {
  background: #f2f3f7;
}

.toast-enter-from {
  opacity: 0;
  transform: translateY(50%);
}

.toast-enter-active {
  transition:
    transform 0.3s,
    opacity 0.3s;
}

.toast-leave-active {
  overflow: hidden;
  transition:
    max-height 0.45s cubic-bezier(0, 1, 0, 1),
    opacity 0.3s,
    margin-bottom 0.3s;
}

.toast-leave-to {
  max-height: 0;
  margin-bottom: 0;
  opacity: 0;
}

@media (prefers-reduced-motion: reduce) {
  .toast-enter-active,
  .toast-leave-active {
    transition: none;
  }
}
</style>
