<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    p.ml-2(style="font-size: 1.3em") プロフィール編集
    v-spacer
    v-btn(
      text
      @click="cancelDialog = true"
      append-icon="mdi-close"
    ) キャンセル
    v-btn(
      text
      @click="save()"
      append-icon="mdi-content-save"
      style="background-color: rgb(var(--v-theme-primary)); color: white;"
      ) 保存
  v-card-text(style="height: inherit; overflow-y: auto;")
    .imgs
      .cover
        img.cover-img(
          :src="myProfile.coverImg"
          onerror="this.src='/img/default_cover.jpg'"
          )
        .change-cover-button(
          style="font-size: 2em;"
          v-ripple
          @click="changeCover()"
          )
          v-icon(
            style="opacity: 0.7;"
          ) mdi-camera-flip
      .icon-cover.mb-6.ml-2
        .icon.cover
          img.icon-img.cover-img(
            v-if="myProfile.icon"
            :src="myProfile.icon"
            onerror="this.src='/account_default.jpg'"
            )
          img.icon-img.cover-img(
            v-else
            src="/account_default.jpg"
            )
          .change-icon-button.change-cover-button(
            style="font-size: 2em;"
            v-ripple
            @click="changeIcon()"
            )
            v-icon(
              style="opacity: 0.7;"
            ) mdi-camera-flip
    .text-form
      v-text-field(
        name="id"
        label="ID"
        disabled
        v-model="myProfile.userId"
      )
      v-text-field(
        name="name"
        label="ニックネーム"
        placeholder="名前を入力してください"
        v-model="myProfile.name"
      )
      v-textarea(
        label="自己紹介"
        placeholder="趣味は色々です"
        clearable
        auto-grow
        v-model="myProfile.message"
      )
      p(
        v-show="settings.developerOptions.enabled"
      ) 開発者オプション
      v-text-field(
        name="icon"
        label="アイコンのURL"
        placeholder="https://icon.com/icon.png"
        v-model="myProfile.icon"
        v-show="settings.developerOptions.enabled"
      )
      v-text-field(
        name="coverImg"
        label="カバー画像のURL"
        placeholder="https://cover.com/cover.png"
        v-model="myProfile.coverImg"
        v-show="settings.developerOptions.enabled"
      )
v-dialog(
  v-model="cancelDialog"
  persistent
)
  v-card
    v-card-title キャンセル
    v-card-text 変更は保存されません。キャンセルしますか？
    v-card-actions
      v-btn(
        @click="cancelDialog = false"
      ) いいえ
      v-btn(
        @click="cancel()"
      ) はい（キャンセル）
v-dialog(
  v-model="saveDialog"
  persistent
)
  v-card(
    width="400"
  )
    v-card-text
      v-card-title 保存中
      v-progress-linear(
        indeterminate
      )
</template>

<script lang="ts">
  import { App } from '@capacitor/app'
  import { Camera, CameraResultType, CameraSource } from '@capacitor/camera'

  import mixins from '@/mixins/mixins'
  import { useMyProfileStore } from '@/stores/myProfile'
  import { useSettingsStore } from '@/stores/settings'

  export default {
    mixins: [mixins],
    data () {
      return {
        myProfile: useMyProfileStore(),
        cancelDialog: false,
        saveDialog: false,
        settings: useSettingsStore(),
      }
    },
    async mounted () {
      App.addListener('backButton', () => {
        this.cancelDialog = this.cancelDialog ? false : true
      })
    },
    unmounted () {
      App.removeAllListeners()
    },
    methods: {
      /** 変更したプロフィールの保存 */
      async save () {
        this.saveDialog = true

        try {
          const res = await this.sendAjaxWithAuth('/updateProfile.php', {
            id: this.myProfile?.userId,
            token: this.myProfile?.userToken,
          }, {
            icon: this.myProfile?.icon,
            coverImg: this.myProfile?.coverImg,
            name: this.myProfile?.name,
            message: this.myProfile?.message,
          })
          let iconUrl = null
          let coverImgUrl = null
          if (res && res.body) {
            iconUrl = res.body.iconUrl
            coverImgUrl = res.body.coverImgUrl
          }

          /** iconとcoverImgはURLを格納する */
          this.myProfile = {
            ...this.myProfile,
            icon: iconUrl,
            coverImg: coverImgUrl,
          }
          const textProfile = JSON.stringify(this.myProfile)
          localStorage.setItem('profile', textProfile)
        } catch (error) {
          if (this.settings.developerOptions.enabled) {
            alert(error)
          }
        }
        this.saveDialog = false
        this.$router.back()
      },
      /** カバー画像の変更 */
      async changeCover () {
        const permission = await Camera.checkPermissions()
        if (permission.camera !== 'granted' || permission.photos !== 'granted') {
          await Camera.requestPermissions()
        }
        const image = await Camera.getPhoto({
          quality: 100,
          resultType: CameraResultType.DataUrl,
          allowEditing: false,
          saveToGallery: false,
          width: 1600,
          height: 1600,
          source: CameraSource.Photos,
          promptLabelHeader: '写真を使う',
          promptLabelCancel: 'キャンセル',
          promptLabelPhoto: 'アルバムから選択',
          promptLabelPicture: '撮影',
        })
        const base64 = image.dataUrl
        if (this.myProfile) {
          this.myProfile.coverImg = base64 ?? null
        }
      },
      /** アイコンの変更 */
      async changeIcon () {
        const permission = await Camera.checkPermissions()
        if (permission.camera !== 'granted' || permission.photos !== 'granted') {
          await Camera.requestPermissions()
        }
        const image = await Camera.getPhoto({
          quality: 100,
          resultType: CameraResultType.DataUrl,
          allowEditing: false,
          saveToGallery: false,
          width: 1600,
          height: 1600,
          source: CameraSource.Photos,
          promptLabelHeader: '写真を使う',
          promptLabelCancel: 'キャンセル',
          promptLabelPhoto: 'アルバムから選択',
          promptLabelPicture: '撮影',
        })
        const base64 = image.dataUrl
        if (this.myProfile) {
          this.myProfile.icon = base64 ?? null
        }
      },
      /** プロフィール編集をキャンセル */
      cancel () {
        // これないとバグる
        this.cancelDialog = false
        this.$router.back()
      },
    },
  }
</script>

<style lang="scss" scoped>
.top-android-15-or-higher {
  height: calc(100vh - 40px - 16px)!important;
}
.cover {
  width: 100%;
  height: 12em;
  position: relative;
  border-radius: 16px;
  overflow: hidden;
  .cover-img {
    width: 100%;
    height: 12em;
  }
  .change-cover-button {
    position: absolute;
    top:50%;
    left:50%;
    transform: translate(-50%,-50%);
    padding: 0;
    margin: 0;
    background: rgba(0,0,0,0.5);
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    color: #FFFFFF;
  }
}
.icon-cover{
  margin-top: -36px;
  z-index: 5;
  position: sticky;
  .icon {
    border-radius: 9999px;
    height: 72px;
    width: 72px;
    .icon-img {
      border-radius: 9999px;
      width: 72px;
      height: 72px;
    }
    .change-icon-button {
      border-radius: 9999px;
    }
  }
}
</style>
