<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    p.ml-2(style="font-size: 1.3em") 新規口座登録
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
        placeholder="○○銀行のカード"
        label="カード名"
        autocomplete="username"
        clearable
        ref="cardName"
        required
        prepend-inner-icon="mdi-tag"
        @keydown.enter="$refs.bankNameCode.focus()"
        )
      .deadline(
        style="display: flex; gap: 16px;"
      )
        v-autocomplete(
          v-model="editCard.bankName"
          placeholder="0000"
          label="銀行コード"
          ref="bankNameCode"
          required
          :items="bankList"
          item-title="value"
          @keydown.enter="$refs.bankName.focus()"
          )
        v-autocomplete(
          v-model="editCard.bankName"
          placeholder="○○銀行"
          label="銀行名"
          ref="bankName"
          required
          :items="bankList"
          @keydown.enter="$refs.shopNameCode.focus()"
          style="width: 50%;"
          suffix="銀行"
          )
      .deadline(
        style="display: flex; gap: 16px;"
      )
        v-autocomplete(
          v-model="editCard.shopName"
          placeholder="000"
          label="支店コード"
          ref="shopNameCode"
          required
          :items="bankBranches"
          item-title="code"
          @keydown.enter="$refs.shopName.focus()"
          )
        v-autocomplete(
          v-model="editCard.shopName"
          placeholder="○○支店"
          label="支店名"
          ref="shopName"
          required
          :items="bankBranches"
          @keydown.enter="$refs.type.focus()"
          style="width: 50%;"
          suffix="支店"
          )
      v-select(
        v-model="editCard.type"
        :items="['普通', '当座']"
        label="種類"
        clearable
        ref="type"
        required
        @keydown.enter="$refs.cardNumber.focus()"
      )
      v-text-field(
        v-model="editCard.cardNumber"
        placeholder="0123456"
        label="カード番号（7桁）"
        clearable
        ref="cardNumber"
        required
        prepend-inner-icon="mdi-credit-card"
        @keydown.enter="$refs.ownName.focus()"
        type="number"
        :rules="[v => /[0-9]/.test(v) && v.length <= 7 || '数字のみ7桁まで']"
        hide-spin-buttons
        maxlength="7"
        )
      v-text-field(
        v-model="editCard.ownName"
        placeholder="ﾐｮｳｼﾞ ﾅﾏｴ"
        label="名義"
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
        style="background-color: rgb(var(--v-theme-primary)); color: white;"
        ref="submitButton"
        prepend-icon="mdi-check"
        @click="addCardList(editCard)"
      ) 登録
</template>

<script lang="ts">
  import type { Bank } from '@/stores/cards'
  import { App } from '@capacitor/app'
  import { Browser } from '@capacitor/browser'
  import { Toast } from '@capacitor/toast'
  // @ts-ignore
  import zenginCode from 'zengin-code'
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
          bankName: '',
          shopName: '',
          type: '普通',
          cardNumber: '',
          ownName: '',
          memo: '',
        } as Bank,
        /** 銀行コード */
        zenginCode,
        bankBranches: [] as any,
      }
    },
    computed: {
      bankList () {
        // オブジェクトの「値」の配列を作成し、それを整形して返す
        return Object.values(this.zenginCode).map((bank: any) => ({
          text: bank.hira,
          title: bank.name,
          value: bank.code,
          branches: bank.branches,
        }))
      },
    },
    watch: {
      /** 銀行名が変わった時 */
      'editCard.bankName': {
        handler (bankName: string) {
          const banks = Object.values(this.zenginCode) as any[]
          for (const bank of banks) {
            if (bankName !== this.zenginCode[bank.code].name
              && bankName !== this.zenginCode[bank.code].code
            ) {
              continue
            }
            const branches = this.zenginCode[bank.code].branches
            this.bankBranches = Object.values(branches).map((branch: any) => ({
              text: branch.hira,
              title: branch.name,
              value: branch.code,
              code: branch.code,
            }))
            break
          }
          this.editCard.shopName = ''
        },
        deep: true,
        immediate: true,
      },
    },
    async mounted () {
      App.addListener('backButton', () => {
        if (this.editCard.name.length > 0
          || this.editCard.cardNumber.length > 0
          || this.editCard.bankName.length > 0
          || this.editCard.shopName.length > 0
          || this.editCard.type.length > 0
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
      addCardList (card: Bank) {
        if (this.editCard.name.length > 0
          && this.editCard.cardNumber.length > 0
          && this.editCard.cardNumber.length <= 7
          && this.editCard.ownName.length > 0
          && this.editCard.bankName.length > 0
          && this.editCard.shopName.length > 0
          && this.editCard.type.length > 0
        ) {
          this.cards.bank.push(card)
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
