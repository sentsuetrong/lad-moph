import type { RouteLocationRaw } from 'vue-router';

export type NavigationItemType = {
  name: string;
  to?: RouteLocationRaw;
  isOpen: boolean;
  children?: NavigationItemType[];
};

export interface Reservation {
  id: number;
  requesterName: string;
  destination: string;
  details: string;
  vehicleType: 'sedan' | 'pickup' | 'van';
  passengerCount: number;
  arrivalDateTime: Date;
  status: 'pending' | 'approved' | 'rejected' | 'cancelled';
  approverName?: string;
  rejectionReason?: string;
  approvalDate?: Date;
  createdAt: Date;
}

export type MonthlyReservations = {
  [key: string]: Reservation[];
};

export type ReservationStats = {
  pendingCount: number;
  todayCount: number;
  lastMonthCount: number;
  currentMonthCount: number;
};