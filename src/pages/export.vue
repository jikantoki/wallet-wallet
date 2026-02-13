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
    //-- 警告メッセージ --
    v-alert(
      type="warning"
      variant="tonal"
      prominent
      class="mb-4"
      icon="mdi-alert"
    )
      .text-h6 注意事項
      div この機能は設定ファイル（JSON）を直接編集します
      div データを破損させた場合、アプリの初期化が必要になる場合があります

    //-- クレジットカード設定 --
    v-card.mb-4(variant="outlined")
      v-card-title.bg-primary
        v-icon.mr-2 mdi-credit-card
        span クレジットカード設定ファイル
      v-card-text.pt-4
        v-textarea(
          v-model="cardJSON"
          variant="outlined"
          no-resize
          rows="8"
          @input="validateCardJSON"
          :error="!!cardJSONError"
        )
        v-alert.mb-4(
          v-if="cardJSONError"
          type="error"
          variant="tonal"
          density="compact"
          class="mb-2"
          icon="mdi-alert-circle"
        ) {{ cardJSONError }}
        .d-flex(
          style="gap: 8px;"
        )
          v-btn(
            prepend-icon="mdi-content-copy"
            color="primary"
            variant="elevated"
            @click="copy(cardJSON)"
          ) コピー
          v-btn(
            prepend-icon="mdi-download"
            color="success"
            variant="elevated"
            @click="downloadCardJSON"
          ) ダウンロード

    //-- 銀行口座設定 --
    v-card.mb-4(variant="outlined")
      v-card-title.bg-primary
        v-icon.mr-2 mdi-bank
        span 銀行口座設定ファイル
      v-card-text.pt-4
        v-textarea(
          v-model="bankJSON"
          variant="outlined"
          no-resize
          rows="8"
          @input="validateBankJSON"
          :error="!!bankJSONError"
        )
        v-alert.mb-4(
          v-if="bankJSONError"
          type="error"
          variant="tonal"
          density="compact"
          class="mb-2"
          icon="mdi-alert-circle"
        ) {{ bankJSONError }}
        .d-flex(
          style="gap: 8px;"
        )
          v-btn(
            prepend-icon="mdi-content-copy"
            color="primary"
            variant="elevated"
            @click="copy(bankJSON)"
          ) コピー
          v-btn(
            prepend-icon="mdi-download"
            color="success"
            variant="elevated"
            @click="downloadBankJSON"
          ) ダウンロード
    .my-16.pa-4
  //-- 個人情報警告ダイアログ --
  v-dialog(
    v-model="warningDialog"
    max-width="500"
  )
    v-card
      v-card-title.bg-error.text-white
        v-icon.mr-2 mdi-alert-octagon
        span 重要な警告
      v-card-text.pt-4
        .text-h6.mb-2 個人情報の取り扱いについて
        p.mb-2 このファイルには以下の個人情報が含まれています：
        v-list(density="compact")
          v-list-item(prepend-icon="mdi-credit-card") クレジットカード番号
          v-list-item(prepend-icon="mdi-calendar") 有効期限
          v-list-item(prepend-icon="mdi-lock") セキュリティコード（CVC）
          v-list-item(prepend-icon="mdi-account") カード名義人
          v-list-item(prepend-icon="mdi-bank") 銀行口座情報
        p.mt-4.font-weight-bold.text-error ファイルの取り扱いには十分ご注意ください
        p • 第三者と共有しないでください
        p • 安全な場所に保管してください
        p • 不要になったら確実に削除してください
      v-card-actions
        v-spacer
        v-btn(
          @click="warningDialog = false"
          variant="text"
        ) キャンセル
        v-btn(
          @click="confirmDownload"
          color="error"
          variant="elevated"
        ) 理解した上でダウンロード
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
        cardJSONError: '',
        bankJSONError: '',
        warningDialog: false,
        downloadTarget: '' as 'card' | 'bank' | '',
        cardValidationTimeout: null as number | null,
        bankValidationTimeout: null as number | null,
      }
    },
    async mounted () {
      this.cardJSON = JSON.stringify(this.cards.cards, null, 2)
      this.bankJSON = JSON.stringify(this.cards.bank, null, 2)
    },
    beforeUnmount () {
      if (this.cardValidationTimeout) {
        clearTimeout(this.cardValidationTimeout)
      }
      if (this.bankValidationTimeout) {
        clearTimeout(this.bankValidationTimeout)
      }
    },
    methods: {
      /** URLをブラウザで開く */
      async openURL (url: string) {
        await Browser.open({ url: url })
      },
      /** クレジットカードJSONの検証 */
      validateCardJSON () {
        if (this.cardValidationTimeout) {
          clearTimeout(this.cardValidationTimeout)
        }
        this.cardValidationTimeout = window.setTimeout(() => {
          try {
            const data = JSON.parse(this.cardJSON)
            this.cards.cards = data
            this.cardJSONError = ''
          } catch (error) {
            this.cardJSONError = error instanceof Error ? `JSONパースエラー: ${error.message}` : 'JSONパースエラーが発生しました'
          }
        }, 10)
      },
      /** 銀行口座JSONの検証 */
      validateBankJSON () {
        if (this.bankValidationTimeout) {
          clearTimeout(this.bankValidationTimeout)
        }
        this.bankValidationTimeout = window.setTimeout(() => {
          try {
            const data = JSON.parse(this.bankJSON)
            this.cards.bank = data
            this.bankJSONError = ''
          } catch (error) {
            this.bankJSONError = error instanceof Error ? `JSONパースエラー: ${error.message}` : 'JSONパースエラーが発生しました'
          }
        }, 10)
      },
      /** クリップボードにコピー */
      async copy (content: string) {
        await Clipboard.write({
          string: content,
        })
      },
      /** クレジットカードJSONのダウンロード */
      downloadCardJSON () {
        this.downloadTarget = 'card'
        this.warningDialog = true
      },
      /** 銀行口座JSONのダウンロード */
      downloadBankJSON () {
        this.downloadTarget = 'bank'
        this.warningDialog = true
      },
      /** ダウンロード確認後の処理 */
      confirmDownload () {
        this.warningDialog = false
        if (this.downloadTarget === 'card') {
          this.downloadJSON(this.cardJSON, 'wallet-wallet-cards.json')
        } else if (this.downloadTarget === 'bank') {
          this.downloadJSON(this.bankJSON, 'wallet-wallet-bank.json')
        }
        this.downloadTarget = ''
      },
      /** JSONファイルのダウンロード */
      downloadJSON (content: string, filename: string) {
        const blob = new Blob([content], { type: 'application/json' })
        const url = URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.download = filename
        document.body.append(link)
        link.click()
        setTimeout(() => {
          link.remove()
          URL.revokeObjectURL(url)
        }, 100)
      },
    },
  }
</script>

<style lang="scss" scoped>
.top-android-15-or-higher {
  height: calc(100vh - 40px - 16px)!important;
}
</style>
