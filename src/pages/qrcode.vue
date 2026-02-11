<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    p.ml-2(style="font-size: 1.3em") QRで友達を探す
    v-spacer
    v-btn(
      text
      @click="$router.back()"
      icon="mdi-close"
      )
  v-card-text(style="height: inherit; overflow-y: auto;")
    p.text-h6.my-4(style="text-align: center;")
      span QRコードスキャン中…
      br
      span カメラにQRコードを映してください
    .qrcode-stream(style="background-color: white;position: relative;")
      QrcodeStream(
        @detect="readQrcode"
        style="position: absolute;"
        )
      .scan-wrap(
        style="width: 100%; height: 100%;z-index: 999;position: absolute;"
      )
    .btns.my-8(
      style="display: flex; justify-content: center;"
    )
      v-btn(
        @click="myQrDialog = true"
        prepend-icon="mdi-qrcode"
        style="color: white; background-color: rgb(var(--v-theme-primary));"
      ) 自分のQRコードを表示
      .my-16
v-dialog(
  v-model="searchFriendLoading"
  persistent
)
  v-card(width="400")
    v-card-title 処理中…
    v-card-text
      v-progress-linear.my-4(indeterminate)
v-dialog(
  v-model="searchResultDialog"
)
  v-card(width="400")
    v-card-title 検索結果
    v-card-text お探しのユーザーが見つかりませんでした
    v-card-actions
      v-btn(
        @click="searchResultDialog = false"
      ) 閉じる
v-dialog(
  v-model="otherResultDialog"
)
  v-card(width="400")
    v-card-title 検索結果
    v-card-text {{ resultValue }}
    v-card-actions
      v-btn(
        @click="open(resultValue)"
        prepend-icon="mdi-earth"
      ) ブラウザで開く
      v-btn(
        @click="copy(resultValue)"
        prepend-icon="mdi-content-copy"
      ) コピー
      v-btn(
        @click="otherResultDialog = false; $router.back()"
        prepend-icon="mdi-close"
      ) 閉じる
v-dialog(
  v-model="myQrDialog"
)
  v-card(width="80vw")
    v-card-title 私のQRコード
    v-card-text
      p.mb-4 以下のURLをコピーして、共有してください
      v-btn.mb-4(
        @click="copy(myLink)"
        append-icon="mdi-content-copy"
        style="color: white;"
        color="var(--accent-color)"
      ) コピー
      pre.pa-4(
        style="border-radius: var(--border-radius);"
      ) {{ myLink }}
      .canvas-area.my-4(
        style="display: flex; justify-content: center;"
      )
        canvas#qr-canvas.ma-2(
          v-show="!qrLoading"
          style="border-radius: 10%; max-width: 20em; max-height: 20em;"
        )
        .qr-loading.ma-2(
          v-show="qrLoading"
          style="width: 70vw; height: 70vw; max-width: 20em; max-height: 20em; background-color: white; border-radius: 10%; display: flex; flex-direction: column; align-items: center; justify-content: center;"
        )
          v-progress-circular.my-4(
            indeterminate
            :size="64"
            color="black"
            )
          p.my-4(
            style="color: black;"
          ) QRコード読み込み中…
    v-card-actions
      v-btn(
        @click="myQrDialog = false"
        prepend-icon="mdi-close"
      ) 閉じる
</template>

<script lang="ts">
  import { Browser } from '@capacitor/browser'
  // import { Clipboard } from '@capacitor/clipboard'
  import { Share } from '@capacitor/share'
  import QRCode from 'qrcode'
  import { QrcodeStream } from 'vue-qrcode-reader'

  import mixins from '@/mixins/mixins'
  import { useMyProfileStore } from '@/stores/myProfile'
  import { useSettingsStore } from '@/stores/settings'

  export default {
    components: {
      QrcodeStream,
    },
    mixins: [mixins],
    data () {
      return {
        searchFriendLoading: false,
        searchResultDialog: false,
        otherResultDialog: false,
        resultValue: '',
        myQrDialog: false,
        qrLoading: false,
        myProfile: useMyProfileStore(),
        myLink: null as null | string,
        settings: useSettingsStore(),
      }
    },
    watch: {
      myQrDialog: {
        handler: async function () {
          await setTimeout(() => {}, 50)
          this.qrLoading = true
          this.myLink = `https://wallet.enoki.xyz/user/${this.myProfile.userId}?openExternalBrowser=1`

          const canvas = document.querySelector('#qr-canvas') as any
          if (!canvas) {
            console.error('not defined canvas')
            return false
          }
          const ctx = canvas.getContext('2d')
          QRCode.toCanvas(
            canvas,
            this.myLink,
            {
              scale: 10,
            },
          )
          canvas.style.height = '70vw'
          canvas.style.width = '70vw'

          const logo = new Image()
          logo.src = '/icon.png'
          logo.addEventListener('load', () => {
            const actualCanvasWidth = canvas.width
            const actualCanvasHeight = canvas.height
            const logoDiameter = actualCanvasWidth * (15 / 70)

            const logoWidth = logoDiameter
            const logoHeight = logoDiameter

            const startX = (actualCanvasWidth / 2) - (logoWidth / 2)
            const startY = (actualCanvasHeight / 2) - (logoHeight / 2)

            ctx.beginPath()
            const rad = logoDiameter / 2
            ctx.arc(actualCanvasWidth / 2, actualCanvasHeight / 2, rad, 0, Math.PI * 2, false)
            ctx.fillStyle = '#FFFFFF'
            ctx.fill()

            ctx.drawImage(logo, startX, startY, logoWidth, logoHeight)
          })

          this.qrLoading = false
        },
      },
    },
    async mounted () {},
    methods: {
      readQrcode (content: any) {
        const val = content[0].rawValue as string
        try {
          const url = new URL(val)
          const pathname = url.pathname
          const userId = pathname.match(/\/user\/([^\/]+)\/?$/)
          if (userId) {
            this.searchFriend(userId[1] ?? '')
          } else {
            throw new Error('謎のURL')
          }
        } catch {
          // Invalid URL
          this.resultValue = val
          this.otherResultDialog = true
        }
      },
      /** 友達を検索 */
      async searchFriend (searchId: string) {
        this.searchFriendLoading = true
        if (!searchId) {
          this.searchResultDialog = true
          return
        }
        const userData = await this.getProfile(
          searchId,
        )
        if (userData) {
          this.$router.push(`/user/${userData.userId}`)
        } else {
          this.searchResultDialog = true
          this.searchFriendLoading = false
          return
        }
        this.searchFriendLoading = false
      },
      /** コンテンツの共有 */
      async copy (content: string) {
        await Share.share({
          title: 'Wallet Walletで位置情報を共有しよう',
          url: content,
        })
        this.searchResultDialog = false
      },
      async open (content: string) {
        await Browser.open({
          url: content,
        })
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
