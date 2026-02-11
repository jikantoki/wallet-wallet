/**
 * main.ts
 *
 * Bootstraps Vuetify and other plugins then mounts the App`
 */

import { defineCustomElements } from '@ionic/pwa-elements/loader'

import { createPinia } from 'pinia'

import { createPersistedState } from 'pinia-plugin-persistedstate'

// Composables
import { createApp } from 'vue'
// Plugins
import { registerPlugins } from '@/plugins'

// Components
import App from './App.vue'
// Styles
import 'unfonts.css'
defineCustomElements(window)

const pinia = createPinia()
pinia.use(createPersistedState())

const app = createApp(App).use(pinia)

registerPlugins(app)

app.mount('#app')
