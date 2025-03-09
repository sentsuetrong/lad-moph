import { createWebHashHistory, createRouter, type RouteRecordRaw } from 'vue-router'

const routes: readonly RouteRecordRaw[] = [
  {
    path: '/',
    component: () => import('./components/Layouts/Portal.vue'),
    children: [
      {
        path: '',
        name: 'portal',
        component: () => import('./pages/portal/Index.vue')
      }
    ]
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