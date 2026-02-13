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
  import { Filesystem, Directory } from '@capacitor/filesystem'
  import { Clipboard } from '@capacitor/clipboard'
  import { Toast } from '@capacitor/toast'
  import { useCardsStore } from '@/stores/cards'
  import { useSettingsStore } from '@/stores/settings'
  import { decryptData } from '@/js/transferEncryption'

  export default {
    data () {
      return {
        settings: useSettingsStore(),
        cards: useCardsStore(),
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
    methods: {
      async selectFile () {
        try {
          // ファイルピッカーを使用してファイルを選択
          // Capacitorのファイルピッカーはプラグインが必要なので、
          // 代わりにinput[type=file]を使用
          const input = document.createElement('input')
          input.type = 'file'
          input.accept = '.wlt,.txt'
          
          input.onchange = async (e: any) => {
            const file = e.target.files[0]
            if (!file) return
            
            this.fileName = file.name
            
            const reader = new FileReader()
            reader.onload = (e: any) => {
              this.encryptedData = e.target.result
              this.fileLoaded = true
            }
            reader.onerror = () => {
              this.errorMessage = 'ファイルの読み込みに失敗しました'
              this.importErrorDialog = true
            }
            reader.readAsText(file)
          }
          
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
  max-width: 600px;
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
