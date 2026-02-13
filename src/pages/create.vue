<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    p.ml-2(style="font-size: 1.3em") 新規クレジットカード登録
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
        :placeholder="`${brand}の○○カード`"
        :label="`${brand !== '不明なブランド' ? `${brand}の○○カード` : 'カード名'}`"
        clearable
        ref="cardName"
        required
        prepend-inner-icon="mdi-tag"
        @keydown.enter="$refs.cardNumber.focus()"
        )
      v-text-field(
        v-model="editCard.cardNumber"
        v-maska="'#### #### #### ####'"
        placeholder="0123 4567 8901 2345"
        label="カード番号（16桁）"
        autocomplete="cc-number"
        inputmode="numeric"
        clearable
        ref="cardNumber"
        required
        prepend-inner-icon="mdi-credit-card"
        @keydown.enter="$refs.deadlineMM.focus()"
        type="text"
        :rules="[v => /^[0-9\s]*$/.test(v) && (v.replace(/\s/g, '').length >= 14 && v.replace(/\s/g, '').length <= 16 || v.replace(/\s/g, '').length === 0) || '数字のみ14〜16桁']"
        maxlength="19"
        :counter="19"
        )
      v-text-field(
        v-model="brand"
        label="ブランド"
        prepend-inner-icon="mdi-earth"
        readonly
        )
      p 有効期限
      .deadline(
        style="display: flex; gap: 16px;"
      )
        v-text-field(
          v-model="editCard.deadlineMM"
          placeholder="00"
          label="月（MM）"
          autocomplete="cc-exp-month"
          clearable
          ref="deadlineMM"
          required
          @keydown.enter="$refs.deadlineYYYY.focus()"
          @input="onDeadlineMMInput"
          type="number"
          :rules="[v => /[0-9]/.test(v) && v.length <= 2 || '数字のみ2桁まで']"
          hide-spin-buttons
          )
        v-text-field(
          v-model="editCard.deadlineYYYY"
          placeholder="2030"
          label="年（YYもしくはYYYY）"
          autocomplete="cc-exp-year"
          clearable
          ref="deadlineYYYY"
          required
          @keydown.enter="$refs.cardCVC.focus()"
          @input="onDeadlineYYYYInput"
          type="number"
          :rules="[v => /[0-9]/.test(v) && v.length <= 4 || '数字のみ4桁まで']"
          hide-spin-buttons
          )
      v-text-field(
        v-model="editCard.cvc"
        placeholder="012"
        label="CVC"
        autocomplete="cc-csc"
        clearable
        ref="cardCVC"
        required
        prepend-inner-icon="mdi-form-textbox-password"
        @keydown.enter="$refs.ownName.focus()"
        @input="onCVCInput"
        type="number"
          :rules="[v => /[0-9]/.test(v) && v.length <= 4 || '数字のみ4桁まで']"
        hide-spin-buttons
        )
      v-text-field(
        v-model="editCard.ownName"
        placeholder="NAMAE MYOJI"
        label="名義"
        autocomplete="cc-name"
        clearable
        ref="ownName"
        required
        prepend-inner-icon="mdi-account"
        @keydown.enter="$refs.cardMemo.focus()"
        )
      v-text-field(
        v-model="editCard.memo"
        placeholder="メインの決済方法"
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
        @click="addCardList(editCard)"
      ) 登録
    .ma-16
</template>

<script lang="ts">
  import type { Card } from '@/stores/cards'
  import { App } from '@capacitor/app'
  import { Browser } from '@capacitor/browser'
  import { Toast } from '@capacitor/toast'
  import mixins from '@/mixins/mixins'
  import { useCardsStore } from '@/stores/cards'
  import { useSettingsStore } from '@/stores/settings'

  export default {
    mixins: [mixins],
    data () {
      return {
        settings: useSettingsStore(),
        cards: useCardsStore(),
        editCard: {
          name: '',
          cardNumber: '',
          deadlineYYYY: '',
          deadlineMM: '',
          cvc: '',
          ownName: '',
          memo: '',
          color: '',
        } as Card,
      }
    },
    computed: {
      /** スペースを除いたカード番号 */
      cardNumberWithoutSpaces () {
        return this.editCard.cardNumber?.replace(/\s/g, '') ?? ''
      },
      /** クレカのブランド名 */
      brand () {
        return this.searchBrand(this.cardNumberWithoutSpaces) ?? '不明なブランド'
      },
    },
    async mounted () {
      App.addListener('backButton', () => {
        if (this.editCard.name.length > 0
          || this.editCard.cardNumber.length > 0
          || this.editCard.deadlineMM.length > 0
          || this.editCard.deadlineYYYY.length > 0
          || this.editCard.cvc.length > 0
          || this.editCard.ownName.length > 0
          || this.editCard.memo?.length
        ) {
          Toast.show({ text: 'フォームを空にしてから戻れます' })
        } else {
          this.$router.back()
        }
      })
    },
    unmounted () {
      App.removeAllListeners()
    },
    methods: {
      /** URLをブラウザで開く */
      async openURL (url: string) {
        await Browser.open({ url: url })
      },
      /** カード番号入力時に16桁になったら次のフィールドに移動 */
      onCardNumberInput () {
        if (this.editCard.cardNumber?.length === 16) {
          (this.$refs.deadlineMM as any).focus()
        }
      },
      /** 有効期限MM入力時に2桁になったら次のフィールドに移動 */
      onDeadlineMMInput () {
        if (this.editCard.deadlineMM?.length === 2) {
          (this.$refs.deadlineYYYY as any).focus()
        }
      },
      /** 有効期限YYYY入力時に4桁になったら次のフィールドに移動 */
      onDeadlineYYYYInput () {
        if (this.editCard.deadlineYYYY?.length === 4) {
          (this.$refs.cardCVC as any).focus()
        }
      },
      /** CVC入力時に3桁になったら次のフィールドに移動 */
      onCVCInput () {
        if (this.editCard.cvc?.length === 3) {
          (this.$refs.ownName as any).focus()
        }
      },
      addCardList (card: Card) {
        if (
          this.cardNumberWithoutSpaces.length >= 14
          && this.cardNumberWithoutSpaces.length <= 16
          && this.editCard.deadlineYYYY.length >= 2
          && this.editCard.deadlineYYYY.length <= 4
          && this.editCard.deadlineMM.length > 0
          && this.editCard.deadlineMM.length <= 2
          && this.editCard.cvc.length > 0
          && this.editCard.cvc.length <= 4
          && this.editCard.ownName.length > 0
        ) {
          if (this.editCard.name.length === 0) {
            this.editCard.name = `${this.brand}のクレジットカード`
          }
          this.cards.cards.push(card)
          Toast.show({ text: 'カード情報を保存しました' })
          this.$router.push('/')
        } else {
          Toast.show({ text: 'カード情報に不備があるため登録できません' })
        }
      },
    },
  }
</script>

<style lang="scss" scoped>
.top-android-15-or-higher {
  height: calc(100vh - 40px - 16px)!important;
}
</style>
