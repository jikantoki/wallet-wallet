<template lang="pug">
.user-page(v-if="param" style="height: 85vh;")
  v-card-actions
    p.ml-2(style="font-size: 1.3em") アカウント詳細
    v-spacer
    v-btn(
      text
      @click="$router.push('/')"
      icon="mdi-close"
      )
  .profile-zone(
    style="overflow-y: auto; height: 100%;"
    )
    .cover
      img.cover-img(
        v-if="userData && userData.coverImg"
        :src="userData.coverImg"
        onerror="this.src='/img/default_cover.jpg'"
        )
      img.cover-img(v-else src="/img/default_cover.jpg")
    .icon-and-follow.px-2
      .icon
        img.icon-img(
          v-if="userData && userData.icon"
          :src="userData.icon"
          onerror="this.src='/account_default.jpg'"
          )
        img.icon-img(v-else src="/account_default.jpg")
      .button(v-if="loading")
        v-btn.follow-button(
            loading
          ) 友達申請
      .button(v-if="!loading")
        v-btn.follow-button(
          v-if="myProfile.userId == param.userId && !myProfile.guest"
          @click="$router.push('/settings/profile')"
        ) プロフィールを編集
        .follow-button-cover(
          v-else-if="myProfile.userId && !myProfile.guest"
          )
          v-btn.follow-button(
            v-if="userData && userData.friendStatus === 'friend'"
            disabled
            ) 既に友達です！
          v-btn.follow-button(
            v-else-if="userData && userData.friendStatus"
            disabled
            ) 友達申請中
          v-btn.follow-button(
            v-else
            @click="friendRequest(param.userId)"
            :loading="followLoadingNow"
            ) 友達申請
        v-btn.follow-button(
          v-else
          @click="$router.push('/login')"
          ) ログインして友達申請
    .name-and-id.mx-2.mt-2
      .name.text-h5(v-if="!userData")
        ContentLoader.text-h5.loading-text(width="3em")
      .name.text-h5(v-else-if="userData && userData.status != 'ng'") {{ userData.name ? userData.name : userData.userId }}
      .name.text-h5(v-else) unknown user
      .id.text-h7(v-if="!userData")
        ContentLoader.text-h7.loading-text(width="6em")
      .id.text-h7(v-else-if="userData && userData.status != 'ng'") @{{ userData.userId }}
      .id.text-h7(v-else) @unknown
    .message.mx-2.mb-4
      .message-content(v-if="loading") データ取得中…
      .message-content(v-else-if="userData && userData.status != 'ng'" v-html="userData.message ? userData.message : 'ステータスメッセージが設定されていません'")
      .message-content(v-else) ユーザーが存在しません
    //-.createdat.mx-2(v-show="!loading")
      p(
        v-if="userData && userData.status != 'ng'"
        ) {{ new Date(userData.createdAt * 1000) }}からWallet Walletを利用しています
    //.share-and-sns-links.px-2(v-show="userData")
      .share-buttons
        v-btn.mx-2(
          small
          icon="mdi-twitter"
          color="#1DA1F2"
          :href="`https://twitter.com/`"
        )
        v-btn.mx-2(
          small
          icon="mdi-facebook"
          color="#3b5998"
          :href="`https://facebook.com/`"
        )
        v-btn.mx-2(
          small
          icon="mdi-instagram"
          color="#fc2d9f"
          :href="`https://instagram.com/`"
        )
        v-btn.mx-2(
          small
          icon="mdi-youtube"
          color="#FF0033"
          :href="`https://youtube.com/`"
        )
        v-btn.mx-2(
          small
          icon="mdi-chat"
          color="#06C755"
          :href="`https://lin.ee/`"
        )
        v-btn.mx-2(
          small
          icon="mdi-gmail"
          color="#DC483C"
          :href="`mailto:info@enoki.xyz`"
        )
        v-btn.mx-2(
          small
          icon="mdi-earth"
          color="#1ef783"
          :href="`https://enoki.xyz`"
        )
        v-btn.mx-2(
          small
          icon="mdi-share-variant"
          color="#f7e91e"
          @click="shareMyLinkDialog = true"
        )
    .canvas.ma-4(
      style="display: flex; flex-direction: column; align-items: center"
    )
      p.ma-2(
        style="font-size: 1.5em;"
        v-show="!noAccountExist"
      ) このプロフィールを共有
      p.ma-2(
        style="font-size: 1.5em;"
        v-show="noAccountExist"
      ) ユーザーが存在しません
      v-btn.ma-2(
        @click="copy(this.myLink)"
        append-icon="mdi-share-variant"
        style="background-color: rgb(var(--v-theme-primary)); color: white; border-radius: 8px;"
        size="large"
        v-show="!noAccountExist"
      ) シェア
      v-btn.ma-2(
        @click="$router.push('/')"
        append-icon="mdi-home-outline"
        style="background-color: rgb(var(--v-theme-primary)); color: white; border-radius: 8px;"
        size="large"
        v-show="noAccountExist"
      ) トップへ戻る
      canvas#qr-canvas.ma-2(
        v-show="!qrLoading && myProfile.userId == param.userId"
        style="border-radius: 10%; max-width: 20em; max-height: 20em;"
      )
      .qr-loading.ma-2(
        v-show="qrLoading && myProfile.userId == param.userId"
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
      .my-16.text-h4(
        v-show="myProfile.userId != param.userId"
        style="text-align: center;"
      ) 友達を増やそう！
v-dialog(
  v-model="isInvalid"
  style="max-width: 500px;"
  persistent
  )
  v-card
    v-card-title 閲覧不可
    v-card-text このユーザーのプロフィールは、ログインしたユーザーのみ閲覧可能です。
    v-card-actions
      v-spacer
      v-btn(@click="$router.back()" prepend-icon="mdi-arrow-left") 戻る
      v-btn(
        @click="a('/login')"
        prepend-icon="mdi-login"
        style="background-color: var(--accent-color); color: white;"
        ) ログイン
v-dialog(v-model="shareMyLinkDialog")
  v-card(width="85vw")
    v-card-title このプロフィールを共有
    v-card-text
      p.mb-4 以下のURLをコピーして、共有してください
      v-btn.mb-4(
        @click="copy(this.myLink)"
        append-icon="mdi-share-variant"
        style="color: white;"
        color="var(--accent-color)"
      ) 共有
      pre.pa-4(
        style="border-radius: var(--border-radius);"
      ) {{ myLink }}
    v-card-actions
      v-btn(
        @click="shareMyLinkDialog = false"
        variant="elevated"
        append-icon="mdi-check"
        style="color: white;"
        color="var(--accent-color)"
      ) 閉じる
v-dialog(v-model="followDialogMessage")
  v-card
    v-card-title 友達申請
    v-card-text
      p {{ followDialogMessage }}
    v-card-actions
      v-spacer
      v-btn(
        @click="followDialogMessage = null"
        style="background-color: rgb(var(--v-theme-primary));"
      ) 閉じる
</template>

<script lang="ts">
  import { App } from '@capacitor/app'
  import { Clipboard } from '@capacitor/clipboard'
  import { Share } from '@capacitor/share'
  import QRCode from 'qrcode'
  import mixins from '@/mixins/mixins'
  import { useMyProfileStore } from '@/stores/myProfile'

  export default {
    mixins: [mixins],
    data () {
      return {
        param: null as any,
        userData: null as {
          [key: string]: any
        } | null,
        pushMessage: '',
        errorMessage: false,
        successMessage: false,
        isInvalid: false,
        /** 自分のプロフィールリンク */
        myLink: '',
        shareMyLinkDialog: false,
        myProfile: useMyProfileStore(),
        /** フォロー処理中のクルクル表示 */
        followLoadingNow: false,
        followDialogMessage: null as string | null,
        /** ユーザー情報取得中フラグ */
        loading: false,
        /** 友達申請の状態 */
        friendStatus: null as string | null,
        /** QRコード生成中フラグ */
        qrLoading: false,
        /** 存在しないアカウント */
        noAccountExist: false,
      }
    },
    async mounted () {
      this.loading = true
      this.qrLoading = true

      // ユーザー情報の取得
      this.param = this.$route.params
      if (!this.param.userId) {
        return
      }

      /** paramから取得したユーザーID */
      const userId = this.param.userId

      // Ajaxでユーザー情報取得前に、localStorageに情報があれば表示
      const localUserData = localStorage.getItem(`userdata-${userId}`)
      if (localUserData) {
        this.userData = JSON.parse(localUserData)
      }

      this.myLink = `https://wallet.enoki.xyz/user/${userId}?openExternalBrowser=1`

      setTimeout(() => {
        const canvas = document.querySelector('#qr-canvas') as HTMLCanvasElement
        if (!canvas) return false
        const ctx = canvas.getContext('2d')
        if (!ctx) return false
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
      }, 100)

      this.userData = await this.getProfile(
        userId,
        this.myProfile.userId,
        this.myProfile.userToken,
      )
      if (!this.userData) {
        this.loading = false
        this.qrLoading = false
        this.noAccountExist = true
        return
      }
      // 次回はすぐ開けるように、ローカルストレージに保存
      localStorage.setItem(`userdata-${userId}`, JSON.stringify(this.userData))
      if (this.userData.status == 'invalid') {
        // ログインしていないので閲覧不可
        this.isInvalid = true
      }

      App.addListener('backButton', () => {
        if (this.shareMyLinkDialog) {
          this.shareMyLinkDialog = false
        } else {
          this.$router.back()
        }
      })

      this.loading = false
    },
    unmounted () {
      App.removeAllListeners()
    },
    methods: {
      /** 友達申請 */
      async friendRequest (userId: string) {
        this.followLoadingNow = true
        /** サーバーに送信 */
        try {
          const res = await this.sendAjaxWithAuth(
            '/friendRequest.php', {
              id: this.myProfile.userId,
              token: this.myProfile.userToken,
              requestUserId: userId,
            },
          )
          console.log(res)
          switch (res?.body.status) {
            case 'request':
            case 'ok': {
              this.followDialogMessage = '友達申請が完了しました！相手の承認をお待ちください'
              break
            }
            case 'cannot': {
              this.followDialogMessage = '既に友達申請済みです！'
              break
            }
            default: {
              this.followDialogMessage = '友達申請ができませんでした！'
            }
          }
        } catch {}
        await setTimeout(() => {}, 500)
        this.followLoadingNow = false
      },
      /** クリップボードにコピー */
      async copy (content: string) {
        // await Clipboard.write({
        //   string: content,
        // })
        await Share.share({
          title: 'Wallet Walletで位置情報を共有しよう',
          url: content,
        })
      },
    },
  }
</script>

<style lang="scss" scoped>
.user-page {
  .profile-zone {
    .cover {
      .cover-img {
        width: 100%;
        aspect-ratio: 4/1;
        object-fit: cover;
      }
    }
    .icon-and-follow {
      display: flex;
      align-items: flex-end;
      margin-top: -36px;
      .icon {
        height: 72px;
        .icon-img {
          border-radius: 9999px;
          width: 72px;
          height: 72px;
          object-fit: cover;
        }
      }
      .button {
        margin-left: auto;
        .follow-button {
          border-radius: 9999px;
          margin: 0 !important;
        }
      }
    }
    .name-and-id {
      .id {
        opacity: 0.7;
      }
    }
    .createdat {
      opacity: 0.7;
    }
  }
}
</style>
