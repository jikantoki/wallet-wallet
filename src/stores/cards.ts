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

export const useCardsStore = defineStore('cards', {
  state: () => ({
    cards: [] as Card[],
  }),
  persist: true,
})
