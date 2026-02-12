<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    p.ml-2(style="font-size: 1.3em") データ転送（インポート）
    v-spacer
    v-btn(
      text
      @click="$router.back()"
      icon="mdi-close"
      )
  v-card-text(style="height: inherit; overflow-y: auto;")
    .content
      h2 QRコードからデータをインポート
      p.my-4 転送元の端末で生成したQRコードをスキャンして、データをインポートします。
      
      v-alert.my-4(
        type="warning"
        variant="tonal"
      )
        | 既存のカードと銀行口座データは
        strong 上書き
        | されます。事前にバックアップを取ることをお勧めします。
      
      .qrcode-stream.my-4(
        v-if="!scanned"
        style="background-color: white;position: relative;"
      )
        QrcodeStream(
          @detect="readQrcode"
          style="position: absolute;"
        )
        .scan-wrap(
          style="width: 100%; height: 100%;z-index: 999;position: absolute;"
        )
      
      .scanned-content.my-4(v-if="scanned")
        v-icon.mb-2(color="success" size="64") mdi-check-circle
        h3 QRコードをスキャンしました
        
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
          @click="resetScan"
          prepend-icon="mdi-refresh"
          variant="text"
          :disabled="importing"
          block
        ) 再スキャン
      
      .my-16

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
      p.mt-2
        strong カード: {{ importedData?.cards?.length || 0 }}件
        br
        strong 銀行口座: {{ importedData?.bank?.length || 0 }}件
    v-card-actions
      v-spacer
      v-btn(
        @click="closeAndGoHome"
        style="background-color: rgb(var(--v-theme-primary)); color: white;"
      ) 閉じる

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
  import { QrcodeStream } from 'vue-qrcode-reader'
  import { useCardsStore } from '@/stores/cards'
  import { useSettingsStore } from '@/stores/settings'
  import { decryptData } from '@/js/transferEncryption'

  export default {
    components: {
      QrcodeStream,
    },
    data () {
      return {
        settings: useSettingsStore(),
        cards: useCardsStore(),
        password: '',
        scanned: false,
        importing: false,
        encryptedData: '',
        importSuccessDialog: false,
        importErrorDialog: false,
        errorMessage: '',
        importedData: null as any,
      }
    },
    methods: {
      async readQrcode (content: any) {
        const val = content[0].rawValue as string
        
        try {
          // Check if it's a wallet transfer QR code
          if (!val.startsWith('wallet-transfer:')) {
            this.errorMessage = 'これはWallet Wallet転送用のQRコードではありません'
            this.importErrorDialog = true
            return
          }
          
          // Extract and parse the data
          const base64Data = val.substring('wallet-transfer:'.length)
          const jsonString = atob(base64Data)
          const transferData = JSON.parse(jsonString)
          
          if (transferData.v !== 1) {
            this.errorMessage = 'サポートされていないバージョンのQRコードです'
            this.importErrorDialog = true
            return
          }
          
          this.encryptedData = transferData.d
          this.scanned = true
        } catch (error) {
          console.error('QR code parsing failed:', error)
          this.errorMessage = 'QRコードの読み取りに失敗しました'
          this.importErrorDialog = true
        }
      },
      
      async importData () {
        if (!this.password || !this.encryptedData) {
          return
        }
        
        this.importing = true
        
        try {
          // Decrypt the data
          const decrypted = decryptData(this.encryptedData, this.password)
          
          if (!decrypted) {
            this.errorMessage = 'パスワードが間違っているか、データが破損しています'
            this.importErrorDialog = true
            this.importing = false
            return
          }
          
          // Validate the decrypted data
          if (!decrypted.cards || !decrypted.bank) {
            this.errorMessage = 'データ形式が不正です'
            this.importErrorDialog = true
            this.importing = false
            return
          }
          
          // Import the data
          this.cards.cards = decrypted.cards
          this.cards.bank = decrypted.bank
          
          this.importedData = decrypted
          this.importSuccessDialog = true
          this.importing = false
        } catch (error) {
          console.error('Import failed:', error)
          this.errorMessage = 'データのインポートに失敗しました'
          this.importErrorDialog = true
          this.importing = false
        }
      },
      
      resetScan () {
        this.scanned = false
        this.password = ''
        this.encryptedData = ''
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
  max-width: 600px;
  margin: 0 auto;
}

.qrcode-stream {
  width: 95%;
  height: calc(50vh - 40px - 16px);
  border-radius: 32px;
  overflow: hidden;
  margin: 0 auto;
}

.scanned-content {
  text-align: center;
}
</style>
