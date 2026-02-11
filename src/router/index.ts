/**
 * router/index.ts
 *
 * Automatic routes for `./src/pages/*.vue`
 */
import type { URLOpenListenerEvent } from '@capacitor/app'

import { App } from '@capacitor/app'

import { Browser } from '@capacitor/browser'
import { Toast } from '@capacitor/toast'
// Composables
import { createRouter, createWebHistory } from 'vue-router'
import { routes } from 'vue-router/auto-routes'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// Workaround for https://github.com/vitejs/vite/issues/11804
router.onError((err, to) => {
  if (err?.message?.includes?.('Failed to fetch dynamically imported module')) {
    if (localStorage.getItem('vuetify:dynamic-reload')) {
      console.error('Dynamic import error, reloading page did not fix it', err)
    } else {
      console.log('Reloading page to fix dynamic import error')
      localStorage.setItem('vuetify:dynamic-reload', 'true')
      location.assign(to.fullPath)
    }
  } else {
    console.error(err)
  }
})

router.isReady().then(() => {
  localStorage.removeItem('vuetify:dynamic-reload')
})
// 特定のURLを開いたらこのアプリが立ち上がる設定
App.addListener('appUrlOpen', function (event: URLOpenListenerEvent) {
  const url = new URL(event.url)
  const slug = url.pathname
  if (slug.startsWith('/user/')) {
    router.push(slug)
  } else if (slug) {
    Browser.open({ url: `https://wallet.enoki.xyz${slug}` })
  } else {
    Toast.show({ text: '対応する動作が見つかりませんでした。' })
  }
})

export default router
