<template>
  <div class="calendar-container">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold">{{ thaiMonthYear }}</h2>
      <div class="space-x-2">
        <button @click="prevMonth" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">
          เดือนก่อนหน้า
        </button>
        <button @click="resetMonth" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">
          เดือนปัจจุบัน
        </button>
        <button @click="nextMonth" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">
          เดือนถัดไป
        </button>
      </div>
    </div>

    <div class="grid grid-cols-7 gap-1">
      <!-- หัวข้อวันในสัปดาห์ -->
      <div v-for="day in thaiDays" :key="day" class="text-center font-bold py-2 rounded-lg">
        {{ day }}
      </div>

      <!-- ช่องวันในปฏิทิน -->
      <CalendarDay v-for="day in calendarDays" :key="day.toISOString()" :day="day" :reservations="reservations"
        :current-month="currentMonth" :current-year="currentYear" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import CalendarDay from './CalendarDay.vue';
import { getReservationsByMonth } from '../services/fake-api';
import type { Reservation } from '../types';
import { formatThaiMonthYear, getCalendarDays } from '../utils/date';

// ชื่อวันในภาษาไทย
const thaiDays = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];

// เดือนและปีปัจจุบัน
const currentMonth = ref(new Date().getMonth() + 1);
const currentYear = ref(new Date().getFullYear());

// การจองทั้งหมดในเดือนนี้
const reservations = ref<Reservation[]>([]);

// วันที่แสดงในปฏิทิน
const calendarDays = computed(() => {
  return getCalendarDays(currentYear.value, currentMonth.value);
});

// แสดงเดือนและปีในรูปแบบไทย
const thaiMonthYear = computed(() => {
  return formatThaiMonthYear(new Date(currentYear.value, currentMonth.value - 1));
});

// เปลี่ยนไปเดือนก่อนหน้า
const prevMonth = () => {
  if (currentMonth.value === 1) {
    currentMonth.value = 12;
    currentYear.value--;
  } else {
    currentMonth.value--;
  }
};

// เปลี่ยนไปเดือนถัดไป
const nextMonth = () => {
  if (currentMonth.value === 12) {
    currentMonth.value = 1;
    currentYear.value++;
  } else {
    currentMonth.value++;
  }
};

const resetMonth = () => {
  const today = new Date();
  currentMonth.value = today.getMonth() + 1;
  currentYear.value = today.getFullYear();
};

// โหลดข้อมูลการจอง
const loadReservations = async () => {
  try {
    reservations.value = await getReservationsByMonth(currentYear.value, currentMonth.value);
  } catch (error) {
    console.error('Error loading reservations:', error);
  }
};

// โหลดข้อมูลเมื่อเปลี่ยนเดือนหรือปี
watch([currentMonth, currentYear], () => {
  loadReservations();
});

// โหลดข้อมูลเมื่อคอมโพเนนต์ถูกโหลด
onMounted(() => {
  loadReservations();
});
</script>