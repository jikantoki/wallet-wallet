<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    p.ml-2(style="font-size: 1.3em") 開発者オプション
    v-spacer
    v-btn(
      text
      @click="$router.back()"
      icon="mdi-close"
      )
  v-card-text(style="height: inherit; overflow-y: auto;")
    p ここに表示されている設定項目は、一部アプリの再起動が必要なものがあります。
    .settings-list
      .setting-item
        .icon
          v-icon mdi-cellphone-settings
        .text
          p.title ステータスバーの切り欠きを強制変更
          p.description Android 15以降用の機能でステータスバーの切り欠きを強制的に表示・非表示にします
      .setting-button-item
        .li(
          v-ripple
          :class="settings.developerOptions.statusBarNotch === 'default' ? 'selected' : null"
          @click="settings.developerOptions.statusBarNotch = 'default'"
        )
          v-icon(size="x-large") mdi-sync
          p デフォルト
        .li(
          v-ripple
          :class="settings.developerOptions.statusBarNotch === 'true' ? 'selected' : null"
          @click="settings.developerOptions.statusBarNotch = 'true'"
        )
          v-icon(size="x-large") mdi-check
          p 有効
        .li(
          v-ripple
          :class="settings.developerOptions.statusBarNotch === 'false' ? 'selected' : null"
          @click="settings.developerOptions.statusBarNotch = 'false'"
        )
          v-icon(size="x-large") mdi-close
          p 無効
      .setting-item(
        v-ripple
        @click="$router.push('/tutorial')"
        )
        .icon
          v-icon mdi-fit-to-screen-outline
        .text
          p.title チュートリアルの再表示
          p.description 初回限定のチュートリアル画面を再度表示
      .setting-item(
        v-ripple
        @click="developerOptionLogoutDialog = true"
        )
        .icon(
          style="background-color: rgba(var(--v-theme-error), 1);"
          )
          v-icon mdi-logout
        .text
          p.title 開発者オプションの無効化
          p.description またお会いしましょう！
      .my-16
v-dialog(
  v-model="developerOptionLogoutDialog"
  max-width="400"
  )
  v-card
    v-card-title(class="headline") 開発者オプションを無効化しますか？
    v-card-text
      | 開発者オプションは再度有効にするまで変更できません
    v-card-actions
      v-spacer
      v-btn(
        text
        @click="developerOptionLogoutDialog = false"
        append-icon="mdi-close"
        ) キャンセル
      v-btn(
        text
        style="background: rgba(var(--v-theme-error), 1); color: white;"
        append-icon="mdi-logout"
        @click="developerOptionLogout"
        ) 無効化
</template>

<script lang="ts">
  import { useSettingsStore } from '@/stores/settings'

  export default {
    data () {
      return {
        settings: useSettingsStore(),
      }
    },
    watch: {},
    async mounted () {},
    methods: {
      developerOptionLogout () {
        this.settings.developerOptions.enabled = false
        this.$router.back()
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
