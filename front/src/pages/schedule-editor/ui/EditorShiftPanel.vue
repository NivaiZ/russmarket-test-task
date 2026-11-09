<script setup lang="ts">
import { computed } from 'vue'
import Sheet from '@/shared/ui/AppSheet.vue'
import type { Shift, ShiftTemplate } from '../model/types'
import ShiftPanel from './ShiftPanel.vue'

const open = defineModel<boolean>('open', { default: false })

const props = defineProps<{
  phone: boolean
  templates: ShiftTemplate[]
  selectedCount: number
  editing: Shift | null
  customStart: string
  customEnd: string
  loading?: boolean
}>()

const emit = defineEmits<{
  'apply-template': [template: ShiftTemplate]
  'apply-custom': []
  'custom-start': [value: string]
  'custom-end': [value: string]
  'edit-start': [value: string]
  'edit-end': [value: string]
  'save-edit': []
  'delete-edit': []
}>()

const shell = computed(() =>
  props.phone
    ? {
        modelValue: open.value,
        'onUpdate:modelValue': (value: boolean) => {
          open.value = value
        },
      }
    : {},
)
</script>

<template>
  <component :is="phone ? Sheet : 'div'" v-bind="shell" :class="{ passthrough: !phone }">
    <template v-if="phone" #header>Смена</template>
    <ShiftPanel
      :flat="phone"
      :loading="loading"
      :templates="templates"
      :selected-count="selectedCount"
      :editing="editing"
      :custom-start="customStart"
      :custom-end="customEnd"
      @apply-template="emit('apply-template', $event)"
      @apply-custom="emit('apply-custom')"
      @custom-start="emit('custom-start', $event)"
      @custom-end="emit('custom-end', $event)"
      @edit-start="emit('edit-start', $event)"
      @edit-end="emit('edit-end', $event)"
      @save-edit="emit('save-edit')"
      @delete-edit="emit('delete-edit')"
    />
  </component>
</template>

<style scoped>
.passthrough {
  display: contents;
}
</style>
