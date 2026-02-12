<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    p.ml-2(style="font-size: 1.3em") インポート・エクスポート
    v-spacer
    v-btn(
      text
      @click="$router.back()"
      icon="mdi-close"
      )
  v-card-text(style="height: inherit; overflow-y: auto;")
    h2 注意: この機能は中身の設定ファイル（JSON）を直接編集します
    p.my-4 データを破損させた場合、アプリの初期化が必要になる場合があるので注意してください
    v-textarea(
      v-model="cardJSON"
      label="クレジットカード設定ファイル"
      no-resize
      rows="8"
      @keydown="changeCardJSON"
    )
    v-btn.mb-8(
      prepend-icon="mdi-content-copy"
      style="background-color: rgb(var(--v-theme-primary)); color: white;"
      @click="copy(cardJSON)"
    ) コピー
    v-textarea(
      v-model="bankJSON"
      label="銀行口座設定ファイル"
      no-resize
      rows="8"
      @keydown="changeBankJSON"
    )
    v-btn.mb-8(
      prepend-icon="mdi-content-copy"
      style="background-color: rgb(var(--v-theme-primary)); color: white;"
      @click="copy(bankJSON)"
    ) コピー
    .my-16
</template>

<script lang="ts">
  import { Browser } from '@capacitor/browser'
  import { Clipboard } from '@capacitor/clipboard'
  import { useCardsStore } from '@/stores/cards'
  import { useSettingsStore } from '@/stores/settings'

  export default {
    data () {
      return {
        settings: useSettingsStore(),
        cards: useCardsStore(),
        cardJSON: '',
        bankJSON: '',
      }
    },
    async mounted () {
      this.cardJSON = JSON.stringify(this.cards.cards, null, 2)
      this.bankJSON = JSON.stringify(this.cards.bank, null, 2)
    },
    methods: {
      /** URLをブラウザで開く */
      async openURL (url: string) {
        await Browser.open({ url: url })
      },
      changeCardJSON () {
        try {
          const data = JSON.parse(this.cardJSON)
          this.cards.cards = data
        } catch (error) {
          console.log(error)
        }
      },
      changeBankJSON () {
        try {
          const data = JSON.parse(this.bankJSON)
          this.cards.bank = data
        } catch (error) {
          console.log(error)
        }
      },
      /** クリップボードにコピー */
      async copy (content: string) {
        await Clipboard.write({
          string: content,
        })
      },
    },
  }
</script>

<style lang="scss" scoped>
.top-android-15-or-higher {
  height: calc(100vh - 40px - 16px)!important;
}
</style>
