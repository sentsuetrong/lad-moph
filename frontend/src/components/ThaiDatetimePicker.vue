<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { format, addMonths, addYears, setHours, setMinutes } from 'date-fns';
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
  if (!props.modelValue) {
    selectedDate.value = null;
    selectedStartDate.value = null;
    selectedEndDate.value = null;
    return;
  }

  if (props.rangeMode === 'single') {
    selectedDate.value = props.modelValue as Date;
    if (selectedDate.value) {
      currentMonth.value = new Date(selectedDate.value);
      if (props.mode === 'datetime' || props.mode === 'time') {
        selectedHours.value = selectedDate.value.getHours();
        selectedMinutes.value = selectedDate.value.getMinutes();
      }
    }
  } else {
    const [start, end] = props.modelValue as [Date | null, Date | null];
    selectedStartDate.value = start;
    selectedEndDate.value = end;
    if (selectedStartDate.value) {
      currentMonth.value = new Date(selectedStartDate.value);
    }
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

  // Adjust for first day of week
  if (props.firstDayOfWeek === 1) {
    firstDay = firstDay === 0 ? 6 : firstDay - 1;
  }

  const prevMonthYear = month === 0 ? year - 1 : year;
  const prevMonth = month === 0 ? 11 : month - 1;
  const daysInPrevMonth = getDaysInMonth(prevMonthYear, prevMonth);

  const nextMonthYear = month === 11 ? year + 1 : year;
  const nextMonth = month === 11 ? 0 : month + 1;

  const days: { date: Date; isCurrentMonth: boolean; isToday: boolean; isDisabled: boolean }[] = [];

  // Days from previous month
  for (let i = 0; i < firstDay; i++) {
    const day = daysInPrevMonth - firstDay + i + 1;
    const date = new Date(prevMonthYear, prevMonth, day);
    days.push({
      date,
      isCurrentMonth: false,
      isToday: isToday(date),
      isDisabled: isDateDisabled(date)
    });
  }

  // Days from current month
  for (let i = 1; i <= daysInMonth; i++) {
    const date = new Date(year, month, i);
    days.push({
      date,
      isCurrentMonth: true,
      isToday: isToday(date),
      isDisabled: isDateDisabled(date)
    });
  }

  // Days from next month
  const remainingDays = 42 - days.length; // 6 rows of 7 days
  for (let i = 1; i <= remainingDays; i++) {
    const date = new Date(nextMonthYear, nextMonth, i);
    days.push({
      date,
      isCurrentMonth: false,
      isToday: isToday(date),
      isDisabled: isDateDisabled(date)
    });
  }

  // Group by weeks
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
  const startYear = currentYear - 10;
  const endYear = currentYear + 10;
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
  if (props.disabled) return;

  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    currentView.value = props.mode === 'time' ? 'time' : 'calendar';
  }
}

function selectDate(date: Date) {
  if (props.rangeMode === 'single') {
    selectedDate.value = new Date(date);

    if (props.mode === 'datetime' || props.mode === 'time') {
      selectedDate.value = setHours(selectedDate.value, selectedHours.value);
      selectedDate.value = setMinutes(selectedDate.value, selectedMinutes.value);
    }

    if (props.mode === 'date') {
      emit('update:modelValue', selectedDate.value);
      emit('change', selectedDate.value);
      isOpen.value = false;
    } else if (props.mode === 'datetime') {
      currentView.value = 'time';
    }
  } else {
    if (!selectedStartDate.value || (selectedStartDate.value && selectedEndDate.value)) {
      selectedStartDate.value = new Date(date);
      selectedEndDate.value = null;
    } else {
      if (date < selectedStartDate.value) {
        selectedEndDate.value = selectedStartDate.value;
        selectedStartDate.value = new Date(date);
      } else {
        selectedEndDate.value = new Date(date);
      }

      if (props.mode === 'date') {
        emit('update:modelValue', [selectedStartDate.value, selectedEndDate.value]);
        emit('change', [selectedStartDate.value, selectedEndDate.value]);
        isOpen.value = false;
      } else if (props.mode === 'datetime') {
        currentView.value = 'time';
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
  if (props.rangeMode === 'single' && selectedDate.value) {
    selectedDate.value = setHours(selectedDate.value, selectedHours.value);
    selectedDate.value = setMinutes(selectedDate.value, selectedMinutes.value);
    emit('update:modelValue', selectedDate.value);
    emit('change', selectedDate.value);
  } else if (props.rangeMode === 'range' && selectedStartDate.value && selectedEndDate.value) {
    selectedStartDate.value = setHours(selectedStartDate.value, selectedHours.value);
    selectedStartDate.value = setMinutes(selectedStartDate.value, selectedMinutes.value);
    selectedEndDate.value = setHours(selectedEndDate.value, selectedHours.value);
    selectedEndDate.value = setMinutes(selectedEndDate.value, selectedMinutes.value);
    emit('update:modelValue', [selectedStartDate.value, selectedEndDate.value]);
    emit('change', [selectedStartDate.value, selectedEndDate.value]);
  }

  isOpen.value = false;
}

function changeMonth(delta: number) {
  currentMonth.value = addMonths(currentMonth.value, delta);
}

function changeYear(delta: number) {
  currentMonth.value = addYears(currentMonth.value, delta);
}

function handleDateHover(date: Date) {
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
          <button @click="showMonthView" class="font-medium text-gray-700 text-sm px-2 py-1 hover:bg-gray-100 rounded">
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
          <div v-for="(week, weekIndex) in calendarWeeks" :key="weekIndex" class="grid grid-cols-7 gap-1">
            <div v-for="(day, dayIndex) in week" :key="`${weekIndex}-${dayIndex}`"
              @click="!day.isDisabled && selectDate(day.date)" @mouseenter="handleDateHover(day.date)"
              class="p-1 text-center text-sm rounded-md cursor-pointer transition-colors duration-200" :class="{
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
            <button v-if="mode === 'datetime'" @click="currentView = 'time'"
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
          <button @click="changeYear(-1)" class="p-1 hover:bg-gray-100 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button @click="showYearView" class="font-medium text-gray-700 px-2 py-1 hover:bg-gray-100 rounded">
            {{ currentMonth.getFullYear() + (yearType === 'buddhist' ? 543 : 0) }}
          </button>
          <button @click="changeYear(1)" class="p-1 hover:bg-gray-100 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <div class="grid grid-cols-3 gap-2">
          <button v-for="month in thaiMonths" :key="month.value" @click="selectMonth(month.value)"
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
          <button @click="changeYear(-10)" class="p-1 hover:bg-gray-100 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <span class="font-medium text-gray-700">
            {{ yearType === 'buddhist' ? years[0].buddhist : years[0].christian }} -
            {{ yearType === 'buddhist' ? years[years.length - 1].buddhist : years[years.length - 1].christian }}
          </span>
          <button @click="changeYear(10)" class="p-1 hover:bg-gray-100 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <div class="grid grid-cols-3 gap-2">
          <button v-for="year in years" :key="yearType === 'buddhist' ? year.buddhist : year.christian"
            @click="selectYear(year.christian)" class="p-2 text-sm rounded hover:bg-blue-100 text-center" :class="{
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
            <select v-model="selectedHours" class="p-2 border border-gray-300 rounded text-center">
              <option v-for="hour in hours" :key="hour" :value="hour">
                {{ hour.toString().padStart(2, '0') }}
              </option>
            </select>
          </div>

          <span class="text-2xl">:</span>

          <div class="flex flex-col items-center">
            <span class="text-xs text-gray-500 mb-1">นาที</span>
            <select v-model="selectedMinutes" class="p-2 border border-gray-300 rounded text-center">
              <option v-for="minute in minutes" :key="minute" :value="minute">
                {{ minute.toString().padStart(2, '0') }}
              </option>
            </select>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-between pt-3 border-t border-gray-200">
          <button @click="currentView = 'calendar'" class="px-3 py-1 text-sm text-gray-600 hover:bg-gray-100 rounded">
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