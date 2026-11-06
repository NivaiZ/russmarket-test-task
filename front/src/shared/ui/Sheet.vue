<script setup lang="ts">
import BottomSheet from '@douxcode/vue-spring-bottom-sheet'
import '@douxcode/vue-spring-bottom-sheet/dist/style.css'

const open = defineModel<boolean>({ default: false })
</script>

<template>
  <BottomSheet v-model="open" header-class="app-sheet-header" content-class="app-sheet-content">
    <template #header>
      <slot name="header" />
    </template>
    <slot />
  </BottomSheet>
</template>

<style>
:root {
  --vsbs-background: #fff;
  --vsbs-border-radius: 16px;
  --vsbs-backdrop-bg: rgb(5 5 6 / 24%);
  --vsbs-handle-background: #dedee3;
  --vsbs-max-width: 560px;
}

[data-vsbs-backdrop] {
  z-index: 69;
}

[data-vsbs-sheet] {
  z-index: 70;
}

.app-sheet-header {
  display: flex;
  justify-content: center;
  color: #101012;
  font-size: 16px;
  font-weight: 600;
  line-height: 24px;
  text-align: center;
}

.app-sheet-content {
  padding-bottom: 8px;
}
</style>
