import { defineStore } from 'pinia'

export const useMyProfileStore = defineStore('myProfile', {
  state: () => ({
    coverImg: null as string | null,
    createdAt: null as number | null,
    icon: null as string | null,
    message: null as string | null,
    name: 'ゲスト' as string | null,
    status: null as string | null,
    // userIdはnullを許可していないため、空文字列などで初期化する
    userId: 'guest',
    userToken: undefined as string | null | undefined,
    guest: true,
  }),
  actions: {
    reset () {
      this.coverImg = null
      this.createdAt = null
      this.icon = null
      this.message = null
      this.name = 'ゲスト'
      this.status = null
      this.userId = 'guest'
      this.userToken = null
      this.guest = true
    },
  },
  persist: true,
})
