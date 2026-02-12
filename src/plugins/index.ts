/**
 * plugins/index.ts
 *
 * Automatically included in `./src/main.ts`
 */

// Types
import type { App } from 'vue'
import router from '../router'

// Plugins
import vuetify from './vuetify'
import { vMaska } from 'maska/vue'

export function registerPlugins (app: App) {
  app.use(vuetify).use(router).directive('maska', vMaska)
}
