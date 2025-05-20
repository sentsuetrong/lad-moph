import { createWebHashHistory, createRouter, type RouteRecordRaw } from 'vue-router'

const routes: readonly RouteRecordRaw[] = [
  {
    path: '',
    component: () => import('./components/Layouts/Portal.vue')
  },
  {
    path: '/portal',
    name: 'portal',
    component: () => import('./pages/portal/Index.vue')
  },
  {
    path: '/home',
    name: 'home',
    component: () => import('./pages/HomeView.vue')
  },
  {
    path: '/admin',
    name: 'admin',
    component: () => import('./pages/AdminView.vue')
  },
  {
    path: '/reservation',
    name: 'reservation',
    component: () => import('./pages/ReservationView.vue')
  },
  {
    path: '/approve',
    name: 'approve',
    component: () => import('./pages/ApproveView.vue')
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior(_, __, savedPosition) {
    return savedPosition ? savedPosition : { top: 0 }
  }
})

export default router