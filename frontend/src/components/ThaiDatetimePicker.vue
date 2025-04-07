<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { format, addMonths, addYears, setHours, setMinutes, startOfDay, isSameDay } from 'date-fns';
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

// Selected dates & Time
const selectedDate = ref<Date | null>(null); // For single mode
const selectedStartDate = ref<Date | null>(null); // For range mode start
const selectedEndDate = ref<Date | null>(null); // For range mode end
const selectedHours = ref(0); // Stores the hour selection
const selectedMinutes = ref(0); // Stores the minute selection

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

// Watch for external changes to modelValue
watch(() => props.modelValue, initializeValues, { deep: true }); // Use deep watch for arrays

function initializeValues() {
  const value = props.modelValue;
  let initialTimeSource: Date | null = null;

  if (props.rangeMode === 'single') {
    const dateValue = value as Date | null;
    selectedDate.value = dateValue ? new Date(dateValue) : null;
    selectedStartDate.value = null;
    selectedEndDate.value = null;
    initialTimeSource = selectedDate.value; // Use the single date for initial time

    if (selectedDate.value) {
      // Only change month view if it's different or dropdown is closed
      if (!isOpen.value || !isSameDayMonthYear(selectedDate.value, currentMonth.value)) {
        currentMonth.value = new Date(selectedDate.value.getFullYear(), selectedDate.value.getMonth(), 1);
      }
    } else if (!isOpen.value) {
      // Reset to current month if no value and not open
      // currentMonth.value = new Date(); // Optional: Reset view if needed
    }

  } else { // rangeMode === 'range'
    const rangeValue = value as [Date | null, Date | null] | null;
    const start = rangeValue?.[0];
    const end = rangeValue?.[1];

    selectedStartDate.value = start ? new Date(start) : null;
    selectedEndDate.value = end ? new Date(end) : null;
    selectedDate.value = null;
    initialTimeSource = selectedStartDate.value; // Use the start date for initial time in range mode

    if (selectedStartDate.value) {
      // Only change month view if it's different or dropdown is closed
      if (!isOpen.value || !isSameDayMonthYear(selectedStartDate.value, currentMonth.value)) {
        currentMonth.value = new Date(selectedStartDate.value.getFullYear(), selectedStartDate.value.getMonth(), 1);
      }
    } else if (!isOpen.value) {
      // Reset to current month if no value and not open
      // currentMonth.value = new Date(); // Optional: Reset view if needed
    }
  }

  // Initialize time based on the source (start date for range, selected date for single)
  if (initialTimeSource && (props.mode === 'datetime' || props.mode === 'time')) {
    selectedHours.value = initialTimeSource.getHours();
    selectedMinutes.value = initialTimeSource.getMinutes();
  } else {
    // Default time if no date selected or mode is 'date'
    selectedHours.value = 0;
    selectedMinutes.value = 0;
  }
}

// Helper to check if two dates are in the same month and year
function isSameDayMonthYear(date1: Date, date2: Date): boolean {
  return date1.getMonth() === date2.getMonth() && date1.getFullYear() === date2.getFullYear();
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
  // ... (calendar generation logic remains the same) ...
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
    days.push({ date, isCurrentMonth: false, isToday: isSameDay(date, today), isDisabled: isDateDisabled(date) });
  }

  for (let i = 1; i <= daysInMonth; i++) {
    const date = startOfDay(new Date(year, month, i));
    days.push({ date, isCurrentMonth: true, isToday: isSameDay(date, today), isDisabled: isDateDisabled(date) });
  }

  const currentDaysCount = days.length;
  const remainingDays = (currentDaysCount % 7 === 0) ? 0 : 7 - (currentDaysCount % 7);

  for (let i = 1; i <= remainingDays; i++) {
    const date = startOfDay(new Date(nextMonthYear, nextMonth, i));
    days.push({ date, isCurrentMonth: false, isToday: isSameDay(date, today), isDisabled: isDateDisabled(date) });
  }

  const weeks = [];
  for (let i = 0; i < days.length; i += 7) {
    weeks.push(days.slice(i, i + 7));
  }
  return weeks;
});

const thaiMonths = computed(() => {
  // ... (thai month generation remains the same) ...
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
  // ... (year generation remains the same) ...
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
  // ... (weekday generation remains the same) ...
  const days = props.firstDayOfWeek === 1
    ? ['จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.', 'อา.']
    : ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'];
  return days;
});

// Display formatters
function formatDate(date: Date | null): string {
  if (!date) return '';

  const yearOffset = props.yearType === 'buddhist' ? 543 : 0;
  const adjustedDate = new Date(date); // Clone to avoid mutating original

  if (props.mode === 'date') {
    return format(adjustedDate, 'd MMMM ', { locale: th }) + (adjustedDate.getFullYear() + yearOffset);
  } else if (props.mode === 'datetime') {
    return format(adjustedDate, 'd MMMM ', { locale: th }) + (adjustedDate.getFullYear() + yearOffset) +
      format(adjustedDate, ' HH:mm น.', { locale: th }); // Add space before time
  } else { // time mode
    // For time mode only, we might not have a date part if modelValue starts null
    const baseDate = date ? adjustedDate : setMinutes(setHours(new Date(), (date as Date)?.getHours() ?? 0), (date as Date)?.getMinutes() ?? 0);
    return format(baseDate, 'HH:mm น.', { locale: th });
  }
}

function formatMonthYear(): string {
  const year = currentMonth.value.getFullYear() + (props.yearType === 'buddhist' ? 543 : 0);
  return format(currentMonth.value, 'MMMM ', { locale: th }) + year;
}

// State helpers
function isDateDisabled(date: Date): boolean {
  const dayOnly = startOfDay(date);
  if (props.minDate && dayOnly < startOfDay(props.minDate)) return true;
  if (props.maxDate && dayOnly > startOfDay(props.maxDate)) return true;
  return false;
}

function isDateSelected(date: Date): boolean {
  if (props.rangeMode === 'single' && selectedDate.value) {
    return isSameDay(date, selectedDate.value);
  }
  return false;
}

function isDateInRange(date: Date): boolean {
  const dayOnly = startOfDay(date);
  if (props.rangeMode === 'range' && selectedStartDate.value && selectedEndDate.value) {
    const startDay = startOfDay(selectedStartDate.value);
    const endDay = startOfDay(selectedEndDate.value);
    return dayOnly > startDay && dayOnly < endDay; // Exclude start/end itself
  }

  // Hover effect
  if (props.rangeMode === 'range' && selectedStartDate.value && hoveredDate.value && !selectedEndDate.value) {
    const startDay = startOfDay(selectedStartDate.value);
    const hoverDay = startOfDay(hoveredDate.value);
    const dayOnly = startOfDay(date);
    return (
      (dayOnly > startDay && dayOnly <= hoverDay) ||
      (dayOnly < startDay && dayOnly >= hoverDay)
    );
  }

  return false;
}

function isRangeStart(date: Date): boolean {
  if (!selectedStartDate.value) return false;
  return isSameDay(date, selectedStartDate.value);
}

function isRangeEnd(date: Date): boolean {
  if (!selectedEndDate.value) return false;
  return isSameDay(date, selectedEndDate.value);
}

// Display formatted value in the input
const displayValue = computed(() => {
  if (props.rangeMode === 'single') {
    return formatDate(selectedDate.value);
  } else {
    const start = formatDate(selectedStartDate.value);
    const end = formatDate(selectedEndDate.value);
    if (start && end) {
      // Check if start and end are on the same day for datetime mode display
      if (props.mode === 'datetime' && selectedStartDate.value && selectedEndDate.value && isSameDay(selectedStartDate.value, selectedEndDate.value)) {
        const yearOffset = props.yearType === 'buddhist' ? 543 : 0;
        const datePart = format(selectedStartDate.value, 'd MMMM ', { locale: th }) + (selectedStartDate.value.getFullYear() + yearOffset);
        const startTime = format(selectedStartDate.value, 'HH:mm');
        const endTime = format(selectedEndDate.value, 'HH:mm');
        return `${datePart} ${startTime} - ${endTime} น.`;
      }
      // Default range display
      return `${start} - ${end}`;
    } else if (start) {
      return start; // Show start date if end is not yet selected
    }
    return ''; // Empty if no start date
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
    // If clicking outside cancels the selection process
    // cancelSelection(); // Option 1: Reset everything
    isOpen.value = false; // Option 2: Just close the dropdown
  }
}

function toggleCalendar() {
  if (props.disabled) return;
  const shouldOpen = !isOpen.value;
  isOpen.value = shouldOpen;

  if (shouldOpen) {
    initializeValues(); // Refresh state from modelValue when opening
    // Determine initial view
    if (props.mode === 'time') {
      currentView.value = 'time';
    } else {
      currentView.value = 'calendar';
      // Ensure currentMonth view is relevant
      const initialDate = props.rangeMode === 'single'
        ? selectedDate.value
        : selectedStartDate.value; // Base view on start date for range
      if (initialDate) {
        currentMonth.value = new Date(initialDate.getFullYear(), initialDate.getMonth(), 1);
      } else {
        currentMonth.value = new Date(); // Default to current month if no value
      }
    }
  }
}

function selectDate(date: Date) {
  const selectedDay = startOfDay(new Date(date)); // Use startOfDay for comparisons and setting date part

  if (props.rangeMode === 'single') {
    // Set the date part first
    selectedDate.value = selectedDay;

    if (props.mode === 'date') {
      // Apply immediately and close for date mode
      computedModelValue.value = selectedDay;
      isOpen.value = false;
    } else if (props.mode === 'datetime') {
      // Move to time view, keep internal selectedDate (date part only for now)
      currentView.value = 'time';
      // Time will be applied in confirmTime
    }
    // 'time' mode doesn't use calendar view

  } else { // rangeMode === 'range'
    if (!selectedStartDate.value || (selectedStartDate.value && selectedEndDate.value)) {
      // Start new selection or restart after finishing a range
      selectedStartDate.value = selectedDay;
      selectedEndDate.value = null; // Clear end date
      hoveredDate.value = null;
      // Reset time to default (or keep previous?) - let's reset to 00:00 for clarity
      selectedHours.value = 0;
      selectedMinutes.value = 0;
      // Keep calendar open, DO NOT switch view yet
    } else {
      // Selecting the end date
      const startDate = selectedStartDate.value; // Keep reference to start date part

      if (selectedDay < startDate) {
        // If end date is before start date, swap them
        selectedEndDate.value = startDate;
        selectedStartDate.value = selectedDay;
      } else {
        selectedEndDate.value = selectedDay;
      }
      hoveredDate.value = null;

      // **CRITICAL CHANGE for Range Datetime:**
      // DO NOT switch to 'time' view here. Stay in 'calendar'.
      // The user will click the "ตั้งเวลา" or "ตกลง" button in the calendar footer.

      // If mode is 'date', the user can confirm directly from the calendar
      if (props.mode === 'date') {
        // Ready to confirm (via footer button)
      } else if (props.mode === 'datetime') {
        // Ready to go to time view (via footer button)
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
  currentView.value = 'month'; // Go back to month view after selecting year
}

// **NEW:** Function to confirm selection directly from Calendar (for date modes)
function confirmSelectionFromCalendar() {
  if (props.mode === 'date') {
    if (props.rangeMode === 'single' && selectedDate.value) {
      computedModelValue.value = selectedDate.value;
      isOpen.value = false;
    } else if (props.rangeMode === 'range' && selectedStartDate.value && selectedEndDate.value) {
      computedModelValue.value = [selectedStartDate.value, selectedEndDate.value];
      isOpen.value = false;
    }
  }
  // For datetime mode, this button shouldn't be the final confirm, it should lead to time view.
}


// Function called when clicking "ตกลง" in the TIME VIEW
function confirmTime() {
  if (props.rangeMode === 'single') {
    let baseDate = selectedDate.value; // Should have the date part from selectDate

    // If only time mode, or somehow date wasn't selected, use today as base
    if (!baseDate && (props.mode === 'time' || props.mode === 'datetime')) {
      // If mode is 'time' only, baseDate can be null. Use current time as base.
      baseDate = new Date(); // Use current date and time as initial base for time-only mode
    } else if (!baseDate) {
      console.warn("DatePicker: Confirming time without a selected date in single mode.");
      // Maybe default to today or just close without updating
      isOpen.value = false;
      return;
    }

    // Apply selected time to the base date
    const finalDate = setMinutes(setHours(baseDate, selectedHours.value), selectedMinutes.value);
    selectedDate.value = finalDate; // Update internal state fully
    computedModelValue.value = finalDate;

  } else { // rangeMode === 'range'
    // Ensure both start and end dates are selected before confirming time
    if (selectedStartDate.value && selectedEndDate.value) {
      // Apply the SAME selected time to BOTH start and end date parts
      const finalStartDate = setMinutes(setHours(selectedStartDate.value, selectedHours.value), selectedMinutes.value);
      const finalEndDate = setMinutes(setHours(selectedEndDate.value, selectedHours.value), selectedMinutes.value);

      // Update internal state fully
      selectedStartDate.value = finalStartDate;
      selectedEndDate.value = finalEndDate;

      // Emit the final range with time
      computedModelValue.value = [finalStartDate, finalEndDate];
    } else {
      console.warn("DatePicker: Confirming time without a complete date range selected.");
      // Optionally reset or just close without updating
    }
  }
  isOpen.value = false; // Close dropdown after confirming time
}

function changeMonth(delta: number) {
  currentMonth.value = addMonths(currentMonth.value, delta);
}

function changeYear(delta: number) {
  // Adjust the step for year view navigation if needed
  const step = currentView.value === 'year' ? 12 : 1;
  currentMonth.value = addYears(currentMonth.value, delta * step);
}

function handleDateHover(date: Date | null) {
  if (props.rangeMode === 'range' && selectedStartDate.value && !selectedEndDate.value) {
    hoveredDate.value = date ? startOfDay(date) : null;
  } else {
    hoveredDate.value = null; // Clear hover if not in range selection phase
  }
}


function showMonthView() {
  currentView.value = 'month';
}

function showYearView() {
  currentView.value = 'year';
}

function cancelSelection() {
  isOpen.value = false;
  // Re-initialize to reflect the original modelValue state
  initializeValues();
}

// Generate hours and minutes for time selection
const hours = Array.from({ length: 24 }, (_, i) => i);
const minutes = Array.from({ length: 60 }, (_, i) => i);

// **NEW:** Computed property to control visibility of the "Confirm" button in the CALENDAR footer
const showCalendarConfirmButton = computed(() => {
  // Only show for 'date' mode when a selection is complete
  if (props.mode !== 'date') return false;
  if (props.rangeMode === 'single') {
    return !!selectedDate.value;
  } else { // rangeMode === 'range'
    return !!selectedStartDate.value && !!selectedEndDate.value;
  }
});

// **NEW:** Computed property to control visibility of the "Set Time" button in the CALENDAR footer
const showCalendarSetTimeButton = computed(() => {
  // Only show for 'datetime' mode when a selection is complete
  if (props.mode !== 'datetime') return false;
  if (props.rangeMode === 'single') {
    return !!selectedDate.value;
  } else { // rangeMode === 'range'
    return !!selectedStartDate.value && !!selectedEndDate.value;
  }
});
</script>

<template>
  <div class="relative w-full">
    <div ref="inputRef" @click="toggleCalendar"
      class="border border-gray-300 rounded-md p-2 flex items-center justify-between cursor-pointer w-full min-h-[40px]"
      :class="{ 'bg-gray-100 opacity-60 cursor-not-allowed': disabled, 'ring-2 ring-blue-500': isOpen }">
      <div v-if="displayValue" class="text-sm flex-1 mr-1">{{ displayValue }}</div>
      <div v-else class="text-gray-400 text-sm flex-1 mr-1">{{ placeholder }}</div>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 flex-shrink-0" fill="none"
        viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
    </div>

    <div v-if="isOpen" ref="dropdownRef" :style="floatingStyles"
      class="bg-white border border-gray-200 rounded-md shadow-lg p-3 z-50 w-72 sm:w-80 md:w-96 transition-opacity duration-200">
      <div v-if="currentView === 'calendar'" class="space-y-3">
        <div class="flex justify-between items-center mb-2">
          <button @click="changeMonth(-1)" aria-label="Previous month" class="p-1.5 hover:bg-gray-100 rounded-full">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button @click.stop="showMonthView"
            class="font-semibold text-gray-700 text-sm px-2 py-1 hover:bg-gray-100 rounded">
            {{ formatMonthYear() }}
          </button>
          <button @click="changeMonth(1)" aria-label="Next month" class="p-1.5 hover:bg-gray-100 rounded-full">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <div class="grid grid-cols-7 gap-1 text-center text-xs text-gray-500 font-medium">
          <div v-for="(day, index) in weekdays" :key="index" class="p-1">
            {{ day }}
          </div>
        </div>

        <div class="grid">
          <div v-for="(week, weekIndex) in calendarWeeks" :key="`week-${weekIndex}`" class="grid grid-cols-7">
            <div v-for="(day, _) in week" :key="day.date.toISOString().substring(0, 10)"
              @click.stop="!day.isDisabled && selectDate(day.date)" @mouseenter="handleDateHover(day.date)"
              @mouseleave="handleDateHover(null)"
              class="flex items-center justify-center h-8 w-full p-1 text-center text-sm rounded-md transition-colors duration-150"
              :class="{
                'cursor-pointer': !day.isDisabled,
                'hover:bg-emerald-100': !day.isDisabled && !isDateSelected(day.date) && !isRangeStart(day.date) && !isRangeEnd(day.date), // Hover only if not selected
                'text-gray-400': !day.isCurrentMonth,
                'font-medium text-gray-700': day.isCurrentMonth,
                // Selection styles
                'bg-emerald-500 text-white hover:bg-emerald-600': isDateSelected(day.date) || isRangeStart(day.date) || isRangeEnd(day.date),
                // In-range styles (excluding start/end)
                'bg-emerald-50 text-emerald-700': isDateInRange(day.date),
                // Start/End specific range hover styles (slightly different background)
                'bg-emerald-100 text-emerald-800': (rangeMode === 'range' && selectedStartDate && !selectedEndDate && hoveredDate && (isSameDay(day.date, selectedStartDate) || isSameDay(day.date, hoveredDate))),

                'ring-1 ring-emerald-400 ring-inset': day.isToday && !isDateSelected(day.date) && !isRangeStart(day.date) && !isRangeEnd(day.date), // Today marker
                'opacity-50 cursor-not-allowed text-gray-400 pointer-events-none': day.isDisabled
              }">
              {{ day.date.getDate() }}
            </div>
          </div>
        </div>


        <div class="flex justify-between items-center pt-3 border-t border-gray-200 mt-2">
          <button @click="cancelSelection" class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-md">
            ยกเลิก
          </button>
          <div class="space-x-2">
            <button v-if="showCalendarSetTimeButton" @click.stop="currentView = 'time'"
              class="px-3 py-1.5 text-sm bg-blue-100 text-blue-700 font-medium hover:bg-blue-200 rounded-md">
              ตั้งเวลา
            </button>
            <button v-if="showCalendarConfirmButton" @click="confirmSelectionFromCalendar"
              class="px-3 py-1.5 text-sm bg-emerald-500 text-white font-medium hover:bg-emerald-600 rounded-md">
              ตกลง
            </button>
          </div>
        </div>
      </div>

      <div v-else-if="currentView === 'month'" class="space-y-3">
        <div class="flex justify-between items-center mb-2">
          <button @click.stop="changeYear(-1)" aria-label="Previous year" class="p-1.5 hover:bg-gray-100 rounded-full">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button @click.stop="showYearView" class="font-semibold text-gray-700 px-2 py-1 hover:bg-gray-100 rounded">
            {{ currentMonth.getFullYear() + (yearType === 'buddhist' ? 543 : 0) }}
          </button>
          <button @click.stop="changeYear(1)" aria-label="Next year" class="p-1.5 hover:bg-gray-100 rounded-full">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <div class="grid grid-cols-3 gap-2">
          <button v-for="month in thaiMonths" :key="month.value" @click.stop="selectMonth(month.value)"
            class="p-2 text-sm rounded hover:bg-blue-100 text-center transition-colors duration-150" :class="{
              'bg-blue-500 text-white hover:bg-blue-600': month.value === currentMonth.getMonth(),
              'bg-white text-gray-700': month.value !== currentMonth.getMonth()
            }">
            {{ yearType === 'buddhist' ? month.thaiName : month.englishName }}
          </button>
        </div>
        <div class="flex justify-end pt-3 border-t border-gray-200 mt-2">
          <button @click.stop="currentView = 'calendar'"
            class="px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md">
            กลับ
          </button>
        </div>
      </div>

      <div v-else-if="currentView === 'year'" class="space-y-3">
        <div class="flex justify-between items-center mb-2">
          <button @click.stop="changeYear(-1)" aria-label="Previous year range"
            class="p-1.5 hover:bg-gray-100 rounded-full">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
          </button>
          <span class="font-semibold text-gray-700 text-sm">
            {{ yearType === 'buddhist' ? years[0].buddhist : years[0].christian }} -
            {{ yearType === 'buddhist' ? years[years.length - 1].buddhist : years[years.length - 1].christian }}
          </span>
          <button @click.stop="changeYear(1)" aria-label="Next year range" class="p-1.5 hover:bg-gray-100 rounded-full">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <div class="grid grid-cols-4 gap-2"> <button v-for="year in years"
            :key="yearType === 'buddhist' ? year.buddhist : year.christian" @click.stop="selectYear(year.christian)"
            class="p-2 text-sm rounded hover:bg-blue-100 text-center transition-colors duration-150" :class="{
              'bg-blue-500 text-white hover:bg-blue-600': year.christian === currentMonth.getFullYear(),
              'bg-white text-gray-700': year.christian !== currentMonth.getFullYear()
            }">
            {{ yearType === 'buddhist' ? year.buddhist : year.christian }}
          </button>
        </div>
        <div class="flex justify-end pt-3 border-t border-gray-200 mt-2">
          <button @click.stop="currentView = 'month'"
            class="px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md">
            กลับ
          </button>
        </div>
      </div>

      <div v-else-if="currentView === 'time'" class="space-y-4">
        <div class="text-center font-semibold text-gray-700">
          ตั้งเวลา
          <div v-if="rangeMode === 'single' && selectedDate && mode === 'datetime'"
            class="text-xs font-normal text-gray-500 mt-1">
            {{ formatDate(selectedDate)?.split(' ')[0] }} {{ formatDate(selectedDate)?.split(' ')[1] }} {{
              formatDate(selectedDate)?.split(' ')[2] }}
          </div>
          <div v-if="rangeMode === 'range' && selectedStartDate && selectedEndDate && mode === 'datetime'"
            class="text-xs font-normal text-gray-500 mt-1">
            {{ formatDate(selectedStartDate)?.split(' ')[0] }} {{ formatDate(selectedStartDate)?.split(' ')[1] }} {{
              formatDate(selectedStartDate)?.split(' ')[2] }} - {{ formatDate(selectedEndDate)?.split(' ')[0] }} {{
              formatDate(selectedEndDate)?.split(' ')[1] }} {{ formatDate(selectedEndDate)?.split(' ')[2] }}
          </div>
        </div>

        <div class="flex items-center justify-center space-x-3">
          <div class="flex flex-col items-center">
            <label for="select-hours" class="text-xs text-gray-500 mb-1">ชั่วโมง</label>
            <select id="select-hours" v-model.number="selectedHours"
              class="w-16 p-2 border border-gray-300 rounded text-center appearance-none focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
              <option v-for="hourValue in hours" :key="`hr-${hourValue}`" :value="hourValue">
                {{ hourValue.toString().padStart(2, '0') }}
              </option>
            </select>
          </div>

          <span class="text-2xl font-light text-gray-400 pb-3">:</span>
          <div class="flex flex-col items-center">
            <label for="select-minutes" class="text-xs text-gray-500 mb-1">นาที</label>
            <select id="select-minutes" v-model.number="selectedMinutes"
              class="w-16 p-2 border border-gray-300 rounded text-center appearance-none focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
              <option v-for="minuteValue in minutes" :key="`min-${minuteValue}`" :value="minuteValue">
                {{ minuteValue.toString().padStart(2, '0') }}
              </option>
            </select>
          </div>
        </div>

        <div class="flex justify-between items-center pt-3 border-t border-gray-200 mt-2">
          <button @click.stop="currentView = (mode === 'datetime' ? 'calendar' : 'time')" :disabled="mode === 'time'"
            class="px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md"
            :class="{ 'opacity-50 cursor-not-allowed': mode === 'time' }">
            กลับ
          </button>
          <button @click="confirmTime"
            class="px-3 py-1.5 text-sm bg-emerald-500 text-white font-medium hover:bg-emerald-600 rounded-md">
            ตกลง
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
/* Add scrollbar styling for time selects if they overflow on small heights */
select {
  max-height: 150px;
  overflow-y: auto;
}

/* Improve focus visibility */
button:focus-visible,
select:focus-visible,
div[ref="inputRef"]:focus-visible {
  outline: 2px solid theme('colors.blue.500');
  outline-offset: 2px;
}

/* Tailwind JIT reference */
@reference "tailwindcss";

/* Support touch screen devices - ensure larger tap targets */
@media (pointer: coarse) {

  button,
  select,
  .grid-cols-7>div {
    /* Make calendar days easier to tap */
    min-height: 44px;
    min-width: 44px;
    display: flex;
    /* Ensure text centers vertically */
    align-items: center;
    justify-content: center;
  }

  div[ref="inputRef"] {
    min-height: 44px;
  }
}
</style>