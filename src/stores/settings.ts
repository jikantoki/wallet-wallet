import { defineStore } from 'pinia'

export const useSettingsStore = defineStore('settings', {
  state: () => ({
    /** 表示と言語設定 */
    display: {
      /** テーマ */
      theme: 'system' as 'system' | 'light' | 'dark',
      /** 言語 */
      language: '日本語' as const,
      /** カード番号のヒント（下4桁・下3桁）を表示するか */
      showCardNumberHint: true,
    },
    /** 通知設定 */
    notification: {},
    /** 開発者オプション */
    developerOptions: {
      /** 開発者オプションが有効 */
      enabled: false,
      /** ステータスバーのノッチの切り欠き */
      statusBarNotch: 'default' as 'default' | 'true' | 'false',
    },
    /** 画面からは見えない設定 */
    hidden: {
      /** AndroidかつVer.15以上で自動true */
      isAndroid15OrHigher: false,
    },
  }),
  persist: true,
})
