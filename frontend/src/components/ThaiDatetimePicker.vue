<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { format, addMonths, addYears, setHours, setMinutes, startOfDay } from 'date-fns';
import { th } from 'date-fns/locale';
import { useFloating, offset, flip, shift, autoUpdate } from '@floating-ui/vue';

type DatePickerMode = 'date' | 'datetime' | 'time';
type DateRangeMode = 'single' | 'range';
type YearType = 'buddhist' | 'christian';

interface Props {
  modelValue?: Date | [Date | null, Date | null];
  yearType?: YearType;
  rangeMode?: DateRangeMode;
  mode?: DatePickerMode;
  placeholder?: string;
  disabled?: boolean;
  minDate?: Date;
  maxDate?: Date;
  firstDayOfWeek?: 0 | 1; // 0 for Sunday, 1 for Monday
}

const props = withDefaults(defineProps<Props>(), {
  yearType: 'buddhist',
  rangeMode: 'single',
  mode: 'datetime',
  placeholder: 'เลือกวันที่',
  disabled: false,
  firstDayOfWeek: 0
});

const emit = defineEmits(['update:modelValue', 'change']);

const computedModelValue = computed({
  get: () => props.modelValue,
  set: (newValue) => {
    if (props.rangeMode === 'range') {
      if (!Array.isArray(newValue) || newValue.length !== 2) {
        emit('update:modelValue', [null, null]);
        emit('change', [null, null]);
      } else {
        emit('update:modelValue', newValue as [Date | null, Date | null]);
        emit('change', newValue as [Date | null, Date | null]);
      }
    } else {
      if (Array.isArray(newValue)) {
        emit('update:modelValue', null);
        emit('change', null);
      } else {
        emit('update:modelValue', newValue as Date | null);
        emit('change', newValue as Date | null);
      }
    }
  }
});

// UI state
const isOpen = ref(false);
const currentView = ref<'calendar' | 'month' | 'year' | 'time'>('calendar');
const currentMonth = ref(new Date());
const hoveredDate = ref<Date | null>(null);

// Selected dates
const selectedDate = ref<Date | null>(null);
const selectedStartDate = ref<Date | null>(null);
const selectedEndDate = ref<Date | null>(null);
const selectedHours = ref(0);
const selectedMinutes = ref(0);

// Input references
const inputRef = ref<HTMLElement | null>(null);
const dropdownRef = ref<HTMLElement | null>(null);

// Floating UI setup
const { floatingStyles } = useFloating(inputRef, dropdownRef, {
  placement: 'bottom-start',
  middleware: [
    offset(8),
    flip(),
    shift({ padding: 8 })
  ],
  whileElementsMounted: autoUpdate
});

// Initialize values
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  initializeValues();
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});

watch(() => props.modelValue, initializeValues);

function initializeValues() {
  const value = props.modelValue;

  if (props.rangeMode === 'single') {
    const dateValue = value as Date | null;
    selectedDate.value = dateValue ? new Date(dateValue) : null;
    if (selectedDate.value) {
      if (!isOpen.value || selectedDate.value.getMonth() !== currentMonth.value.getMonth() || selectedDate.value.getFullYear() !== currentMonth.value.getFullYear()) {
        currentMonth.value = new Date(selectedDate.value);
      }
      if (props.mode === 'datetime' || props.mode === 'time') {
        selectedHours.value = selectedDate.value.getHours();
        selectedMinutes.value = selectedDate.value.getMinutes();
      } else {
        selectedHours.value = 0;
        selectedMinutes.value = 0;
      }
    } else {
      selectedHours.value = 0;
      selectedMinutes.value = 0;
      // Keep currentMonth as is if no value, or set to today if needed
      // currentMonth.value = new Date(); // Only set if you always want to reset view
    }
    selectedStartDate.value = null;
    selectedEndDate.value = null;

  } else { // rangeMode === 'range'
    const rangeValue = value as [Date | null, Date | null] | null;
    const start = rangeValue?.[0];
    const end = rangeValue?.[1];

    selectedStartDate.value = start ? new Date(start) : null;
    selectedEndDate.value = end ? new Date(end) : null;

    if (selectedStartDate.value) {
      if (!isOpen.value || selectedStartDate.value.getMonth() !== currentMonth.value.getMonth() || selectedStartDate.value.getFullYear() !== currentMonth.value.getFullYear()) {
        currentMonth.value = new Date(selectedStartDate.value);
      }
      if (props.mode === 'datetime') {
        selectedHours.value = selectedStartDate.value.getHours();
        selectedMinutes.value = selectedStartDate.value.getMinutes();
      } else {
        selectedHours.value = 0;
        selectedMinutes.value = 0;
      }

    } else {
      selectedHours.value = 0;
      selectedMinutes.value = 0;
      // Keep currentMonth as is if no value, or set to today if needed
      // currentMonth.value = new Date(); // Only set if you always want to reset view
    }
    selectedDate.value = null;
  }
}

// Utility functions
function getDaysInMonth(year: number, month: number) {
  return new Date(year, month + 1, 0).getDate();
}

function getFirstDayOfMonth(year: number, month: number) {
  return new Date(year, month, 1).getDay();
}

// Calendar data
const calendarWeeks = computed(() => {
  const year = currentMonth.value.getFullYear();
  const month = currentMonth.value.getMonth();
  const daysInMonth = getDaysInMonth(year, month);
  let firstDay = getFirstDayOfMonth(year, month);

  if (props.firstDayOfWeek === 1) {
    firstDay = firstDay === 0 ? 6 : firstDay - 1;
  }

  const prevMonthYear = month === 0 ? year - 1 : year;
  const prevMonth = month === 0 ? 11 : month - 1;
  const daysInPrevMonth = getDaysInMonth(prevMonthYear, prevMonth);
  const nextMonthYear = month === 11 ? year + 1 : year;
  const nextMonth = month === 11 ? 0 : month + 1;
  const days: { date: Date; isCurrentMonth: boolean; isToday: boolean; isDisabled: boolean }[] = [];
  const today = startOfDay(new Date());

  for (let i = 0; i < firstDay; i++) {
    const day = daysInPrevMonth - firstDay + i + 1;
    const date = startOfDay(new Date(prevMonthYear, prevMonth, day));
    days.push({ date, isCurrentMonth: false, isToday: date.getTime() === today.getTime(), isDisabled: isDateDisabled(date) });
  }

  for (let i = 1; i <= daysInMonth; i++) {
    const date = startOfDay(new Date(year, month, i));
    days.push({ date, isCurrentMonth: true, isToday: date.getTime() === today.getTime(), isDisabled: isDateDisabled(date) });
  }

  const currentDaysCount = days.length;
  const remainingDays = (currentDaysCount % 7 === 0) ? 0 : 7 - (currentDaysCount % 7);

  for (let i = 1; i <= remainingDays; i++) {
    const date = startOfDay(new Date(nextMonthYear, nextMonth, i));
    days.push({ date, isCurrentMonth: false, isToday: date.getTime() === today.getTime(), isDisabled: isDateDisabled(date) });
  }

  const weeks = [];
  for (let i = 0; i < days.length; i += 7) {
    weeks.push(days.slice(i, i + 7));
  }
  return weeks;
});

const thaiMonths = computed(() => {
  const months = [];
  for (let i = 0; i < 12; i++) {
    const date = new Date(currentMonth.value.getFullYear(), i, 1);
    months.push({
      value: i,
      thaiName: format(date, 'LLLL', { locale: th }),
      englishName: format(date, 'LLLL')
    });
  }
  return months;
});

const years = computed(() => {
  const currentYear = currentMonth.value.getFullYear();
  const startYear = currentYear - 12;
  const endYear = currentYear + 12;
  const yearsArray = [];

  for (let i = startYear; i <= endYear; i++) {
    yearsArray.push({
      christian: i,
      buddhist: i + 543
    });
  }

  return yearsArray;
});

const weekdays = computed(() => {
  const days = props.firstDayOfWeek === 1
    ? ['จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.', 'อา.']
    : ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'];
  return days;
});

// Display formatters
function formatDate(date: Date | null): string {
  if (!date) return '';

  const yearOffset = props.yearType === 'buddhist' ? 543 : 0;

  if (props.mode === 'date') {
    return format(date, 'd MMMM ', { locale: th }) + (date.getFullYear() + yearOffset);
  } else if (props.mode === 'datetime') {
    return format(date, 'd MMMM ', { locale: th }) + (date.getFullYear() + yearOffset) +
      format(date, ' เวลา HH:mm น.', { locale: th });
  } else {
    return format(date, 'เวลา HH:mm น.', { locale: th });
  }
}

function formatMonthYear(): string {
  const year = currentMonth.value.getFullYear() + (props.yearType === 'buddhist' ? 543 : 0);
  return format(currentMonth.value, 'MMMM ', { locale: th }) + year;
}

// State helpers
function isToday(date: Date): boolean {
  const today = new Date();
  return (
    date.getDate() === today.getDate() &&
    date.getMonth() === today.getMonth() &&
    date.getFullYear() === today.getFullYear()
  );
}

function isDateDisabled(date: Date): boolean {
  if (props.minDate && date < props.minDate) return true;
  if (props.maxDate && date > props.maxDate) return true;
  return false;
}

function isDateSelected(date: Date): boolean {
  if (props.rangeMode === 'single' && selectedDate.value) {
    return (
      date.getDate() === selectedDate.value.getDate() &&
      date.getMonth() === selectedDate.value.getMonth() &&
      date.getFullYear() === selectedDate.value.getFullYear()
    );
  }
  return false;
}

function isDateInRange(date: Date): boolean {
  if (props.rangeMode === 'range' && selectedStartDate.value && selectedEndDate.value) {
    return date >= selectedStartDate.value && date <= selectedEndDate.value;
  }

  if (props.rangeMode === 'range' && selectedStartDate.value && hoveredDate.value && !selectedEndDate.value) {
    return (
      (date >= selectedStartDate.value && date <= hoveredDate.value) ||
      (date <= selectedStartDate.value && date >= hoveredDate.value)
    );
  }

  return false;
}

function isRangeStart(date: Date): boolean {
  if (!selectedStartDate.value) return false;

  return (
    date.getDate() === selectedStartDate.value.getDate() &&
    date.getMonth() === selectedStartDate.value.getMonth() &&
    date.getFullYear() === selectedStartDate.value.getFullYear()
  );
}

function isRangeEnd(date: Date): boolean {
  if (!selectedEndDate.value) return false;

  return (
    date.getDate() === selectedEndDate.value.getDate() &&
    date.getMonth() === selectedEndDate.value.getMonth() &&
    date.getFullYear() === selectedEndDate.value.getFullYear()
  );
}

// Display formatted value
const displayValue = computed(() => {
  if (props.rangeMode === 'single') {
    return formatDate(selectedDate.value);
  } else {
    const start = formatDate(selectedStartDate.value);
    const end = formatDate(selectedEndDate.value);
    if (start && end) {
      return `${start} - ${end}`;
    } else if (start) {
      return start;
    }
    return '';
  }
});

// Event handlers
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

function toggleCalendar() {
  /* if (props.disabled) return;

  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    currentView.value = props.mode === 'time' ? 'time' : 'calendar';
  } */

  if (props.disabled) return;
  const shouldOpen = !isOpen.value;
  isOpen.value = shouldOpen;

  if (shouldOpen) {
    initializeValues(); // Sync state when opening
    currentView.value = props.mode === 'time' ? 'time' : 'calendar';
    const initialDate = props.rangeMode === 'single'
      ? selectedDate.value
      : selectedStartDate.value;
    if (initialDate && (props.mode === 'date' || props.mode === 'datetime')) {
      currentMonth.value = new Date(initialDate.getFullYear(), initialDate.getMonth(), 1);
    } else if (!initialDate) {
      currentMonth.value = new Date(); // Default to current month if no value
    }
  }
}

function selectDate(date: Date) {
  const selectedDay = startOfDay(new Date(date));

  if (props.rangeMode === 'single') {
    const finalDate = setMinutes(setHours(selectedDay, selectedHours.value), selectedMinutes.value);
    selectedDate.value = finalDate;

    if (props.mode === 'date') {
      computedModelValue.value = finalDate;
      isOpen.value = false;
    } else if (props.mode === 'datetime') {
      currentView.value = 'time';
    } else { // time mode (shouldn't be reached from calendar view)
      computedModelValue.value = finalDate;
      isOpen.value = false;
    }
  } else { // rangeMode === 'range'
    if (!selectedStartDate.value || (selectedStartDate.value && selectedEndDate.value)) {
      selectedStartDate.value = selectedDay;
      selectedEndDate.value = null;
      hoveredDate.value = null;
      // Do not close yet, wait for end date selection
    } else {
      const startDate = selectedStartDate.value; // Keep reference
      if (selectedDay < startDate) {
        selectedEndDate.value = startDate;
        selectedStartDate.value = selectedDay;
      } else {
        selectedEndDate.value = selectedDay;
      }
      hoveredDate.value = null;

      // Apply selected time to both start and end
      const finalStartDate = setMinutes(setHours(selectedStartDate.value, selectedHours.value), selectedMinutes.value);
      const finalEndDate = setMinutes(setHours(selectedEndDate.value, selectedHours.value), selectedMinutes.value); // Apply same time, or add separate time selection logic if needed

      // Update internal state with time *before* deciding next step
      selectedStartDate.value = finalStartDate;
      selectedEndDate.value = finalEndDate;

      if (props.mode === 'date') {
        computedModelValue.value = [finalStartDate, finalEndDate];
        isOpen.value = false;
      } else if (props.mode === 'datetime') {
        currentView.value = 'time'; // Go to time view after selecting range
      }
    }
  }
}

function selectMonth(month: number) {
  currentMonth.value = new Date(currentMonth.value.getFullYear(), month, 1);
  currentView.value = 'calendar';
}

function selectYear(year: number) {
  currentMonth.value = new Date(year, currentMonth.value.getMonth(), 1);
  currentView.value = 'month';
}

function confirmTime() {
  if (props.rangeMode === 'single') {
    let baseDate = selectedDate.value;
    // If only time mode, or date wasn't selected yet, use today as base
    if (!baseDate && (props.mode === 'time' || props.mode === 'datetime')) {
      baseDate = startOfDay(new Date()); // Use start of today
    } else if (!baseDate) {
      // Should not happen if flow is correct, but handle defensively
      console.warn("Confirming time without a selected date.");
      isOpen.value = false; // Close as we can't proceed
      return;
    }

    const finalDate = setMinutes(setHours(baseDate, selectedHours.value), selectedMinutes.value);
    selectedDate.value = finalDate; // Update internal state too
    computedModelValue.value = finalDate;

  } else { // rangeMode === 'range'
    if (selectedStartDate.value && selectedEndDate.value) {
      const finalStartDate = setMinutes(setHours(selectedStartDate.value, selectedHours.value), selectedMinutes.value);
      const finalEndDate = setMinutes(setHours(selectedEndDate.value, selectedHours.value), selectedMinutes.value); // Apply same time to both
      selectedStartDate.value = finalStartDate; // Update internal state
      selectedEndDate.value = finalEndDate;     // Update internal state
      computedModelValue.value = [finalStartDate, finalEndDate];
    } else {
      // Handle case where range is not fully selected? Maybe just close or show warning.
      console.warn("Confirming time without a complete date range.");
      // Optionally reset or initialize: initializeFromModelValue();
    }
  }
  isOpen.value = false;
}

function changeMonth(delta: number) {
  currentMonth.value = addMonths(currentMonth.value, delta);
}

function changeYear(delta: number) {
  currentMonth.value = addYears(currentMonth.value, delta);
}

function handleDateHover(date: Date | null) {
  hoveredDate.value = date;
}

function showMonthView() {
  currentView.value = 'month';
}

function showYearView() {
  currentView.value = 'year';
}

function cancelSelection() {
  initializeValues();
  isOpen.value = false;
}

// Generate hours and minutes for time selection
const hours = Array.from({ length: 24 }, (_, i) => i);
const minutes = Array.from({ length: 60 }, (_, i) => i);
</script>

<template>
  <div class="relative w-full">
    <!-- Input field -->
    <div ref="inputRef" @click="toggleCalendar"
      class="border border-gray-300 rounded-md p-2 flex items-center justify-between cursor-pointer w-full"
      :class="{ 'opacity-60 cursor-not-allowed': disabled, 'ring-2 ring-blue-500': isOpen }">
      <div v-if="displayValue" class="text-sm">{{ displayValue }}</div>
      <div v-else class="text-gray-400 text-sm">{{ placeholder }}</div>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
    </div>

    <!-- Dropdown -->
    <div v-if="isOpen" ref="dropdownRef" :style="floatingStyles"
      class="bg-white border border-gray-200 rounded-md shadow-lg p-3 z-50 w-72 sm:w-80 md:w-96 transition-all duration-200"
      @mousedown.prevent>
      <!-- Calendar view -->
      <div v-if="currentView === 'calendar'" class="space-y-3">
        <!-- Header -->
        <div class="flex justify-between items-center mb-2">
          <button @click="changeMonth(-1)" class="p-1 hover:bg-gray-100 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button @click.stop="showMonthView"
            class="font-medium text-gray-700 text-sm px-2 py-1 hover:bg-gray-100 rounded">
            {{ formatMonthYear() }}
          </button>
          <button @click="changeMonth(1)" class="p-1 hover:bg-gray-100 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <!-- Weekdays -->
        <div class="grid grid-cols-7 gap-1 text-center text-xs text-gray-500">
          <div v-for="(day, index) in weekdays" :key="index" class="p-1">
            {{ day }}
          </div>
        </div>

        <!-- Days -->
        <div class="grid">
          <div v-for="(week, weekIndex) in calendarWeeks" :key="weekIndex" class="grid grid-cols-7">
            <div v-if="mode === 'datetime'" v-for="(day, dayIndex) in week" :key="`${weekIndex}-${dayIndex}-datetime`"
              @click.stop="!day.isDisabled && selectDate(day.date)" @mouseenter="handleDateHover(day.date)"
              @mouseleave="handleDateHover(null)"
              class="hover:bg-emerald-500 hover:text-white p-1 text-center text-sm rounded-md cursor-pointer transition-colors duration-200"
              :class="{
                'text-gray-400': !day.isCurrentMonth,
                'font-medium': day.isCurrentMonth,
                'bg-emerald-500 text-white': isDateSelected(day.date) || isRangeStart(day.date) || isRangeEnd(day.date),
                'bg-emerald-100': isDateInRange(day.date) && !isRangeStart(day.date) && !isRangeEnd(day.date),
                'ring-2 ring-emerald-300': day.isToday && !isDateSelected(day.date),
                'opacity-40 cursor-not-allowed': day.isDisabled
              }">
              {{ day.date.getDate() }}
            </div>
            <div v-else v-for="(day, dayIndex) in week" :key="`${weekIndex}-${dayIndex}`"
              @click="!day.isDisabled && selectDate(day.date)" @mouseenter="handleDateHover(day.date)"
              @mouseleave="handleDateHover(null)"
              class="hover:bg-emerald-500 hover:text-white p-1 text-center text-sm rounded-md cursor-pointer transition-colors duration-200"
              :class="{
                'text-gray-400': !day.isCurrentMonth,
                'font-medium': day.isCurrentMonth,
                'bg-emerald-500 text-white': isDateSelected(day.date) || isRangeStart(day.date) || isRangeEnd(day.date),
                'bg-emerald-100': isDateInRange(day.date) && !isRangeStart(day.date) && !isRangeEnd(day.date),
                'ring-2 ring-emerald-300': day.isToday && !isDateSelected(day.date),
                'opacity-40 cursor-not-allowed': day.isDisabled
              }">
              {{ day.date.getDate() }}
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-between pt-3 border-t border-gray-200">
          <button @click="cancelSelection" class="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded">
            ยกเลิก
          </button>
          <div class="space-x-2">
            <button v-if="mode === 'datetime'" @click.stop="currentView = 'time'"
              class="px-3 py-1 text-sm bg-blue-100 text-blue-600 hover:bg-blue-200 rounded">
              ตั้งเวลา
            </button>
            <button v-if="mode === 'date'" @click="isOpen = false"
              class="px-3 py-1 text-sm bg-blue-500 text-white hover:bg-blue-600 rounded">
              ตกลง
            </button>
          </div>
        </div>
      </div>

      <!-- Month selection view -->
      <div v-else-if="currentView === 'month'" class="space-y-3">
        <div class="flex justify-between items-center mb-2">
          <button @click.stop="changeYear(-1)" class="p-1 hover:bg-gray-100 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button @click.stop="showYearView" class="font-medium text-gray-700 px-2 py-1 hover:bg-gray-100 rounded">
            {{ currentMonth.getFullYear() + (yearType === 'buddhist' ? 543 : 0) }}
          </button>
          <button @click.stop="changeYear(1)" class="p-1 hover:bg-gray-100 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <div class="grid grid-cols-3 gap-2">
          <button v-for="month in thaiMonths" :key="month.value" @click.stop="selectMonth(month.value)"
            class="p-2 text-sm rounded hover:bg-blue-100 text-center" :class="{
              'bg-blue-500 text-white': month.value === currentMonth.getMonth()
            }">
            {{ yearType === 'buddhist' ? month.thaiName : month.englishName }}
          </button>
        </div>
      </div>

      <!-- Year selection view -->
      <div v-else-if="currentView === 'year'" class="space-y-3">
        <div class="flex justify-between items-center mb-2">
          <button @click.stop="changeYear(-12)" class="p-1 hover:bg-gray-100 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <span class="font-medium text-gray-700">
            {{ yearType === 'buddhist' ? years[0].buddhist : years[0].christian }} -
            {{ yearType === 'buddhist' ? years[years.length - 1].buddhist : years[years.length - 1].christian }}
          </span>
          <button @click.stop="changeYear(12)" class="p-1 hover:bg-gray-100 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <div class="grid grid-cols-3 gap-2">
          <button v-for="year in years" :key="yearType === 'buddhist' ? year.buddhist : year.christian"
            @click.stop="selectYear(year.christian)" class="p-2 text-sm rounded hover:bg-blue-100 text-center" :class="{
              'bg-blue-500 text-white': year.christian === currentMonth.getFullYear()
            }">
            {{ yearType === 'buddhist' ? year.buddhist : year.christian }}
          </button>
        </div>
      </div>

      <!-- Time selection view -->
      <div v-else-if="currentView === 'time'" class="space-y-3">
        <div class="text-center font-medium text-gray-700 mb-3">
          ตั้งเวลา
        </div>

        <div class="flex items-center justify-center space-x-2">
          <div class="flex flex-col items-center">
            <span class="text-xs text-gray-500 mb-1">ชั่วโมง</span>
            <select v-model.number="selectedHours" class="p-2 border border-gray-300 rounded text-center">
              <option v-for="hourValue in hours" :key="hourValue" :value="hourValue">
                {{ hourValue.toString().padStart(2, '0') }}
              </option>
            </select>
          </div>

          <span class="text-2xl">:</span>

          <div class="flex flex-col items-center">
            <span class="text-xs text-gray-500 mb-1">นาที</span>
            <select v-model.number="selectedMinutes" class="p-2 border border-gray-300 rounded text-center">
              <option v-for="minuteValue in minutes" :key="minuteValue" :value="minuteValue">
                {{ minuteValue.toString().padStart(2, '0') }}
              </option>
            </select>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-between pt-3 border-t border-gray-200">
          <button @click.stop="currentView = 'calendar'"
            class="px-3 py-1 text-sm text-gray-600 hover:bg-gray-100 rounded">
            กลับ
          </button>
          <button @click="confirmTime" class="px-3 py-1 text-sm bg-blue-500 text-white hover:bg-blue-600 rounded">
            ตกลง
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss";

/* Support touch screen devices */
@media (pointer: coarse) {

  button,
  select {
    min-height: 44px;
    min-width: 44px;
  }
}

/* Additional responsive adjustments */
@media (max-width: 640px) {
  .grid-cols-7>div {
    @apply p-1 text-xs;
  }
}
</style>