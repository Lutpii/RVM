<template>
  <span class="user-avatar" :style="style" aria-hidden="true">
    <component :is="picked.component" v-if="picked" :size="iconSize" weight="regular" />
    <template v-else>{{ initial }}</template>
  </span>
</template>

<script setup>
import { computed } from 'vue'
import { parseAvatar } from '@/utils/avatarIcons.js'

// The user's picked icon avatar, or the first letter of their name.
const props = defineProps({
  user: { type: Object, default: null },
  // Preview a choice that isn't saved yet ("leaf:green"); null = the user's own.
  value: { type: String, default: undefined },
  size: { type: Number, default: 40 },
})

const picked = computed(() => parseAvatar(props.value !== undefined ? props.value : props.user?.avatar_icon))
const initial = computed(() => (props.user?.name || '?').trim().charAt(0).toUpperCase())
const iconSize = computed(() => Math.round(props.size * 0.52))
const style = computed(() => ({
  width: `${props.size}px`,
  height: `${props.size}px`,
  fontSize: `${Math.round(props.size * 0.4)}px`,
  background: picked.value ? picked.value.hex : 'rgba(255,255,255,0.25)',
}))
</script>

<style scoped>
.user-avatar {
  display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
  border-radius: 50%; color: #fff; font-weight: 800; line-height: 1;
}
</style>
