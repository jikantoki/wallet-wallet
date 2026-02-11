<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    p.ml-2(style="font-size: 1.3em") カードを登録
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
        placeholder="○○銀行"
        label="カード名"
        autocomplete="username"
        clearable
        ref="cardName"
        required
        prepend-inner-icon="mdi-tag"
        @keydown.enter="$refs.cardNumber.focus()"
        )
      v-text-field(
        v-model="editCard.cardNumber"
        placeholder="0123456789012345"
        label="カード番号（16桁）"
        autocomplete="cc-number"
        clearable
        ref="cardNumber"
        required
        prepend-inner-icon="mdi-credit-card"
        @keydown.enter="$refs.deadlineMM.focus()"
        )
      p {{ searchBrand(editCard.cardNumber) ?? '不明なブランド' }}
      p 有効期限
      .deadline(
        style="display: flex; gap: 16px;"
      )
        v-text-field(
          v-model="editCard.deadlineMM"
          placeholder="2030"
          label="月（MM）"
          autocomplete="cc-exp-month"
          clearable
          ref="deadlineMM"
          required
          @keydown.enter="$refs.deadlineYYYY.focus()"
          )
        v-text-field(
          v-model="editCard.deadlineYYYY"
          placeholder="2030"
          label="年（YYYY）"
          autocomplete="cc-exp-year"
          clearable
          ref="deadlineYYYY"
          required
          @keydown.enter="$refs.cardCVC.focus()"
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
        @keydown.enter="$refs.submitButton.focus()"
        )
    v-card-actions
      v-spacer
      v-btn(
        style=""
        ref="submitButton"
      ) 登録
</template>

<script lang="ts">
  import type { Card } from '@/stores/cards'
  import { Browser } from '@capacitor/browser'
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
        } as Card,
      }
    },
    async mounted () {},
    methods: {
      /** URLをブラウザで開く */
      async openURL (url: string) {
        await Browser.open({ url: url })
      },
    },
  }
</script>

<style lang="scss" scoped>
.top-android-15-or-higher {
  height: calc(100vh - 40px - 16px)!important;
}
</style>
