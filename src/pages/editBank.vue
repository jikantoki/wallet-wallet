<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    p.ml-2(style="font-size: 1.3em") 口座編集
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
        :placeholder="editCard.bankName.length ? editCard.bankName : '○○銀行のカード'"
        :label="`${editCard.bankName.length ? editCard.bankName : 'カード名'}`"
        clearable
        ref="cardName"
        prepend-inner-icon="mdi-tag"
        @keydown.enter="$refs.bankNameCode.focus()"
        )
      .deadline(
        style="display: flex; gap: 16px;"
      )
        v-autocomplete(
          v-model="editCard.bankCode"
          placeholder="0000"
          label="銀行コード"
          ref="bankNameCode"
          required
          :items="bankList"
          item-title="value"
          @keydown.enter="$refs.bankName.focus()"
          )
        v-autocomplete(
          v-model="editCard.bankCode"
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
          v-model="editCard.shopCode"
          placeholder="000"
          label="支店コード"
          ref="shopNameCode"
          required
          :items="bankBranches"
          item-title="code"
          @keydown.enter="$refs.shopName.focus()"
          :disabled="!editCard.bankCode"
          )
        v-autocomplete(
          v-model="editCard.shopCode"
          placeholder="○○支店"
          label="支店名"
          ref="shopName"
          required
          :items="bankBranches"
          @keydown.enter="$refs.type.focus()"
          style="width: 50%;"
          suffix="支店"
          :disabled="!editCard.bankCode"
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
        @input="onCardNumberInput"
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
        @click="updateBank"
      ) 更新
    .ma-16
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
        bankIndex: -1,
        editCard: {
          name: '',
          /** 銀行コード */
          bankCode: '',
          /** 銀行名・コードが入力されたら自動で反映する */
          bankName: '',
          /** 支店コード */
          shopCode: '',
          /** 支店名・コードが入力されたら自動で反映する */
          shopName: '',
          type: '普通',
          cardNumber: '',
          ownName: '',
          memo: '',
          color: '',
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
      'editCard.bankCode': {
        handler (bankCode: string) {
          const banks = Object.values(this.zenginCode) as any[]
          for (const bank of banks) {
            if (bankCode !== this.zenginCode[bank.code].name
              && bankCode !== this.zenginCode[bank.code].code
            ) {
              continue
            }
            // 銀行名を自動反映しておく
            this.editCard.bankName = this.zenginCode[bank.code].name
            /** 支店リスト */
            const branches = this.zenginCode[bank.code].branches
            // 整形する
            this.bankBranches = Object.values(branches).map((branch: any) => ({
              text: branch.hira,
              title: branch.name,
              value: branch.code,
              code: branch.code,
            }))
            break
          }
        },
        deep: true,
        immediate: true,
      },
      'editCard.shopCode': {
        handler (shopCode: string) {
          for (const branch of this.bankBranches) {
            if (branch.code != shopCode) {
              continue
            }
            // 支店名を入力する
            this.editCard.shopName = branch.title
          }
        },
        deep: true,
        immediate: true,
      },
    },
    async mounted () {
      // Get the bank index from route params
      this.bankIndex = parseInt(this.$route.params.index as string)
      
      // Check if the index is valid
      if (isNaN(this.bankIndex) || this.bankIndex < 0 || this.bankIndex >= this.cards.bank.length) {
        Toast.show({ text: '口座情報が見つかりません' })
        this.$router.push('/')
        return
      }

      // Load the existing bank data
      const bank = this.cards.bank[this.bankIndex]
      this.editCard = {
        name: bank.name,
        bankCode: bank.bankCode,
        bankName: bank.bankName,
        shopCode: bank.shopCode,
        shopName: bank.shopName,
        type: bank.type,
        cardNumber: bank.cardNumber,
        ownName: bank.ownName,
        memo: bank.memo,
        color: bank.color,
      }

      App.addListener('backButton', () => {
        this.$router.back()
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
      /** 口座番号入力時に7桁になったら次のフィールドに移動 */
      onCardNumberInput () {
        if (this.editCard.cardNumber?.length === 7) {
          (this.$refs.ownName as any).focus()
        }
      },
      updateBank () {
        if (
          this.editCard.cardNumber.length > 0
          && this.editCard.cardNumber.length <= 7
          && this.editCard.ownName.length > 0
          && this.editCard.bankName.length > 0
          && this.editCard.shopName.length > 0
          && this.editCard.type.length > 0
        ) {
          if (this.editCard.name.length === 0) {
            this.editCard.name = this.editCard.bankName
          }
          // Update the bank at the specified index
          this.cards.bank[this.bankIndex] = this.editCard
          Toast.show({ text: '口座情報を更新しました' })
          this.$router.push('/')
        } else {
          Toast.show({ text: '口座情報に不備があるため更新できません' })
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
