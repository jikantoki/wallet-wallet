<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    p.ml-2(style="font-size: 1.3em") 外観
    v-spacer
    v-btn(
      text
      @click="$router.back()"
      icon="mdi-close"
      )
  v-card-text(style="height: inherit; overflow-y: auto;")
    .settings-list
      .setting-item
        .icon
          v-icon mdi-theme-light-dark
        .text
          p.title テーマ
          p.description アプリをライトテーマで表示するか、ダークテーマで表示するかが選べます
      .setting-button-item.mb-4
        .li(
          v-ripple
          :class="settings.display.theme === 'system' ? 'selected' : null"
          @click="settings.display.theme = 'system'"
          )
          v-icon(size="x-large") mdi-brightness-auto
          p システム
        .li(
          v-ripple
          :class="settings.display.theme === 'light' ? 'selected' : null"
          @click="settings.display.theme = 'light'"
          )
          v-icon(size="x-large") mdi-brightness-7
          p ライト
        .li(
          v-ripple
          :class="settings.display.theme === 'dark' ? 'selected' : null"
          @click="settings.display.theme = 'dark'"
          )
          v-icon(size="x-large") mdi-brightness-3
          p ダーク
    .settings-list
      .setting-item
        .icon
          v-icon mdi-translate-variant
        .text
          p.title 言語
          p.description Switch language
      v-select(
        label="Language"
        :items="['日本語']"
        v-model="settings.display.language"
      )
      .my-16
</template>

<script lang="ts">
  import { Capacitor } from '@capacitor/core'
  import { Device } from '@capacitor/device'
  import { StatusBar, Style } from '@capacitor/status-bar'
  import { useSettingsStore } from '@/stores/settings'

  export default {
    data () {
      return {
        settings: useSettingsStore(),
      }
    },
    watch: {
      settings: {
        handler () {
          switch (this.settings.display.theme) {
            case 'light': {
              this.$vuetify.theme.change('light')
              if (Capacitor.getPlatform() !== 'web') {
                StatusBar.setStyle({ style: Style.Light })
              }
              break
            }
            case 'dark': {
              this.$vuetify.theme.change('dark')
              if (Capacitor.getPlatform() !== 'web') {
                StatusBar.setStyle({ style: Style.Dark })
              }

              break
            }
            case 'system': {
              const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches
              if (systemTheme) {
                if (Capacitor.getPlatform() !== 'web') {
                  StatusBar.setStyle({ style: Style.Dark })
                }
                this.$vuetify.theme.change('dark')
              } else {
                if (Capacitor.getPlatform() !== 'web') {
                  StatusBar.setStyle({ style: Style.Light })
                }

                this.$vuetify.theme.change('light')
              }
              break
            }
          // No default
          }
        },
        deep: true,
      },
    },
    async mounted () {},
  }
</script>

<style lang="scss" scoped>
  .settings-list {
    display: flex;
    flex-direction: column;
    gap: 1em;
    .setting-item {
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 1em;
      padding: 1em;
      border-radius: 8px;
      cursor: pointer;
      .icon {
        background: rgba(var(--v-theme-on-surface), 0.1);
        border-radius: 50%;
        width: 40px;
        min-width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .text {
        .title {
          font-weight: bold;
          font-size: 1.1em;
        }
        .description {
          font-size: 0.9em;
          color: #666;
        }
      }
    }
  }

  .setting-button-item {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    margin: 0 1em;
    border-radius: 8px;
    overflow: hidden;
      border: 1px solid rgba(var(--v-theme-on-surface), 0.3);
    .li {
      width: 33%;
      text-align: center;
      cursor: pointer;
      .v-icon {
        margin: 8px;
      }
      p {
        margin: 0;
        padding: 0.5em 0;
      }
      &.selected {
        background-color: rgba(var(--v-theme-primary), 0.3);
      }
    }
  }

  .top-android-15-or-higher {
    height: calc(100vh - 40px - 16px)!important;
  }
</style>
