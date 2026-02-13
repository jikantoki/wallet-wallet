<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    p.ml-2(style="font-size: 1.3em") 設定
    v-spacer
    v-btn(
      text
      @click="$router.back()"
      icon="mdi-close"
      )
  v-card-text(style="height: inherit; overflow-y: auto;")
    .settings-list
      .setting-item(
        v-ripple
        @click="$router.push('/settings/display')"
        )
        .icon
          v-icon mdi-palette-outline
        .text
          p.title 外観
          p.description テーマ、色、言語
      .setting-item(
        v-ripple
        @click="openNotificationSettings"
        )
        .icon
          v-icon mdi-bell-outline
        .text
          p.title 通知
          p.description メール、プッシュ通知
      .setting-item(
        v-ripple
        @click="openExport()"
        )
        .icon
          v-icon mdi-export
        .text
          p.title データエクスポート
          p.description JSON形式または暗号化形式で出力
      .setting-item(
        v-ripple
        @click="openImport()"
        )
        .icon
          v-icon mdi-import
        .text
          p.title データインポート
          p.description JSON形式または暗号化形式から読み込み
      .setting-item(
        v-ripple
        @click="$router.push('/terms')"
        )
        .icon
          v-icon mdi-file-document-outline
        .text
          p.title 利用規約
      .setting-item(
        v-ripple
        @click="openURL('https://enoki.xyz/privacy')"
        )
        .icon
          v-icon mdi-shield-lock-outline
        .text
          p.title プライバシーポリシー
          p.description 外部リンクが立ち上がります
      .setting-item(
        v-ripple
        @click="$router.push('/about')"
        )
        .icon
          v-icon mdi-information-outline
        .text
          p.title このアプリについて
          p.description バージョン情報
      .setting-item(
        v-if="settings.developerOptions.enabled"
        v-ripple
        @click="$router.push('/settings/developer-options')"
        )
        .icon(
          style="background-color: rgba(var(--v-theme-primary), 1); color: white;"
        )
          v-icon mdi-bug-outline
        .text
          p.title 開発者オプション
          p.description 特定のモバイル向けの機能を強制有効
      .setting-item(
        v-ripple
        @click="logoutRequest()"
        v-if="!myProfile.guest"
        )
        .icon(style="background: rgba(var(--v-theme-error), 1);")
          v-icon mdi-logout
        .text
          p.title ログアウト
          p.description またお会いしましょう！
      .my-16
v-dialog(
  v-model="logoutDialog"
  max-width="400"
  )
  v-card
    v-card-title(class="headline") ログアウトしますか？
    v-card-text
      | ログアウトすると、再度ログインするまでデータの同期が行われません。
    v-card-actions
      v-spacer
      v-btn(
        text
        @click="logoutDialog = false"
        append-icon="mdi-close"
        ) キャンセル
      v-btn(
        text
        style="background: rgba(var(--v-theme-error), 1); color: white;"
        append-icon="mdi-logout"
        @click="logout"
        ) ログアウト
</template>

<script lang="ts">
  import { AndroidBiometryStrength, BiometricAuth, BiometryError, BiometryErrorType } from '@aparajita/capacitor-biometric-auth'
  import { Browser } from '@capacitor/browser'
  import { Toast } from '@capacitor/toast'
  import { AndroidSettings, IOSSettings, NativeSettings } from 'capacitor-native-settings'
  import { useMyProfileStore } from '@/stores/myProfile'
  import { useSettingsStore } from '@/stores/settings'

  export default {
    name: 'SettingsPage',
    data () {
      return {
        logoutDialog: false,
        myProfile: useMyProfileStore(),
        settings: useSettingsStore(),
      }
    },
    async mounted () {},
    methods: {
      logoutRequest () {
        this.logoutDialog = true
      },
      logout () {
        this.logoutDialog = false
        this.myProfile.reset()
        this.$router.push('/login')
      },
      async openNotificationSettings () {
        await NativeSettings.open({
          optionAndroid: AndroidSettings.AppNotification,
          optionIOS: IOSSettings.AppNotification,
        })
      },
      /** URLをブラウザで開く */
      async openURL (url: string) {
        await Browser.open({ url: url })
      },
      /**
       * ## 生体認証
       * await必須
       * @returns boolean 認証OK→true、NG→false
       */
      async auth (): Promise <boolean> {
        const isAvailable = await BiometricAuth.checkBiometry()
        if (isAvailable.isAvailable == false) {
          // 認証がそもそも機能しない場合は、仕方ないので承認する
          return true
        }
        try {
          await BiometricAuth.authenticate({
            reason: '認証してください',
            cancelTitle: 'キャンセル',
            allowDeviceCredential: true,
            androidTitle: '生体認証ログイン',
            androidSubtitle: '本人確認をしてください',
            androidConfirmationRequired: true,
            androidBiometryStrength: AndroidBiometryStrength.weak,
          })
          return true
        } catch (error) {
          if (error instanceof BiometryError && error.code !== BiometryErrorType.userCancel) {
            // エラーを表示
            Toast.show({ text: error.message })
          } else {
            console.log(error)
          }
          return false
        }
      },
      async openExport () {
        const authResult = await this.auth()
        if (authResult) {
          this.$router.push('/data-export')
          return true
        } else {
          Toast.show({ text: '生体認証に失敗しました' })
          return false
        }
      },
      async openImport () {
        const authResult = await this.auth()
        if (authResult) {
          this.$router.push('/data-import')
          return true
        } else {
          Toast.show({ text: '生体認証に失敗しました' })
          return false
        }
      },
    },
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

  .top-android-15-or-higher {
    height: calc(100vh - 40px - 16px)!important;
  }
</style>
