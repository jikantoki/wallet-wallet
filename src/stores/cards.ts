import { defineStore } from 'pinia'

/** カードの構造 */
export type Card = {
  /** カード名 */
  name: string
  /** 16桁のカード番号 */
  cardNumber: string
  /** 有効期限（西暦4桁） */
  deadlineYYYY: string
  /** 有効期限（月） */
  deadlineMM: string
  /** CVC */
  cvc: string
  /** カード名義人（ローマ字） */
  ownName: string
  /** 何かメモする事項があれば */
  memo: string | null
}

/** 銀行カードの構造 */
export type Bank = {
  /** カード名 */
  name: string
  /** 銀行名 */
  bankName: string
  /** 銀行コード */
  bankCode: string
  /** 支店名 */
  shopName: string
  /** 支店コード */
  shopCode: string
  /** 普通か当座か */
  type: '普通' | '当座'
  /** 7桁の口座番号 */
  cardNumber: string
  /** カード名義人（カタカナ） */
  ownName: string
  /** 何かメモする事項があれば */
  memo: string | null
}

export const useCardsStore = defineStore('cards', {
  state: () => ({
    cards: [] as Card[],
    bank: [] as Bank[],
  }),
  persist: true,
})
