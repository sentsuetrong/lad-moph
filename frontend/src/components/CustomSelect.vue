<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { useFloating, offset, flip, shift, autoUpdate } from '@floating-ui/vue';

interface Option {
  value: string | number;
  label: string;
}

interface Props {
  modelValue?: string | number | null;
  options: Option[];
  placeholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'เลือกตัวเลือก...'
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const searchQuery = ref('');
const highlightedIndex = ref(-1); // เริ่มต้นที่ -1 หมายถึงยังไม่มีการไฮไลท์
const inputRef = ref<HTMLElement | null>(null);
const dropdownRef = ref<HTMLElement | null>(null);
const searchInputRef = ref<HTMLInputElement | null>(null);
const listRef = ref<HTMLUListElement | null>(null); // Ref สำหรับ list element

const { floatingStyles } = useFloating(inputRef, dropdownRef, {
  placement: 'bottom-start',
  middleware: [offset(8), flip(), shift({ padding: 8 })],
  whileElementsMounted: autoUpdate,
});

const selectedOption = computed(() => {
  return props.options.find((option) => option.value === props.modelValue);
});

const filteredOptions = computed(() => {
  if (!searchQuery.value.trim()) {
    return props.options;
  }
  const lowerSearchQuery = searchQuery.value.toLowerCase();
  return props.options.filter((option) =>
    option.label.toLowerCase().includes(lowerSearchQuery)
  );
});

// ฟังก์ชันสำหรับเลื่อน Scroll ไปยังรายการที่ถูกไฮไลท์
async function scrollToHighlighted() {
  await nextTick(); // รอให้ DOM อัปเดตก่อน
  if (listRef.value && highlightedIndex.value >= 0 && highlightedIndex.value < filteredOptions.value.length) {
    const highlightedElement = listRef.value.children[highlightedIndex.value] as HTMLLIElement;
    if (highlightedElement) {
      highlightedElement.scrollIntoView({ block: 'nearest' });
    }
  }
}

function selectOption(option: Option) {
  emit('change', option.value);
  emit('update:modelValue', option.value);
  isOpen.value = false;
}

function moveHighlight(direction: number) {
  if (filteredOptions.value.length === 0) return;
  const maxIndex = filteredOptions.value.length - 1;
  let newIndex = highlightedIndex.value + direction;

  if (newIndex < 0) {
    newIndex = maxIndex; // Wrap around to the bottom
  } else if (newIndex > maxIndex) {
    newIndex = 0; // Wrap around to the top
  }
  highlightedIndex.value = newIndex;
  scrollToHighlighted();
}

function handleKeyDown(event: KeyboardEvent) {
  switch (event.key) {
    case 'Tab':
      if (isOpen.value) {
        event.preventDefault(); // Prevent tabbing out when open
        if (highlightedIndex.value >= 0 && filteredOptions.value.length > 0) {
          selectOption(filteredOptions.value[highlightedIndex.value]);
        } else {
          isOpen.value = false; // Close if nothing is highlighted
        }
      }
      // Allow tabbing away if closed
      break;
    case 'Enter':
      event.preventDefault();
      if (isOpen.value) {
        if (highlightedIndex.value >= 0 && filteredOptions.value.length > 0) {
          selectOption(filteredOptions.value[highlightedIndex.value]);
        }
      } else {
        isOpen.value = true;
      }
      break;
    case 'ArrowDown':
      event.preventDefault();
      if (!isOpen.value) {
        isOpen.value = true;
      } else {
        moveHighlight(1);
      }
      break;
    case 'ArrowUp':
      event.preventDefault();
      if (isOpen.value) {
        moveHighlight(-1);
      }
      break;
    case 'Escape':
      if (isOpen.value) {
        event.stopPropagation(); // Prevent other escape handlers if dropdown is open
        isOpen.value = false;
      }
      break;
    default:
      // Allow typing to open the dropdown and filter directly (optional)
      if (!isOpen.value && event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
        // Optionally open and start search on typing
        // isOpen.value = true;
        // searchQuery.value = event.key;
      }
      break;
  }
}

function handleSearchKeyDown(event: KeyboardEvent) {
  // Stop propagation for arrow keys and enter to prevent main input handler
  if (['ArrowDown', 'ArrowUp', 'Enter', 'Escape'].includes(event.key)) {
    event.stopPropagation(); // Prevent main div keydown handler
  }

  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault();
      moveHighlight(1);
      break;
    case 'ArrowUp':
      event.preventDefault();
      moveHighlight(-1);
      break;
    case 'Enter':
      event.preventDefault();
      if (highlightedIndex.value >= 0 && filteredOptions.value.length > 0) {
        selectOption(filteredOptions.value[highlightedIndex.value]);
      }
      break;
    case 'Escape':
      event.preventDefault();
      isOpen.value = false;
      break;
  }
}

function handleClickOutside(event: MouseEvent) {
  if (
    isOpen.value &&
    inputRef.value &&
    dropdownRef.value &&
    !inputRef.value.contains(event.target as Node) &&
    !dropdownRef.value.contains(event.target as Node)
  ) {
    isOpen.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});

// Watcher สำหรับ isOpen
watch(isOpen, (newValue) => {
  if (newValue) {
    // เมื่อ dropdown เปิด
    nextTick(() => {
      // Focus input search
      if (searchInputRef.value) {
        searchInputRef.value.focus();
      }

      // --- Logic สำคัญ: ตั้งค่า highlightedIndex เริ่มต้น ---
      // หา index ของ option ที่ถูกเลือกปัจจุบันในรายการที่ *ยังไม่* ถูก filter
      const currentSelectedIndex = props.options.findIndex(option => option.value === props.modelValue);
      // ตั้งค่า highlightedIndex (ถ้าไม่เจอ ให้เป็น -1)
      highlightedIndex.value = currentSelectedIndex;
      // เลื่อน scroll ไปยังตำแหน่งที่ไฮไลท์ (ถ้ามี)
      scrollToHighlighted();
      // -----------------------------------------------------
    });
  } else {
    // เมื่อ dropdown ปิด
    searchQuery.value = ''; // ล้างค่าค้นหา
    highlightedIndex.value = -1; // รีเซ็ตการไฮไลท์
    // Return focus to the main input element when dropdown closes
    nextTick(() => {
      inputRef.value?.focus();
    });
  }
});

// Watcher สำหรับ searchQuery
watch(searchQuery, () => {
  // เมื่อมีการเปลี่ยนแปลงค่าค้นหา ให้รีเซ็ต highlightedIndex
  // และเลื่อนไปที่รายการแรก (ถ้ามีผลลัพธ์)
  highlightedIndex.value = filteredOptions.value.length > 0 ? 0 : -1;
  scrollToHighlighted();
});

</script>

<template>
  <div class="relative w-full">
    <div ref="inputRef"
      class="border border-gray-300 rounded-md p-2 flex items-center justify-between cursor-pointer w-full min-h-[40px] focus:outline-none focus:ring-2 focus:ring-emerald-500"
      :class="{ 'ring-2 ring-emerald-500 border-emerald-500': isOpen }" @click.stop="isOpen = !isOpen"
      @keydown="handleKeyDown" tabindex="0">
      <div v-if="selectedOption" class="text-sm flex-1 mr-1 truncate"> {{ selectedOption.label }}
      </div>
      <div v-else class="text-gray-400 text-sm flex-1 mr-1">
        {{ props.placeholder }}
      </div>
      <svg xmlns="http://www.w3.org/2000/svg"
        class="h-4 w-4 text-gray-400 flex-shrink-0 transition-transform duration-200" :class="{ 'rotate-180': isOpen }"
        fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </div>

    <div v-if="isOpen" ref="dropdownRef" :style="floatingStyles"
      class="w-full bg-white border border-gray-200 rounded-md shadow-lg z-50 mt-1 transition-opacity duration-200">
      <div class="p-2">
        <input v-model="searchQuery" type="text"
          class="w-full text-sm border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
          :placeholder="props.placeholder" @keydown="handleSearchKeyDown" ref="searchInputRef" />
      </div>
      <ul ref="listRef" class="max-h-48 overflow-x-hidden overflow-y-auto p-1">
        <li v-for="(option, index) in filteredOptions" :key="option.value"
          class="text-sm p-2 rounded cursor-pointer hover:bg-gray-100" :class="{
            'bg-emerald-100 text-emerald-800': selectedOption?.value === option.value && highlightedIndex !== index, // Style for selected but not highlighted
            'text-emerald-800': highlightedIndex === index, // Style for highlighted
            'hover:bg-blue-50': highlightedIndex !== index // Hover effect only if not highlighted
          }" @click.stop="selectOption(option)" @mouseenter="highlightedIndex = index">
          {{ option.label }}
        </li>
        <li v-if="filteredOptions.length === 0 && searchQuery" class="p-2 text-sm text-gray-500">
          ไม่พบตัวเลือกที่ตรงกัน
        </li>
        <li v-if="filteredOptions.length === 0 && !searchQuery" class="p-2 text-sm text-gray-500">
          ไม่มีตัวเลือก
        </li>
      </ul>
    </div>
  </div>
</template>

<style scoped>
/* Tailwind JIT reference */
@reference "tailwindcss";

/* Custom scrollbar (optional) */
ul::-webkit-scrollbar {
  width: 6px;
}

ul::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

ul::-webkit-scrollbar-thumb {
  background: #ccc;
  border-radius: 3px;
}

ul::-webkit-scrollbar-thumb:hover {
  background: #aaa;
}
</style>