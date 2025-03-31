<template>
  <div class="calendar-day border border-slate-100 h-32 p-1 rounded-lg flex flex-col overflow-hidden" :class="{
    'bg-gray-100': !isCurrentMonth,
    'bg-white': isCurrentMonth,
    'bg-blue-50': isToday,
  }">
    <div class="mb-1 flex justify-between">
      <span class="font-bold text-sm rounded-full w-6 h-6 flex items-center justify-center"
        :class="{ 'bg-emerald-500 text-white': isToday, 'text-gray-300': isWeekend }">
        {{ day.getDate() }}
      </span>
      <span class="text-xs text-gray-500">{{ thaiDay }}</span>
    </div>
    <div class="py-1 overflow-y-auto">
      <div v-for="reservation in dayReservations" :key="reservation.id" class="text-xs p-1 my-0.5 rounded truncate"
        :class="getStatusClass(reservation.status)"
        :title="`${formatTime(reservation.arrivalDateTime)} ${reservation.details}`">
        {{ formatTime(reservation.arrivalDateTime) }} {{ reservation.details }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { Reservation } from '../types';
import { formatThaiTime, getThaiDayName } from '../utils/date';

const props = defineProps<{
  day: Date;
  reservations: Reservation[];
  currentMonth: number;
  currentYear: number;
}>();

// ตรวจสอบว่าเป็นวันในเดือนปัจจุบันหรือไม่
const isCurrentMonth = computed(() => {
  return props.day.getMonth() === props.currentMonth - 1 &&
    props.day.getFullYear() === props.currentYear;
});

// ตรวจสอบว่าเป็นวันนี้หรือไม่
const isToday = computed(() => {
  const today = new Date();
  return today.getDate() === props.day.getDate() &&
    today.getMonth() === props.day.getMonth() &&
    today.getFullYear() === props.day.getFullYear();
});

const isWeekend = computed(() => {
  const day = props.day.getDay();
  return day === 0 || day === 6; // วันอาทิตย์ (0) หรือวันเสาร์ (6)
});

// วันในภาษาไทย
const thaiDay = computed(() => {
  return getThaiDayName(props.day.getDay());
});

// การจองในวันนี้
const dayReservations = computed(() => {
  return props.reservations.filter(reservation => {
    const reservationDate = new Date(reservation.arrivalDateTime);
    return reservationDate.getDate() === props.day.getDate() &&
      reservationDate.getMonth() === props.day.getMonth() &&
      reservationDate.getFullYear() === props.day.getFullYear();
  });
});

// จัดรูปแบบเวลา
const formatTime = (date: Date) => {
  return formatThaiTime(new Date(date));
};

// สีตามสถานะการจอง
const getStatusClass = (status: string) => {
  switch (status) {
    case 'approved':
      return 'bg-green-100 text-green-800';
    case 'pending':
      return 'bg-yellow-100 text-yellow-800';
    case 'rejected':
      return 'bg-red-100 text-red-800';
    case 'cancelled':
      return 'bg-gray-100 text-gray-800';
    default:
      return 'bg-gray-100';
  }
};
</script>