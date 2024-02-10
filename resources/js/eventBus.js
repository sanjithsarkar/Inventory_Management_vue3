import { ref, provide, inject } from 'vue'

const eventBusKey = Symbol()

export function useEventBus() {
  const eventBus = ref(null)

  provide(eventBusKey, eventBus)

  return eventBus
}

export function injectEventBus() {
  const eventBus = inject(eventBusKey)

  if (!eventBus) {
    throw new Error('Event bus not found')
  }

  return eventBus
}
