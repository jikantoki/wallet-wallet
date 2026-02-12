<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    p.ml-2(style="font-size: 1.3em") データ転送（エクスポート）
    v-spacer
    v-btn(
      text
      @click="$router.back()"
      icon="mdi-close"
      )
  v-card-text(style="height: inherit; overflow-y: auto;")
    .content
      h2 QRコードでデータを転送
      p.my-4 カードと銀行口座のデータをQRコードで他の端末に転送できます。パスワードを設定して暗号化されます。

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
        @click="generateQRCode"
        prepend-icon="mdi-qrcode"
        style="background-color: rgb(var(--v-theme-primary)); color: white;"
        :disabled="!canGenerate"
        block
      ) QRコードを生成

      .qr-display.mt-8(v-if="qrGenerated")
        h3.mb-4 QRコード
        p.mb-4 このQRコードをインポート先の端末でスキャンしてください
        .canvas-area(
          style="display: flex; justify-content: center;"
        )
          canvas#transfer-qr-canvas(
            v-show="!qrLoading"
            style="border-radius: 10%; max-width: 20em; max-height: 20em; background: white;"
          )
          .qr-loading(
            v-show="qrLoading"
            style="width: 70vw; height: 70vw; max-width: 20em; max-height: 20em; background-color: white; border-radius: 10%; display: flex; flex-direction: column; align-items: center; justify-content: center;"
          )
            v-progress-circular.my-4(
              indeterminate
              :size="64"
              color="black"
            )
            p.my-4(
              style="color: black;"
            ) QRコード生成中…

        .warning.mt-4.pa-4(
          style="background-color: rgba(var(--v-theme-warning), 0.1); border-radius: 8px;"
        )
          v-icon(color="warning") mdi-alert
          p.mt-2
            strong 重要：
            | QRコードには暗号化されたデータが含まれています。パスワードを忘れないようにしてください。

      .my-16

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
  import QRCode from 'qrcode'
  import { encryptData } from '@/js/transferEncryption'
  import { useCardsStore } from '@/stores/cards'
  import { useSettingsStore } from '@/stores/settings'

  export default {
    data () {
      return {
        settings: useSettingsStore(),
        cards: useCardsStore(),
        password: '',
        passwordConfirm: '',
        qrGenerated: false,
        qrLoading: false,
        errorDialog: false,
        errorMessage: '',
      }
    },
    computed: {
      canGenerate (): boolean {
        return this.password.length >= 8
          && this.password === this.passwordConfirm
      },
    },
    methods: {
      onPasswordChange () {
        this.qrGenerated = false
      },
      async generateQRCode () {
        if (!this.canGenerate) {
          return
        }

        this.qrLoading = true
        this.qrGenerated = true

        try {
          // Prepare data to export
          // version: Data format version for future compatibility checks
          // timestamp: Export timestamp for reference (not currently used in validation)
          const exportData = {
            cards: this.cards.cards,
            bank: this.cards.bank,
            version: 1,
            timestamp: new Date().toISOString(),
          }

          // Encrypt the data
          const encryptedData = encryptData(exportData, this.password)

          // Create transfer URL with encrypted data
          const transferData = {
            d: encryptedData, // encrypted data
            v: 1, // version
          }
          const dataString = JSON.stringify(transferData)

          // For QR code, we'll use a URL scheme
          const qrData = `wallet-transfer:${btoa(dataString)}`

          // Wait a bit for DOM to be ready
          await new Promise(resolve => setTimeout(resolve, 100))

          // Generate QR code
          const canvas = document.querySelector('#transfer-qr-canvas') as any
          if (!canvas) {
            console.error('Canvas not found')
            this.qrLoading = false
            return
          }

          await QRCode.toCanvas(
            canvas,
            qrData,
            {
              scale: 8,
              margin: 2,
              errorCorrectionLevel: 'M',
            },
          )

          canvas.style.height = '70vw'
          canvas.style.width = '70vw'

          this.qrLoading = false
        } catch (error) {
          console.error('QR code generation failed:', error)
          this.errorMessage = 'QRコード生成に失敗しました'
          this.errorDialog = true
          this.qrLoading = false
          this.qrGenerated = false
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
  max-width: 600px;
  margin: 0 auto;
}

.qr-display {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.1);
  border-radius: 8px;
  padding: 1em;
}
</style>
