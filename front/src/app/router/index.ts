import { createRouter, createWebHistory } from 'vue-router'
import { HomePage } from '@/pages/home'
import { ScheduleEditorPage } from '@/pages/schedule-editor'
import { ScheduleListPage } from '@/pages/schedule-list'
import { UiKitPage } from '@/pages/ui-kit'
import AppShell from '../layouts/AppShell.vue'

declare module 'vue-router' {
  interface RouteMeta {
    title?: string
  }
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      component: AppShell,
      children: [
        { path: '', name: 'home', component: HomePage, meta: { title: 'Главная' } },
        {
          path: 'schedules',
          name: 'schedules',
          component: ScheduleListPage,
          meta: { title: 'Графики смен' },
        },
        {
          path: 'schedules/:slug',
          name: 'schedule',
          component: ScheduleEditorPage,
          meta: { title: 'График' },
        },
        { path: 'ui-kit', name: 'ui-kit', component: UiKitPage, meta: { title: 'UI Kit' } },
      ],
    },
  ],
})

router.afterEach((to, _from, failure) => {
  if (!failure && typeof to.meta.title === 'string') {
    document.title = to.meta.title
  }
})

export default router
