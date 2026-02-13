<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    p.ml-2(style="font-size: 1.3em") データインポート
    v-spacer
    v-btn(
      text
      @click="$router.back()"
      icon="mdi-close"
      )
  v-card-text(style="height: inherit; overflow-y: auto;")
    .content
      //-- インポート形式選択 --
      v-card.mb-4(variant="outlined")
        v-card-text
          h3.mb-3 インポート形式を選択
          v-btn-toggle(
            v-model="importType"
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

      //-- JSON形式のインポート --
      .json-import(v-if="importType === 'json'")
        //-- 警告メッセージ --
        v-alert(
          type="warning"
          variant="tonal"
          prominent
          class="mb-4"
          icon="mdi-alert"
        )
          .text-h6 注意事項
          div この機能は設定ファイル（JSON）を直接編集・インポートします
          div 既存のデータは上書きされます。事前にバックアップを取ることをお勧めします

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
                prepend-icon="mdi-file-upload"
                color="primary"
                variant="elevated"
                @click="uploadCardJSON"
              ) ファイルから読み込み
              v-btn(
                prepend-icon="mdi-content-paste"
                color="primary"
                variant="outlined"
                @click="pasteCardJSON"
              ) 貼り付け

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
                prepend-icon="mdi-file-upload"
                color="primary"
                variant="elevated"
                @click="uploadBankJSON"
              ) ファイルから読み込み
              v-btn(
                prepend-icon="mdi-content-paste"
                color="primary"
                variant="outlined"
                @click="pasteBankJSON"
              ) 貼り付け

        v-btn.mt-4(
          @click="applyJSONImport"
          prepend-icon="mdi-check"
          style="background-color: rgb(var(--v-theme-primary)); color: white;"
          :disabled="!!cardJSONError || !!bankJSONError"
          block
        ) データをインポート

      //-- 暗号化形式のインポート --
      .encrypted-import(v-if="importType === 'encrypted'")
        h2 ファイルからデータをインポート
        p.my-4 転送元の端末で生成したファイルを選択して、データをインポートします。

        v-alert.my-4(
          type="warning"
          variant="tonal"
        )
          | 既存のカードと銀行口座データは
          strong 上書き
          | されます。事前にバックアップを取ることをお勧めします。

        .import-method.my-4
          h3.mb-4 インポート方法を選択

          v-btn.mb-3(
            @click="selectFile"
            prepend-icon="mdi-file-upload"
            style="background-color: rgb(var(--v-theme-primary)); color: white;"
            :disabled="importing"
            block
          ) ファイルを選択

          v-btn(
            @click="pasteFromClipboard"
            prepend-icon="mdi-content-paste"
            variant="outlined"
            :disabled="importing"
            block
          ) クリップボードから貼り付け

        .file-info.my-4(v-if="fileLoaded")
          v-icon.mb-2(color="success" size="48") mdi-file-check
          h3 ファイルを読み込みました
          p.mt-2(v-if="fileName")
            strong ファイル名:
            | {{ fileName }}

          v-text-field.mt-4(
            v-model="password"
            label="転送用パスワード"
            type="password"
            :rules="[v => !!v || 'パスワードは必須です']"
            hint="エクスポート時に設定したパスワードを入力してください"
            persistent-hint
            prepend-icon="mdi-lock"
            :disabled="importing"
          )

          v-btn.mt-4(
            @click="importData"
            prepend-icon="mdi-database-import"
            style="background-color: rgb(var(--v-theme-primary)); color: white;"
            :disabled="!password || importing"
            :loading="importing"
            block
          ) データをインポート

          v-btn.mt-2(
            @click="resetImport"
            prepend-icon="mdi-refresh"
            variant="text"
            :disabled="importing"
            block
          ) リセット

      .my-16

//-- インポート成功ダイアログ --
v-dialog(
  v-model="importSuccessDialog"
  max-width="400"
)
  v-card
    v-card-title
      v-icon.mr-2(color="success") mdi-check-circle
      | インポート成功
    v-card-text
      p カードと銀行口座のデータをインポートしました。
      p.mt-2(v-if="importedData")
        strong カード: {{ importedData?.cards?.length || 0 }}件
        br
        strong 銀行口座: {{ importedData?.bank?.length || 0 }}件
    v-card-actions
      v-spacer
      v-btn(
        @click="closeAndGoHome"
        style="background-color: rgb(var(--v-theme-primary)); color: white;"
      ) 閉じる

//-- インポートエラーダイアログ --
v-dialog(
  v-model="importErrorDialog"
  max-width="400"
)
  v-card
    v-card-title
      v-icon.mr-2(color="error") mdi-alert-circle
      | インポート失敗
    v-card-text
      p {{ errorMessage }}
    v-card-actions
      v-spacer
      v-btn(
        @click="importErrorDialog = false"
        text
      ) 閉じる
</template>

<script lang="ts">
  import { Clipboard } from '@capacitor/clipboard'
  import { Toast } from '@capacitor/toast'
  import { decryptData } from '@/js/transferEncryption'
  import { useCardsStore } from '@/stores/cards'
  import { useSettingsStore } from '@/stores/settings'

  export default {
    data () {
      return {
        settings: useSettingsStore(),
        cards: useCardsStore(),
        importType: 'json' as 'json' | 'encrypted',
        // JSON形式用
        cardJSON: '',
        bankJSON: '',
        cardJSONError: '',
        bankJSONError: '',
        cardValidationTimeout: null as number | null,
        bankValidationTimeout: null as number | null,
        // 暗号化形式用
        password: '',
        fileLoaded: false,
        importing: false,
        encryptedData: '',
        fileName: '',
        importSuccessDialog: false,
        importErrorDialog: false,
        errorMessage: '',
        importedData: null as any,
      }
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
      // JSON形式用のメソッド
      /** クレジットカードJSONの検証 */
      validateCardJSON () {
        if (this.cardValidationTimeout) {
          clearTimeout(this.cardValidationTimeout)
        }
        this.cardValidationTimeout = window.setTimeout(() => {
          try {
            JSON.parse(this.cardJSON)
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
            JSON.parse(this.bankJSON)
            this.bankJSONError = ''
          } catch (error) {
            this.bankJSONError = error instanceof Error ? `JSONパースエラー: ${error.message}` : 'JSONパースエラーが発生しました'
          }
        }, 10)
      },
      /** クレジットカードJSONのアップロード */
      uploadCardJSON () {
        const input = document.createElement('input')
        input.type = 'file'
        input.accept = '.json'

        input.addEventListener('change', async (e: any) => {
          const file = e.target.files[0]
          if (!file) return

          try {
            const text = await file.text()
            this.cardJSON = text
            this.validateCardJSON()
          } catch {
            this.cardJSONError = 'ファイルの読み込みに失敗しました'
          }
        })

        input.click()
      },
      /** 銀行口座JSONのアップロード */
      uploadBankJSON () {
        const input = document.createElement('input')
        input.type = 'file'
        input.accept = '.json'

        input.addEventListener('change', async (e: any) => {
          const file = e.target.files[0]
          if (!file) return

          try {
            const text = await file.text()
            this.bankJSON = text
            this.validateBankJSON()
          } catch {
            this.bankJSONError = 'ファイルの読み込みに失敗しました'
          }
        })

        input.click()
      },
      /** クレジットカードJSONの貼り付け */
      async pasteCardJSON () {
        try {
          const result = await Clipboard.read()
          if (result.value) {
            this.cardJSON = result.value
            this.validateCardJSON()
          }
        } catch (error) {
          console.error('クリップボードからの読み込みに失敗しました:', error)
        }
      },
      /** 銀行口座JSONの貼り付け */
      async pasteBankJSON () {
        try {
          const result = await Clipboard.read()
          if (result.value) {
            this.bankJSON = result.value
            this.validateBankJSON()
          }
        } catch (error) {
          console.error('クリップボードからの読み込みに失敗しました:', error)
        }
      },
      /** JSONインポートの適用 */
      async applyJSONImport () {
        try {
          const cardData = JSON.parse(this.cardJSON)
          const bankData = JSON.parse(this.bankJSON)
          
          this.cards.cards = cardData
          this.cards.bank = bankData

          this.importedData = {
            cards: cardData,
            bank: bankData,
          }
          this.importSuccessDialog = true

          await Toast.show({
            text: 'データをインポートしました',
            duration: 'short',
          })
        } catch (error) {
          this.errorMessage = 'データのインポートに失敗しました'
          this.importErrorDialog = true
        }
      },

      // 暗号化形式用のメソッド
      async selectFile () {
        try {
          // ファイルピッカーを使用してファイルを選択
          // Capacitorのファイルピッカーを使いたいのでインポートして使いたいが、
          // 現時点では公式プラグインがないため、代替手段としてHTMLのinput要素を使用
          const input = document.createElement('input')
          input.type = 'file'
          input.accept = '.wlt,.txt'

          input.addEventListener('change', async (e: any) => {
            const file = e.target.files[0]
            if (!file) return

            this.fileName = file.name

            try {
              this.encryptedData = await file.text()
              this.fileLoaded = true
            } catch {
              this.errorMessage = 'ファイルの読み込みに失敗しました'
              this.importErrorDialog = true
            }
          })

          input.click()
        } catch (error) {
          console.error('ファイル選択に失敗しました:', error)
          this.errorMessage = 'ファイルの選択に失敗しました'
          this.importErrorDialog = true
        }
      },

      async pasteFromClipboard () {
        try {
          const result = await Clipboard.read()

          if (result.value) {
            this.encryptedData = result.value
            this.fileName = 'クリップボードから'
            this.fileLoaded = true

            await Toast.show({
              text: 'クリップボードから読み込みました',
              duration: 'short',
            })
          } else {
            this.errorMessage = 'クリップボードにデータがありません'
            this.importErrorDialog = true
          }
        } catch (error) {
          console.error('クリップボードからの読み込みに失敗しました:', error)
          this.errorMessage = 'クリップボードからの読み込みに失敗しました'
          this.importErrorDialog = true
        }
      },

      async importData () {
        if (!this.password || !this.encryptedData) {
          return
        }

        this.importing = true

        try {
          // データを復号化
          const decrypted = decryptData(this.encryptedData, this.password)

          if (!decrypted) {
            this.errorMessage = 'パスワードが間違っているか、データが破損しています'
            this.importErrorDialog = true
            this.importing = false
            return
          }

          // バージョンの互換性を検証
          if (!decrypted.version || decrypted.version !== 1) {
            this.errorMessage = `サポートされていないバージョンです (v${decrypted.version || 'unknown'})`
            this.importErrorDialog = true
            this.importing = false
            return
          }

          // 復号化されたデータ構造を検証
          if (!decrypted.cards || !Array.isArray(decrypted.cards)) {
            this.errorMessage = 'カードデータの形式が不正です'
            this.importErrorDialog = true
            this.importing = false
            return
          }

          if (!decrypted.bank || !Array.isArray(decrypted.bank)) {
            this.errorMessage = '銀行口座データの形式が不正です'
            this.importErrorDialog = true
            this.importing = false
            return
          }

          // カードデータ構造を検証
          for (const card of decrypted.cards) {
            if (!card.name || !card.cardNumber || !card.deadlineYYYY || !card.deadlineMM) {
              this.errorMessage = 'カードデータに必須項目が不足しています'
              this.importErrorDialog = true
              this.importing = false
              return
            }
          }

          // 銀行口座データ構造を検証
          for (const bank of decrypted.bank) {
            if (!bank.name || !bank.bankName || !bank.bankCode || !bank.cardNumber) {
              this.errorMessage = '銀行口座データに必須項目が不足しています'
              this.importErrorDialog = true
              this.importing = false
              return
            }
          }

          // データをインポート
          this.cards.cards = decrypted.cards
          this.cards.bank = decrypted.bank

          this.importedData = decrypted
          this.importSuccessDialog = true
          this.importing = false
        } catch (error) {
          console.error('インポートに失敗しました:', error)
          this.errorMessage = 'データのインポートに失敗しました'
          this.importErrorDialog = true
          this.importing = false
        }
      },

      resetImport () {
        this.fileLoaded = false
        this.password = ''
        this.encryptedData = ''
        this.fileName = ''
      },

      closeAndGoHome () {
        this.importSuccessDialog = false
        this.$router.push('/')
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

.file-info {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.1);
  border-radius: 8px;
  padding: 1.5em;
  text-align: center;
}

.import-method {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.1);
  border-radius: 8px;
  padding: 1.5em;
}
</style>
