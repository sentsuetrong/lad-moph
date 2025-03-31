import type { Reservation, MonthlyReservations, ReservationStats } from '../types';
import { format, addMonths, isToday, subMonths } from 'date-fns';

// สร้างข้อมูลจำลองสำหรับการจองรถยนต์
const generateMockData = (): MonthlyReservations => {
  const currentDate = new Date();
  const result: MonthlyReservations = {};

  // สร้างข้อมูลสำหรับ 3 เดือน (เดือนก่อนหน้า, เดือนปัจจุบัน, เดือนถัดไป)
  for (let monthOffset = -1; monthOffset <= 1; monthOffset++) {
    const targetMonth = addMonths(currentDate, monthOffset);
    const monthKey = format(targetMonth, 'yyyy-MM');

    // สร้างข้อมูลการจองประมาณ 10-20 รายการต่อเดือน
    const reservationsCount = 10 + Math.floor(Math.random() * 10);
    const reservations: Reservation[] = [];

    for (let i = 0; i < reservationsCount; i++) {
      const date = new Date(
        targetMonth.getFullYear(),
        targetMonth.getMonth(),
        1 + Math.floor(Math.random() * 28),
        8 + Math.floor(Math.random() * 8),
        Math.random() > 0.5 ? 0 : 30
      );

      const vehicleTypes: Array<'sedan' | 'pickup' | 'van'> = ['sedan', 'pickup', 'van'];
      const statusTypes: Array<'pending' | 'approved' | 'rejected' | 'cancelled'> = [
        'pending', 'approved', 'approved', 'approved', 'rejected', 'cancelled'
      ];

      const status = statusTypes[Math.floor(Math.random() * statusTypes.length)];

      const reservation: Reservation = {
        id: Date.now() + i,
        requesterName: `ผู้จอง ${i + 1}`,
        destination: `สถานที่ ${i + 1}`,
        details: `การประชุมเรื่อง${i + 1} ณ กรมการปกครอง`,
        vehicleType: vehicleTypes[Math.floor(Math.random() * vehicleTypes.length)],
        passengerCount: 1 + Math.floor(Math.random() * 10),
        arrivalDateTime: date,
        status: status,
        createdAt: new Date(date.getTime() - Math.random() * 1000 * 60 * 60 * 24 * 7)
      };

      if (status === 'approved' || status === 'rejected') {
        reservation.approverName = 'ผู้อนุมัติ';
        reservation.approvalDate = new Date(date.getTime() - Math.random() * 1000 * 60 * 60 * 24);

        if (status === 'rejected') {
          reservation.rejectionReason = 'ไม่อยู่ในช่วงเวลาทำการ';
        }
      }

      reservations.push(reservation);
    }

    result[monthKey] = reservations;
  }

  return result;
};

// ข้อมูลจำลอง
let mockData: MonthlyReservations = generateMockData();

// ดึงข้อมูลการจองในเดือนที่ระบุ
export const getReservationsByMonth = async (year: number, month: number): Promise<Reservation[]> => {
  const monthKey = `${year}-${String(month).padStart(2, '0')}`;

  // สร้างข้อมูลจำลองถ้ายังไม่มีข้อมูล
  if (!mockData[monthKey]) {
    const targetDate = new Date(year, month - 1);
    const currentDate = new Date();

    // สร้างข้อมูลจำลองเฉพาะสำหรับช่วงเวลา 3 เดือนล่าสุดเท่านั้น
    if (
      targetDate >= subMonths(currentDate, 1) &&
      targetDate <= addMonths(currentDate, 1)
    ) {
      mockData = generateMockData();
    } else {
      return [];
    }
  }

  return mockData[monthKey] || [];
};

// เพิ่มการจอง
export const createReservation = async (reservation: Omit<Reservation, 'id' | 'status' | 'createdAt'>): Promise<Reservation> => {
  const newReservation: Reservation = {
    ...reservation,
    id: Date.now(),
    status: 'pending',
    createdAt: new Date()
  };

  const monthKey = format(newReservation.arrivalDateTime, 'yyyy-MM');
  if (!mockData[monthKey]) {
    mockData[monthKey] = [];
  }

  mockData[monthKey].push(newReservation);
  return newReservation;
};

// อัปเดตสถานะการจอง
export const updateReservationStatus = async (
  id: number,
  status: 'approved' | 'rejected' | 'cancelled',
  approverName?: string,
  rejectionReason?: string
): Promise<Reservation | null> => {
  for (const monthKey in mockData) {
    const index = mockData[monthKey].findIndex(r => r.id === id);

    if (index !== -1) {
      const updatedReservation = {
        ...mockData[monthKey][index],
        status,
        approvalDate: new Date()
      };

      if (status === 'approved' || status === 'rejected') {
        updatedReservation.approverName = approverName;
      }

      if (status === 'rejected' && rejectionReason) {
        updatedReservation.rejectionReason = rejectionReason;
      }

      mockData[monthKey][index] = updatedReservation;
      return updatedReservation;
    }
  }

  return null;
};

// ดึงข้อมูลสถิติสำหรับหน้า Dashboard
export const getReservationStats = async (): Promise<ReservationStats> => {
  const currentDate = new Date();
  const currentMonth = currentDate.getMonth() + 1;
  const currentYear = currentDate.getFullYear();
  const lastMonth = currentMonth === 1 ? 12 : currentMonth - 1;
  const lastMonthYear = currentMonth === 1 ? currentYear - 1 : currentYear;

  const currentMonthReservations = await getReservationsByMonth(currentYear, currentMonth);
  const lastMonthReservations = await getReservationsByMonth(lastMonthYear, lastMonth);

  return {
    pendingCount: currentMonthReservations.filter(r => r.status === 'pending').length,
    todayCount: currentMonthReservations.filter(r => isToday(r.arrivalDateTime)).length,
    lastMonthCount: lastMonthReservations.length,
    currentMonthCount: currentMonthReservations.length
  };
};

// ดึงรายการจองที่รอการอนุมัติทั้งหมด
export const getPendingReservations = async (): Promise<Reservation[]> => {
  const result: Reservation[] = [];

  // รวมรายการจองทั้งหมดจากทุกเดือน
  for (const monthKey in mockData) {
    const pendingReservations = mockData[monthKey]
      .filter(reservation => reservation.status === 'pending');

    result.push(...pendingReservations);
  }

  // เรียงลำดับตามวันที่สร้างล่าสุด
  return result.sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime());
};