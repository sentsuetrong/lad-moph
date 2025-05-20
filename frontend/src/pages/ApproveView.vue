<template>
  <div class="container mx-auto py-8">
    <div class="bg-white rounded-lg shadow p-6">
      <h1 class="text-2xl font-bold mb-6">อนุมัติการจองรถยนต์</h1>

      <!-- รายการที่รอการอนุมัติ -->
      <div v-if="pendingReservations.length > 0">
        <div class="overflow-x-auto">
          <table class="min-w-full bg-white border">
            <thead>
              <tr class="bg-gray-100">
                <th class="py-2 px-4 border text-left">ผู้จอง</th>
                <th class="py-2 px-4 border text-left">สถานที่</th>
                <th class="py-2 px-4 border text-left">วันเวลา</th>
                <th class="py-2 px-4 border text-left">ประเภทรถ</th>
                <th class="py-2 px-4 border text-left">จำนวนผู้โดยสาร</th>
                <th class="py-2 px-4 border text-left">วันที่จอง</th>
                <th class="py-2 px-4 border text-center">การจัดการ</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="reservation in pendingReservations" :key="reservation.id" class="hover:bg-gray-50">
                <td class="py-2 px-4 border">{{ reservation.requesterName }}</td>
                <td class="py-2 px-4 border">{{ reservation.destination }}</td>
                <td class="py-2 px-4 border">{{ formatThaiDateTime(reservation.arrivalDateTime) }}</td>
                <td class="py-2 px-4 border">{{ getVehicleTypeThai(reservation.vehicleType) }}</td>
                <td class="py-2 px-4 border">{{ reservation.passengerCount }} คน</td>
                <td class="py-2 px-4 border">{{ formatThaiDate(reservation.createdAt) }}</td>
                <td class="py-2 px-4 border text-center">
                  <div class="flex justify-center space-x-2">
                    <button @click="openApprovalModal(reservation, 'approved')"
                      class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                      อนุมัติ
                    </button>
                    <button @click="openApprovalModal(reservation, 'rejected')"
                      class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                      ไม่อนุมัติ
                    </button>
                    <button @click="openApprovalModal(reservation, 'cancelled')"
                      class="px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-sm">
                      ยกเลิก
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- กรณีไม่มีรายการที่รอการอนุมัติ -->
      <div v-else class="text-center py-10 text-gray-500">
        ไม่มีรายการจองที่รอการอนุมัติ
      </div>
    </div>

    <!-- Modal สำหรับการอนุมัติ/ไม่อนุมัติ/ยกเลิก -->
    <ApprovalModal :show="showModal" :reservation="selectedReservation" :action="modalAction" @close="closeModal"
      @updated="handleReservationUpdated" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import ApprovalModal from '../components/ApprovalModal.vue';
import { getPendingReservations } from '../services/fake-api';
import type { Reservation } from '../types';
import { formatThaiDate, formatThaiTime } from '../utils/date';

// รายการจองที่รอการอนุมัติ
const pendingReservations = ref<Reservation[]>([]);

// ข้อมูลสำหรับ Modal
const showModal = ref(false);
const selectedReservation = ref<Reservation | null>(null);
const modalAction = ref<'approved' | 'rejected' | 'cancelled'>('approved');

// โหลดรายการจองที่รอการอนุมัติ
const loadPendingReservations = async () => {
  try {
    pendingReservations.value = await getPendingReservations();
  } catch (error) {
    console.error('Error loading pending reservations:', error);
  }
};

// แปลงชื่อประเภทรถเป็นภาษาไทย
const getVehicleTypeThai = (type: string) => {
  switch (type) {
    case 'sedan':
      return 'รถเก๋ง';
    case 'pickup':
      return 'รถกระบะ';
    case 'van':
      return 'รถตู้';
    default:
      return type;
  }
};

// จัดรูปแบบวันเวลาเป็นภาษาไทย
const formatThaiDateTime = (date: Date) => {
  const dateObj = new Date(date);
  return `${formatThaiDate(dateObj)} ${formatThaiTime(dateObj)} น.`;
};

// เปิด Modal การอนุมัติ
const openApprovalModal = (reservation: Reservation, action: 'approved' | 'rejected' | 'cancelled') => {
  selectedReservation.value = reservation;
  modalAction.value = action;
  showModal.value = true;
};

// ปิด Modal
const closeModal = () => {
  showModal.value = false;
};

// จัดการเมื่อมีการอัปเดตสถานะ
const handleReservationUpdated = (reservation: Reservation) => {
  // ลบรายการที่ถูกอัปเดตออกจากรายการที่รอการอนุมัติ
  pendingReservations.value = pendingReservations.value.filter(r => r.id !== reservation.id);
};

// โหลดข้อมูลเมื่อคอมโพเนนต์ถูกโหลด
onMounted(() => {
  loadPendingReservations();
});
</script>