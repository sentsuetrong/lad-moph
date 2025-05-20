<script setup lang="ts">
import { computed } from 'vue';
import type { NavigationItemType } from '../../types';

// RouterLink มักจะถูก register เป็น global component
// import { RouterLink } from 'vue-router'; // โดยทั่วไปไม่จำเป็น

// Props definition
const props = defineProps<{
  item: NavigationItemType;
  level: number; // 0 = main, 1 = sub, 2 = grand-sub
  parentItem?: NavigationItemType;
  grandParentItem?: NavigationItemType;
}>();

// Emits definition
const emit = defineEmits<{
  (e: 'toggle-item', itemToToggle: NavigationItemType, parentOfItemToToggle: NavigationItemType | undefined): void;
  (e: 'link-clicked', itemClicked: NavigationItemType, parentOfItemClicked: NavigationItemType | undefined, grandParentOfItemClicked: NavigationItemType | undefined): void;
}>();

const isLink = computed(() => props.item.to && (!props.item.children || props.item.children.length === 0));
const hasChildren = computed(() => props.item.children && props.item.children.length > 0);

function onItemToggle(event: MouseEvent) {
  if (props.level > 0) { // สำหรับ sub-item, หยุด event propagation เพื่อไม่ให้ parent toggle
    event.stopPropagation();
  }
  emit('toggle-item', props.item, props.parentItem);
}

function onLinkClick() {
  emit('link-clicked', props.item, props.parentItem, props.grandParentItem);
}

// --- Computed properties for styling ---
const baseItemClasses = "text-sm font-medium transition-colors duration-150 focus:outline-none";
const baseDropdownClasses = "absolute w-56 origin-top-left rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none";

const itemClasses = computed(() => {
  let classes = '';
  if (props.level === 0) {
    classes = `px-3 py-2 rounded-md hover:bg-emerald-600 focus:bg-emerald-600 ${props.item.isOpen && hasChildren.value ? 'bg-emerald-600' : ''}`;
    if (hasChildren.value) classes += ' flex items-center';
  } else if (props.level === 1) {
    classes = `block px-4 py-2 hover:bg-emerald-700 focus:bg-emerald-700 w-full text-left ${props.item.isOpen && hasChildren.value ? 'bg-emerald-700' : ''}`;
    if (hasChildren.value) classes += ' flex justify-between items-center';
  } else { // Level 2+ (grandchild links)
    classes = `block px-4 py-2 hover:bg-emerald-800 focus:bg-emerald-800 w-full text-left`;
  }
  return `${baseItemClasses} ${classes}`;
});

const dropdownContainerClasses = computed(() => {
  let classes = '';
  if (props.level === 0) {
    classes = `left-0 mt-2 bg-emerald-600 z-40`;
  } else if (props.level === 1) {
    classes = `left-full top-0 -mt-px ml-px bg-emerald-700 z-50`;
  }
  return `${baseDropdownClasses} ${classes}`;
});

const svgIconClasses = computed(() => {
  let classes = "h-4 w-4 transition-transform duration-150";
  if (props.level === 0) {
    classes += " ml-1";
    if (props.item.isOpen) classes += " rotate-180";
  } else if (props.level === 1) {
    // `transform` class was used in original, rotate-90 handles it.
    if (props.item.isOpen) classes += " rotate-90";
    if (!props.item.isOpen) classes += " group-hover/child:text-white"; // Apply group hover style
  }
  return classes;
});

const iconPathD = computed(() => {
  if (props.level === 0) { // Down arrow for main items
    return "M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z";
  }
  // Right arrow for sub items
  return "M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z";
});

const dropdownId = computed(() => `${props.item.name.replace(/\s+/g, '-')}-${props.level}-dropdown`);
const buttonId = computed(() => `${props.item.name.replace(/\s+/g, '-')}-${props.level}-button`);

// Function to bubble events up from children
function bubbleToggleEvent(itemToToggle: NavigationItemType, parentOfItemToToggle: NavigationItemType | undefined) {
  emit('toggle-item', itemToToggle, parentOfItemToToggle);
}

function bubbleLinkClickEvent(itemClicked: NavigationItemType, parentOfItemClicked: NavigationItemType | undefined, grandParentOfItemClicked: NavigationItemType | undefined) {
  emit('link-clicked', itemClicked, parentOfItemClicked, grandParentOfItemClicked);
}
</script>

<template>
  <li class="relative" :class="{ 'group/child': level === 1 }">
    <router-link v-if="isLink" :to="item.to!" @click="onLinkClick" :class="itemClasses" role="menuitem">
      {{ item.name }}
    </router-link>
    <button v-else type="button" @click="onItemToggle" :class="itemClasses" :aria-expanded="item.isOpen"
      :aria-controls="dropdownId" :id="buttonId">
      {{ item.name }}
      <svg v-if="hasChildren" xmlns="http://www.w3.org/2000/svg" :class="svgIconClasses" viewBox="0 0 20 20"
        fill="currentColor">
        <path fill-rule="evenodd" :d="iconPathD" clip-rule="evenodd" />
      </svg>
    </button>

    <div v-if="hasChildren && item.isOpen" :id="dropdownId" :class="dropdownContainerClasses">
      <ul class="py-1" role="menu" aria-orientation="vertical" :aria-labelledby="buttonId">
        <HeaderNavigationItem v-for="childItem in item.children" :key="childItem.name" :item="childItem"
          :level="level + 1" :parentItem="item" :grandParentItem="parentItem" @toggle-item="bubbleToggleEvent"
          @link-clicked="bubbleLinkClickEvent" />
      </ul>
    </div>
  </li>
</template>
