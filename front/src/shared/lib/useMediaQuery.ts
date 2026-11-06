import { onMounted, onUnmounted, ref } from 'vue'

export function useMediaQuery(query: string) {
  const media = window.matchMedia(query)
  const matches = ref(media.matches)

  function onChange(event: MediaQueryListEvent): void {
    matches.value = event.matches
  }

  onMounted(() => {
    matches.value = media.matches
    media.addEventListener('change', onChange)
  })

  onUnmounted(() => {
    media.removeEventListener('change', onChange)
  })

  return matches
}
