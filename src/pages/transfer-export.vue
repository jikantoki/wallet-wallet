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
  import { Clipboard } from '@capacitor/clipboard'
  import { Directory, Filesystem } from '@capacitor/filesystem'
  import { Share } from '@capacitor/share'
  import { Toast } from '@capacitor/toast'
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
    methods: {
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
  max-width: 600px;
  margin: 0 auto;
}

.info-box {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.1);
  border-radius: 8px;
  padding: 1.5em;
  text-align: center;
}
</style>
