<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    p.ml-2(style="font-size: 1.3em") ポイントカード編集
    v-spacer
    v-btn(
      text
      @click="$router.back()"
      icon="mdi-close"
      )
  v-card-text(style="height: inherit; overflow-y: auto;")
    .card-form
      v-text-field(
        v-model="editCard.name"
        placeholder="○○ポイントカード"
        label="カード名"
        clearable
        ref="cardName"
        required
        prepend-inner-icon="mdi-tag"
        @keydown.enter="$refs.cardData.focus()"
        )
      v-text-field(
        v-model="editCard.cardData"
        placeholder="1234567890"
        label="カードデータ（カード番号など）"
        clearable
        ref="cardData"
        required
        prepend-inner-icon="mdi-barcode"
        @keydown.enter="$refs.ownName.focus()"
        )
      v-btn.mb-4(
        prepend-icon="mdi-qrcode-scan"
        variant="outlined"
        @click="scannerDialog = true"
        block
        ) QR/バーコードから読み込む
      p コード種別
      v-btn-toggle.mb-4(
        v-model="editCard.codeType"
        mandatory
        color="primary"
        variant="outlined"
        divided
        style="width: 100%;"
        )
        v-btn(
          value="qr"
          style="flex: 1;"
          )
          v-icon.mr-2 mdi-qrcode
          | QRコード
        v-btn(
          value="barcode"
          style="flex: 1;"
          )
          v-icon.mr-2 mdi-barcode
          | バーコード
      v-text-field(
        v-model="editCard.ownName"
        placeholder="○○太郎"
        label="名義"
        clearable
        ref="ownName"
        prepend-inner-icon="mdi-account"
        @keydown.enter="$refs.cardMemo.focus()"
        )
      v-text-field(
        v-model="editCard.memo"
        placeholder="よく使うお店のポイントカード"
        label="メモ"
        clearable
        ref="cardMemo"
        prepend-inner-icon="mdi-note-outline"
        @keydown.enter="$refs.submitButton.$el.focus()"
        )
      v-text-field(
        readonly
        prepend-inner-icon="mdi-palette-outline"
        hide-details
        :style="`background-color: ${editCard.color}; transition: .2s;`"
        ) ↓色を選択↓
      v-color-picker.my-4(
        v-model="editCard.color"
        hide-canvas
        hide-inputs
        show-swatches
        hide-sliders
        mode="hexa"
        width="100%"
        )
    v-card-actions
      v-spacer
      v-btn(
        style="background-color: rgb(var(--v-theme-primary)); color: white;"
        ref="submitButton"
        prepend-icon="mdi-check"
        @click="updatePointCard"
        ) 更新
    .ma-16
  //-- QR/バーコードスキャナーダイアログ --
  v-dialog(
    v-model="scannerDialog"
    fullscreen
    )
    v-card
      v-card-actions
        p.ml-2(style="font-size: 1.3em") QR/バーコードスキャン
        v-spacer
        v-btn(
          text
          @click="scannerDialog = false"
          icon="mdi-close"
          )
      v-card-text
        p.my-4(style="text-align: center;") カメラにQRコードまたはバーコードを映してください
        .qrcode-stream(style="background-color: white; position: relative;")
          QrcodeStream(
            @detect="onScanDetect"
            style="position: absolute;"
            )
          .scan-wrap(
            style="width: 100%; height: 100%; z-index: 999; position: absolute;"
            )
</template>

<script lang="ts">
  import type { PointCard } from '@/stores/cards'
  import { App } from '@capacitor/app'
  import { Toast } from '@capacitor/toast'
  import { QrcodeStream } from 'vue-qrcode-reader'
  import mixins from '@/mixins/mixins'
  import { useCardsStore } from '@/stores/cards'
  import { useSettingsStore } from '@/stores/settings'

  export default {
    components: {
      QrcodeStream,
    },
    mixins: [mixins],
    data () {
      return {
        settings: useSettingsStore(),
        cards: useCardsStore(),
        pointCardIndex: -1,
        editCard: {
          name: '',
          cardData: '',
          codeType: 'qr',
          ownName: '',
          memo: '',
          color: '',
        } as PointCard,
        scannerDialog: false,
      }
    },
    async mounted () {
      const params: any = this.$route.params
      const indexParam = params.index as string | undefined
      if (!indexParam) {
        Toast.show({ text: 'ポイントカード情報が見つかりません' })
        this.$router.push('/')
        return
      }

      this.pointCardIndex = Number.parseInt(indexParam)

      if (Number.isNaN(this.pointCardIndex) || this.pointCardIndex < 0 || this.pointCardIndex >= this.cards.pointCards.length) {
        Toast.show({ text: 'ポイントカード情報が見つかりません' })
        this.$router.push('/')
        return
      }

      const card = this.cards.pointCards[this.pointCardIndex]
      if (card) {
        this.editCard = {
          name: card.name,
          cardData: card.cardData,
          codeType: card.codeType,
          ownName: card.ownName,
          memo: card.memo,
          color: card.color,
        }
      }

      App.addListener('backButton', () => {
        if (this.scannerDialog) {
          this.scannerDialog = false
        } else {
          this.$router.back()
        }
      })
    },
    unmounted () {
      App.removeAllListeners()
    },
    methods: {
      onScanDetect (content: any) {
        const val = content[0].rawValue as string
        this.editCard.cardData = val
        this.scannerDialog = false
        Toast.show({ text: 'スキャンしました' })
      },
      updatePointCard () {
        if (this.editCard.cardData.length === 0) {
          Toast.show({ text: 'カードデータを入力してください' })
          return
        }
        if (this.editCard.name.length === 0) {
          this.editCard.name = 'ポイントカード'
        }
        this.cards.pointCards.splice(this.pointCardIndex, 1, { ...this.editCard })
        Toast.show({ text: 'ポイントカードを更新しました' })
        this.$router.push('/')
      },
    },
  }
</script>

<style lang="scss" scoped>
.top-android-15-or-higher {
  height: calc(100vh - 40px - 16px)!important;
}
.qrcode-stream {
  width: 95%;
  height: calc(60vh - 40px - 16px);
  border-radius: 32px;
  overflow: hidden;
  justify-self: center;
}
</style>
