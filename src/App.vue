<template lang="pug">
v-app
  common-splash-vue(v-show="splashScreen")
  v-main(style="height: 100vh;")
    .status-bar-padding(:class="settings.hidden.isAndroid15OrHigher ? 'android-15-or-higher' : ''")
    router-view(:style="settings.hidden.isAndroid15OrHigher ? 'height: calc(100vh - 40px - 16px);' : ''")
    .nav-bar-padding(:class="settings.hidden.isAndroid15OrHigher ? 'android-15-or-higher' : ''")
</template>

<script lang="ts">
  import { Capacitor } from '@capacitor/core'
  import { Device } from '@capacitor/device'
  import { StatusBar, Style } from '@capacitor/status-bar'
  import commonSplashVue from './components/common/commonSplash.vue'
  import mixins from './mixins/mixins'
  import { useMyProfileStore } from './stores/myProfile'
  import { useSettingsStore } from './stores/settings'

  export default {
    name: 'App',
    components: {
      commonSplashVue,
    },
    mixins: [mixins],
    data () {
      return {
        splashScreen: true,
        myProfile: useMyProfileStore(),
        settings: useSettingsStore(),
      }
    },
    watch: {
      settings: {
        handler: async function () {
          switch (this.settings.developerOptions.statusBarNotch) {
            case 'default': {
              /** ステータスバーがWebViewをオーバーレイしないように設定 */
              const info = await Device.getInfo()
              this.settings.hidden.isAndroid15OrHigher = info.platform === 'android' && Number(info.osVersion) >= 15 ? true : false
              break
            }
            case 'true': {
              this.settings.hidden.isAndroid15OrHigher = true
              break
            }
            case 'false': {
              this.settings.hidden.isAndroid15OrHigher = false
              break
            }
          }
        },
        deep: true,
        immediate: true,
      },
    },
    async mounted () {
      if (Capacitor.getPlatform() !== 'web') {
        StatusBar.setOverlaysWebView({
          overlay: false,
        })
      }
      /** ステータスバーがWebViewをオーバーレイしないように設定 */
      const info = await Device.getInfo()
      this.settings.hidden.isAndroid15OrHigher = info.platform === 'android' && Number(info.osVersion) >= 15 ? true : false

      // 開発者オプション
      if (this.settings.developerOptions.statusBarNotch !== 'default') {
        this.settings.hidden.isAndroid15OrHigher = this.settings.developerOptions.statusBarNotch == 'true'
      }

      /** ログイン情報 */
      if (this.myProfile.$state.guest == false) {
        setTimeout(async () => {
          const token = this.myProfile.userToken
          const profile = await this.getProfile(this.myProfile.userId ?? '')
          if (profile) {
            profile.userToken = token
            profile.guest = false
            this.myProfile = {
              ...this.myProfile,
              ...profile,
            }
          }
        }, 100)
      } else {
        this.myProfile.reset()
      }

      // テーマに関する設定
      const themeOptions = localStorage.getItem('themeOptions')
      if (themeOptions && Capacitor.getPlatform() !== 'web') {
        const options = JSON.parse(themeOptions)
        switch (options.theme) {
          case true: {
            this.$vuetify.theme.change('light')
            StatusBar.setStyle({ style: Style.Light })
            break
          }
          case false: {
            this.$vuetify.theme.change('dark')
            StatusBar.setStyle({ style: Style.Dark })

            break
          }
          case undefined: {
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches
            if (systemTheme) {
              StatusBar.setStyle({ style: Style.Dark })
              this.$vuetify.theme.change('dark')
            } else {
              StatusBar.setStyle({ style: Style.Light })
              this.$vuetify.theme.change('light')
            }

            break
          }
        }
      }

      /**
       * mountedの最後に記述
       */
      window.setTimeout(() => {
        this.splashScreen = false
      }, 500)
    },
  }
</script>

<style lang="scss">
.status-bar-padding.android-15-or-higher {
  height: 40px;
  width: 100vw;
  background-color: rgb(var(--v-theme-surface));
}
.nav-bar-padding.android-15-or-higher {
  height: 16px;
  width: 100vw;
}

body {
  user-select: none;
}

hr {
  border-color: rgba(var(--v-theme-on-surface), 0.3);
  margin: 0.5em 1em;
}

main {
  height: 100vh;
  overflow: hidden;
}

//テーマカラーの変更
//青紫がテーマカラー！
.v-theme--dark {
  --v-theme-primary: 225,35,120!important;
}
.v-theme--light {
  --v-theme-primary: 225,35,120!important;
}
:root {
  --v-theme-primary: 225,35,120!important;
}

:root {
  font-size: 16px;
  --color-allow: #cceeff;
  --color-error: #ffcccc;
  --color-error: #cc2222;
  --color-success: #338833;
  /** アプリの色 */
  --accent-color: rgb(var(--v-theme-primary));
  /** アプリの色に合わせた文字色 */
  --accent-text-color: #ffffff;
  /** デフォルトのボーダー角の大きさ */
  --border-radius: 16px;
}

button {
  text-transform: none !important;
}

/** URLや日報をコピー用 */
pre {
  background: #cccccc;
  color: #000000;
  user-select: all;
  overflow-x: auto;
}

img {
  object-fit: cover;
  object-position: center;
}

// 表示バグ対策
.v-dialog > .v-overlay__content, .v-dialog > .v-overlay__content > form{
  align-items: center;
}
</style>
