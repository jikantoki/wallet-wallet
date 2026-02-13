<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    p.ml-2(style="font-size: 1.3em") データエクスポート
    v-spacer
    v-btn(
      text
      @click="$router.back()"
      icon="mdi-close"
      )
  v-card-text(style="height: inherit; overflow-y: auto;")
    .content
      //-- エクスポート形式選択 --
      v-card.mb-4(variant="outlined")
        v-card-text
          h3.mb-3 エクスポート形式を選択
          v-btn-toggle(
            v-model="exportType"
            mandatory
            color="primary"
            variant="outlined"
            divided
            style="width: 100%;"
          )
            v-btn(
              value="json"
              style="flex: 1;"
            )
              v-icon.mr-2 mdi-code-json
              | JSON形式
            v-btn(
              value="encrypted"
              style="flex: 1;"
            )
              v-icon.mr-2 mdi-lock
              | 暗号化形式

      //-- JSON形式のエクスポート --
      .json-export(v-if="exportType === 'json'")
        //-- 警告メッセージ --
        v-alert(
          type="warning"
          variant="tonal"
          prominent
          class="mb-4"
          icon="mdi-alert"
        )
          .text-h6 注意事項
          div この機能は設定ファイル（JSON）を直接編集・出力します
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
                :disabled="!!cardJSONError"
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
                :disabled="!!bankJSONError"
              ) ダウンロード

      //-- 暗号化形式のエクスポート --
      .encrypted-export(v-if="exportType === 'encrypted'")
        h2 ファイルでデータを転送
        p.my-4 カードと銀行口座のデータを暗号化ファイルとして他の端末に転送できます。パスワードで保護されます。

        v-text-field(
          v-model="password"
          label="転送用パスワード"
          type="password"
          :rules="[v => !!v || 'パスワードは必須です', v => v.length >= 8 || '8文字以上入力してください']"
          hint="8文字以上のパスワードを入力してください"
          persistent-hint
          prepend-icon="mdi-lock"
          @input="onPasswordChange"
        )

        v-text-field.mt-4(
          v-model="passwordConfirm"
          label="パスワード確認"
          type="password"
          :rules="[v => !!v || 'パスワード確認は必須です', v => v === password || 'パスワードが一致しません']"
          prepend-icon="mdi-lock-check"
          @input="onPasswordChange"
        )

        v-btn.mt-4(
          @click="exportToFile"
          prepend-icon="mdi-export"
          style="background-color: rgb(var(--v-theme-primary)); color: white;"
          :disabled="!canExport"
          :loading="exporting"
          block
        ) ファイルをエクスポート

        .info-box.mt-8(v-if="exported")
          v-icon.mb-2(color="success" size="48") mdi-check-circle
          h3 エクスポート成功
          p.mt-2 ファイルがエクスポートされました
          p
            strong ファイル名:
            | {{ fileName }}

          .actions.mt-4
            v-btn.mb-2(
              @click="downloadFile"
              prepend-icon="mdi-download"
              style="background-color: rgb(var(--v-theme-primary)); color: white;"
              block
            ) ファイルをダウンロード

            v-btn(
              @click="copyToClipboard"
              prepend-icon="mdi-content-copy"
              variant="outlined"
              block
            ) クリップボードにコピー

          .warning.mt-4.pa-4(
            style="background-color: rgba(var(--v-theme-warning), 0.1); border-radius: 8px;"
          )
            v-icon(color="warning") mdi-alert
            p.mt-2
              strong 重要：
              | ファイルには暗号化されたデータが含まれています。パスワードを忘れないようにしてください。

      .my-16

//-- 個人情報警告ダイアログ（JSON形式用） --
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

//-- エラーダイアログ（暗号化形式用） --
v-dialog(
  v-model="errorDialog"
  max-width="400"
)
  v-card
    v-card-title
      v-icon.mr-2(color="error") mdi-alert-circle
      | エラー
    v-card-text {{ errorMessage }}
    v-card-actions
      v-spacer
      v-btn(
        @click="errorDialog = false"
        text
      ) 閉じる
</template>

<script lang="ts">
  import { Browser } from '@capacitor/browser'
  import { Clipboard } from '@capacitor/clipboard'
  import { Directory, Filesystem } from '@capacitor/filesystem'
  import { Toast } from '@capacitor/toast'
  import { encryptData } from '@/js/transferEncryption'
  import { useCardsStore } from '@/stores/cards'
  import { useSettingsStore } from '@/stores/settings'

  export default {
    data () {
      return {
        settings: useSettingsStore(),
        cards: useCardsStore(),
        exportType: 'json' as 'json' | 'encrypted',
        // JSON形式用
        cardJSON: '',
        bankJSON: '',
        cardJSONError: '',
        bankJSONError: '',
        warningDialog: false,
        downloadTarget: '' as 'card' | 'bank' | '',
        cardValidationTimeout: null as number | null,
        bankValidationTimeout: null as number | null,
        // 暗号化形式用
        password: '',
        passwordConfirm: '',
        exported: false,
        exporting: false,
        errorDialog: false,
        errorMessage: '',
        fileName: '',
        fileUri: '',
        encryptedContent: '',
      }
    },
    computed: {
      canExport (): boolean {
        return this.password.length >= 8
          && this.password === this.passwordConfirm
      },
    },
    async mounted () {
      this.cardJSON = JSON.stringify(this.cards.cards, null, 2)
      this.bankJSON = JSON.stringify(this.cards.bank, null, 2)
    },
    unmounted () {
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
        window.setTimeout(() => {
          link.remove()
          URL.revokeObjectURL(url)
        }, 100)
      },

      // 暗号化形式用のメソッド
      onPasswordChange () {
        this.exported = false
      },
      async exportToFile () {
        if (!this.canExport) {
          return
        }

        this.exporting = true

        try {
          // エクスポートするデータを準備
          // version: 将来の互換性チェック用のデータフォーマットバージョン
          // timestamp: エクスポートのタイムスタンプ
          const exportData = {
            cards: this.cards.cards,
            bank: this.cards.bank,
            version: 1,
            timestamp: new Date().toISOString(),
          }

          // データを暗号化
          const encryptedData = encryptData(exportData, this.password)

          // ファイル名を生成（タイムスタンプ付き）
          const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19)
          this.fileName = `wallet-backup-${timestamp}.wlt`

          // ファイルに保存
          const result = await Filesystem.writeFile({
            path: this.fileName,
            data: encryptedData,
            directory: Directory.Cache,
            recursive: true,
          })

          this.fileUri = result.uri
          this.encryptedContent = encryptedData
          this.exported = true
          this.exporting = false

          await Toast.show({
            text: 'ファイルをエクスポートしました',
            duration: 'short',
          })
        } catch (error) {
          console.error('ファイルエクスポートに失敗しました:', error)
          this.errorMessage = 'ファイルのエクスポートに失敗しました'
          this.errorDialog = true
          this.exporting = false
          this.exported = false
        }
      },

      async downloadFile () {
        // 一旦Blobに変換
        const blob = new Blob([this.encryptedContent], { type: 'application/octet-stream' })
        const fileUrl = URL.createObjectURL(blob)
        // aタグを作成してクリックイベントを発火
        const a = document.createElement('a')
        a.href = fileUrl
        a.download = this.fileName
        a.click()
      },

      async copyToClipboard () {
        try {
          await Clipboard.write({
            string: this.encryptedContent,
          })

          await Toast.show({
            text: 'クリップボードにコピーしました',
            duration: 'short',
          })
        } catch (error) {
          console.error('クリップボードへのコピーに失敗しました:', error)
          this.errorMessage = 'クリップボードへのコピーに失敗しました'
          this.errorDialog = true
        }
      },
    },
  }
</script>

<style lang="scss" scoped>
.top-android-15-or-higher {
  height: calc(100vh - 40px - 16px)!important;
}

.content {
  max-width: 800px;
  margin: 0 auto;
}

.info-box {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.1);
  border-radius: 8px;
  padding: 1.5em;
  text-align: center;
}
</style>
