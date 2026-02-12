<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    .ml-2(style="font-size: 1.3em; height: 4em; display: flex; align-items: center;")
      p Wallet Wallet
    v-spacer
  v-card-text(style="height: inherit; overflow-y: auto;")
    h2 クレジットカード（{{ cards.cards.length }}枚）
    .settings-list.my-4(
      v-for="(card, cnt) of cards.cards"
      )
      .setting-item(
        @click="openCard(cnt)"
        v-ripple
        )
        .icon
          v-icon mdi-credit-card
        .text
          h3(
            style="font-size: 1.2em;"
          ) {{ card.name }}
          p {{ searchBrand(card.cardNumber) ?? '不明なブランド' }} ******{{ card.cardNumber.slice(-4) }}
          p.opacity05(
            style="min-height: 1em;"
            ) {{ card.memo.length ? card.memo : '空白のメモ' }}
    .settings-list.my-4(
    )
      .setting-item(
        @click="$router.push('/create')"
        v-ripple
      )
        .icon
          v-icon mdi-plus
        .text
          p 新しいカードを登録
          p.opacity05 ここをクリックして、新しいカードを追加します
    h2 銀行口座（{{ cards.bank.length }}枚）
    .settings-list.my-4(
      v-for="(card, cnt) of cards.bank"
      )
      .setting-item(
        @click="openBank(cnt)"
        v-ripple
        )
        .icon
          v-icon mdi-bank
        .text
          h3(
            style="font-size: 1.2em;"
          ) {{ card.name }}
          p {{ card.bankName }} {{ card.shopName }} ****{{ card.cardNumber.slice(-3) }}
          p.opacity05(
            style="min-height: 1em;"
            ) {{ card.memo.length ? card.memo : '空白のメモ' }}
    .settings-list.my-4(
    )
      .setting-item(
        @click="$router.push('/createBank')"
        v-ripple
      )
        .icon
          v-icon mdi-plus
        .text
          p 新しい銀行口座を登録
          p.opacity05 ここをクリックして、新しい銀行口座を追加します
    .ma-16.pa-16
  //-- 下部のアクションバー --
  .action-bar
    .buttons
      .button(
        v-ripple
        @click="setCurrentPosition"
        )
        v-icon mdi-home
        p トップ
      .button(
        v-ripple
        @click="createTypeSelectDialog = true"
        )
        v-icon mdi-pencil-plus
        p 追加
      .button(
        v-ripple
        @click="optionsDialog = true"
        style="opacity: 0.8;"
        )
        v-icon mdi-dots-vertical
        p その他
    .bottom-android-15-or-higher(v-if="settings.hidden.isAndroid15OrHigher")
  //-- 右下のボタン --
  .right-bottom-buttons
    .current-button
      v-btn(
        size="x-large"
        icon
        @click="createTypeSelectDialog = true"
        style="background-color: rgb(var(--v-theme-primary)); color: white"
        )
        v-icon mdi-pencil-plus
    .bottom-android-15-or-higher(v-if="settings.hidden.isAndroid15OrHigher")
  //-- 左上の友達リストボタン --
  //-- .left-top-buttons
    .top-android-15-or-higher(v-if="settings.hidden.isAndroid15OrHigher")
    .current-button
      v-btn(
        size="x-large"
        icon
        @click="$router.push('/friendlist')"
        style="background-color: rgb(var(--v-theme-primary)); color: white"
        )
        v-icon mdi-account-multiple
  //-- 右上のアカウントボタン --
  .right-top-buttons
    .top-android-15-or-higher(v-if="settings.hidden.isAndroid15OrHigher")
    .account-button
      .button(
        v-ripple
        @click="optionsDialog = true"
        style="cursor: pointer; border-radius: 9999px; height: 4em; width: 4em;"
        )
        img(
          loading="lazy"
          :src="myProfile && myProfile.icon ? myProfile.icon : '/account_default.jpg'"
          style="height: 4em; width: 4em; border-radius: 9999px; border: solid 2px #000;"
          onerror="this.src='/account_default.jpg'"
          )
    //- QRコードボタン
    //- .account-button.my-2
      v-btn(
        v-ripple
        @click="$router.push('/qrcode')"
        icon="mdi-qrcode-scan"
        color="rgb(var(--v-theme-primary)"
        size="x-large"
      )
  //-- 友達検索ダイアログ --
  v-dialog(
    v-model="searchFriendDialog"
  )
    v-card
      v-card-actions(
        style="width: 90vw;"
      )
        p.ml-2 友達を探す
        v-spacer
        v-btn(
          text
          @click="searchFriendDialog = false"
          icon="mdi-close"
          )
      v-card-text
        v-text-field(
          label="友達のID"
          prepend-icon="mdi-account"
          v-model="searchFriendId"
          @keydown="searchFriendErrorMessage = ''"
          @keydown.enter="searchFriend(searchFriendId)"
        )
        p(
          style="height: 1em;"
        ) {{ searchFriendErrorMessage }}
      v-card-actions
        v-btn(
          @click="searchFriend(searchFriendId)"
          prepend-icon="mdi-magnify"
          style="background-color: rgb(var(--v-theme-primary));"
          :loading="searchFriendLoading"
        ) 検索
  //-- オプションダイアログ --
  v-dialog(
    v-model="optionsDialog"
    transition="dialog-bottom-transition"
    fullscreen
  )
    v-card
      .top-android-15-or-higher(v-if="settings.hidden.isAndroid15OrHigher")
      v-card-actions
        p.ml-2(class="headline" style="font-size: 1.3em") ようこそ
        v-spacer
        v-btn(
          text
          @click="optionsDialog = false"
          icon="mdi-close"
          )
      v-card-text
        .account-details(
          style="display: flex; flex-direction: column; align-items: center; gap: 1em; margin-bottom: 1em;"
        )
          .account-img
            img(
              :src="myProfile && myProfile.icon ? myProfile.icon : '/account_default.jpg'"
              style="height: 8em; width: 8em; border-radius: 9999px;"
              onerror="this.src='/account_default.jpg'"
              )
          .account-info(
            style="text-align: center;"
          )
            p(
              v-if="myProfile.userId && !myProfile.guest"
              style="font-size: 1.2em; margin: 0; padding: 0;"
              ) {{ myProfile.name ? myProfile.name : myProfile.userId }}
            p(
              v-else
              style="font-size: 1.2em; margin: 0; padding: 0;"
              ) ログインしていません
            p(style="margin: 0; padding: 0;")
              | {{ myProfile.userId && !myProfile.guest ? `@${myProfile.userId}` : 'データは同期されていません' }}
            //v-btn.my-2(
              v-if="myProfile.userId && !myProfile.guest"
              text
              @click="$router.push(`/user/${myProfile.userId}`)"
              append-icon="mdi-account-outline"
              style="background-color: rgb(var(--v-theme-primary));"
              ) プロフィールを表示
            //v-btn.my-2(
              v-else
              text
              @click="$router.push('/login')"
              append-icon="mdi-login"
              style="background-color: rgb(var(--v-theme-primary)); color: white;"
              ) ログイン
        v-list.options-list
          //v-list-item.item( @click="searchFriendDialog = true" )
            .icon-and-text
              v-icon mdi-magnify
              v-list-item-title 友達を探す
          //v-list-item.item( @click="$router.push('qrcode')" )
            .icon-and-text
              v-icon mdi-qrcode-scan
              v-list-item-title QRコードで友達を探す
          //v-list-item.item(
            @click="$router.push('/friendlist')"
            v-show="myProfile && myProfile.userId"
            )
            .icon-and-text
              v-icon mdi-account-multiple
              v-list-item-title 友達リスト
          v-list-item.item( @click="$router.push('/settings')" )
            .icon-and-text
              v-icon mdi-cog
              v-list-item-title 設定
          v-list-item.item( @click="jumpToExport" )
            .icon-and-text
              v-icon mdi-file-import
              v-list-item-title インポート・エクスポート
          v-list-item.item( @click="$router.push('/terms')" )
            .icon-and-text
              v-icon mdi-file-document-outline
              v-list-item-title 利用規約
          v-list-item.item( @click="openURL('https://enoki.xyz/privacy')" )
            .icon-and-text
              v-icon mdi-shield-lock-outline
              v-list-item-title プライバシーポリシー
          v-list-item.item( @click="$router.push('/about')" )
            .icon-and-text
              v-icon mdi-information
              v-list-item-title このアプリについて
          v-list-item.item( @click="share('https://play.google.com/store/apps/dev?id=8940000495375956936', 'エノキ電気')" )
            .icon-and-text
              v-icon mdi-share-variant
              v-list-item-title このアプリを共有する
  v-dialog(
    v-model="detailDialog"
    )
    v-card(
      v-for="card in [cards.cards[detailDialogTarget]]"
      width="100%"
    )
      v-card-title カード情報
      v-card-text
        v-text-field(
          v-model="card.name"
          label="カード名"
          prepend-inner-icon="mdi-tag"
          readonly
          )
          template(v-slot:append-inner)
            v-icon(@click.stop="copy(card.name)") mdi-content-copy
        v-text-field(
          v-model="card.cardNumber"
          label="カード番号（16桁）"
          prepend-inner-icon="mdi-credit-card"
          readonly
          )
          template(v-slot:append-inner)
            v-icon(@click.stop="copy(card.cardNumber)") mdi-content-copy
        v-text-field(
          v-model="detailDialogTargetBrand"
          label="ブランド"
          prepend-inner-icon="mdi-cards"
          readonly
          )
          template(v-slot:append-inner)
            v-icon(@click.stop="copy(detailDialogTargetBrand)") mdi-content-copy
        p 有効期限
        .deadline(
          style="display: flex; gap: 16px;"
          )
          v-text-field(
            v-model="card.deadlineMM"
            label="月（MM）"
            readonly
            )
            template(v-slot:append-inner)
              v-icon(@click.stop="copy(card.deadlineMM)") mdi-content-copy
          v-text-field(
            v-model="card.deadlineYYYY"
            label="年（YYもしくはYYYY）"
            readonly
            )
            template(v-slot:append-inner)
              v-icon(@click.stop="copy(card.deadlineYYYY)") mdi-content-copy
        v-text-field(
          v-model="card.cvc"
          label="CVC"
          prepend-inner-icon="mdi-form-textbox-password"
          readonly
          )
          template(v-slot:append-inner)
            v-icon(@click.stop="copy(card.cvc)") mdi-content-copy
        v-text-field(
          v-model="card.ownName"
          label="名義"
          prepend-inner-icon="mdi-account"
          readonly
          )
          template(v-slot:append-inner)
            v-icon(@click.stop="copy(card.ownName)") mdi-content-copy
        v-text-field(
          v-model="card.memo"
          label="メモ"
          prepend-inner-icon="mdi-note-outline"
          readonly
          )
          template(v-slot:append-inner)
            v-icon(@click.stop="copy(card.memo)") mdi-content-copy
      v-card-actions
        v-btn(
          prepend-icon="mdi-trash-can"
          style="background-color: var(--color-error); color: white;"
          @click="deleteDialog = true"
        ) 削除
        v-btn(
          prepend-icon="mdi-close"
          style="background-color: rgb(var(--v-theme-primary)); color: white;"
          @click="detailDialog = false"
        ) 閉じる
  v-dialog(
    v-model="detailBankDialog"
    )
    v-card(
      v-for="card in [cards.bank[detailBankDialogTarget]]"
      width="100%"
    )
      v-card-title 口座情報
      v-card-text
        v-text-field(
          v-model="card.name"
          label="カード名"
          prepend-inner-icon="mdi-tag"
          readonly
          )
          template(v-slot:append-inner)
            v-icon(@click.stop="copy(card.name)") mdi-content-copy
        .deadline(
          style="display: flex; gap: 16px;"
          )
          v-text-field(
            v-model="card.bankCode"
            label="銀行コード"
            readonly
            )
            template(v-slot:append-inner)
              v-icon(@click.stop="copy(card.bankCode)") mdi-content-copy
          v-text-field(
            v-model="card.bankName"
            label="銀行名"
            readonly
            style="width: 50%;"
            )
            template(v-slot:append-inner)
              v-icon(@click.stop="copy(card.bankName)") mdi-content-copy
        .deadline(
          style="display: flex; gap: 16px;"
          )
          v-text-field(
            v-model="card.shopCode"
            label="支店コード"
            readonly
            )
            template(v-slot:append-inner)
              v-icon(@click.stop="copy(card.shopCode)") mdi-content-copy
          v-text-field(
            v-model="card.shopName"
            label="支店名"
            readonly
            style="width: 50%;"
            )
            template(v-slot:append-inner)
              v-icon(@click.stop="copy(card.shopName)") mdi-content-copy
        v-text-field(
          v-model="card.type"
          label="種類"
          readonly
          )
          template(v-slot:append-inner)
            v-icon(@click.stop="copy(card.type)") mdi-content-copy
        v-text-field(
          v-model="card.cardNumber"
          label="カード番号（7桁）"
          prepend-inner-icon="mdi-credit-card"
          readonly
          )
          template(v-slot:append-inner)
            v-icon(@click.stop="copy(card.cardNumber)") mdi-content-copy
        v-text-field(
          v-model="card.ownName"
          label="名義"
          prepend-inner-icon="mdi-account"
          readonly
          )
          template(v-slot:append-inner)
            v-icon(@click.stop="copy(card.ownName)") mdi-content-copy
        v-text-field(
          v-model="card.memo"
          label="メモ"
          prepend-inner-icon="mdi-note-outline"
          readonly
          )
          template(v-slot:append-inner)
            v-icon(@click.stop="copy(card.memo)") mdi-content-copy
      v-card-actions
        v-btn(
          prepend-icon="mdi-trash-can"
          style="background-color: var(--color-error); color: white;"
          @click="deleteBankDialog = true"
        ) 削除
        v-btn(
          prepend-icon="mdi-close"
          style="background-color: rgb(var(--v-theme-primary)); color: white;"
          @click="detailBankDialog = false"
        ) 閉じる
  //v-dialog(
    v-model="acceptDialog"
    persistent
    )
    v-card
      v-card-title 友達リクエストが来ています！
      v-card-text
        p {{ acceptList.length }}人の友達があなたを待っています。承認してつながろう！
      v-card-actions
        v-btn(
          @click="acceptDialog = false"
        ) やめとく
        v-btn(
          @click="$router.push('/friendlist')"
          style="background-color: rgb(var(--v-theme-primary)); color: white"
        ) リクエストを見る
  v-dialog(
    v-model="createTypeSelectDialog"
  )
    v-card
      v-card-title 新規登録
      v-card-text
        .settings-list.my-4(
        )
          .setting-item(
            @click="$router.push('/create')"
            v-ripple
          )
            .icon
              v-icon mdi-credit-card
            .text
              p クレジットカード
              p.opacity05 14～16桁のカード番号と、有効期限/CVCが必要です
          .setting-item(
            @click="$router.push('/createBank')"
            v-ripple
          )
            .icon
              v-icon mdi-bank
            .text
              p 銀行口座
              p.opacity05 7桁の口座番号と、銀行名/支店名が必要です
      v-card-actions
        v-btn(
          prepend-icon="mdi-close"
          @click="createTypeSelectDialog = false"
          style="background-color: rgb(var(--v-theme-primary)); color: white;"
        ) キャンセル
  v-dialog(
    v-model="deleteBankDialog"
  )
    v-card(
      v-for="card in [cards.bank[detailBankDialogTarget]]"
      width="100%"
    )
      v-card-title 削除
      v-card-text
        p 以下の情報を削除しますか？
        table.my-4(
          border="1"
          style="width: 100%; border-collapse: collapse;"
        )
          tbody
            tr
              th.pa-2 カード名
              td.pa-2 {{ card.name }}
            tr
              th.pa-2 銀行名
              td.pa-2 {{ card.bankName }}
            tr
              th.pa-2 支店名
              td.pa-2 {{ card.shopName }}
            tr
              th.pa-2 メモ
              td.pa-2 {{ card.memo }}
      v-card-actions
        v-btn(
          prepend-icon="mdi-close"
          @click="deleteBankDialog = false"
          style="background-color: rgb(var(--v-theme-primary)); color: white;"
        ) キャンセル
        v-btn(
          prepend-icon="mdi-trash-can"
          @click="deleteBank"
          style="background-color: var(--color-error); color: white;"
        ) 削除
  v-dialog(
    v-model="deleteDialog"
  )
    v-card(
      v-for="card in [cards.cards[detailDialogTarget]]"
      width="100%"
    )
      v-card-title 削除
      v-card-text
        p 以下の情報を削除しますか？
        table.my-4(
          border="1"
          style="width: 100%; border-collapse: collapse;"
        )
          tbody
            tr
              th.pa-2 カード名
              td.pa-2 {{ card.name }}
            tr
              th.pa-2 ブランド
              td.pa-2 {{ searchBrand(card.cardNumber) ?? '不明なブランド' }}
            tr
              th.pa-2 カード番号
              td.pa-2 ******{{ card.cardNumber.slice(-4) }}
            tr
              th.pa-2 メモ
              td.pa-2 {{ card.memo }}
      v-card-actions
        v-btn(
          prepend-icon="mdi-close"
          @click="deleteDialog = false"
          style="background-color: rgb(var(--v-theme-primary)); color: white;"
        ) キャンセル
        v-btn(
          prepend-icon="mdi-trash-can"
          @click="deleteCard"
          style="background-color: var(--color-error); color: white;"
        ) 削除
</template>

<script lang="ts">
  import { AndroidBiometryStrength, BiometricAuth, BiometryError, BiometryErrorType } from '@aparajita/capacitor-biometric-auth'
  import { App } from '@capacitor/app'

  import { Browser } from '@capacitor/browser'
  import { Clipboard } from '@capacitor/clipboard'
  import { Share } from '@capacitor/share'
  import { Toast } from '@capacitor/toast'
  // @ts-ignore
  import zenginCode from 'zengin-code'
  // @ts-ignore
  import mixins from '@/mixins/mixins'
  import { useCardsStore } from '@/stores/cards'
  import { useMyProfileStore } from '@/stores/myProfile'
  import { useSettingsStore } from '@/stores/settings'

  export default {
    components: {},
    mixins: [mixins],
    data () {
      return {
        /** オプションダイアログの表示フラグ */
        optionsDialog: false,
        /** 自分のプロフィール */
        myProfile: useMyProfileStore(),
        /** 友達検索ダイアログ */
        searchFriendDialog: false,
        /** 検索する友達のID */
        searchFriendId: '',
        /** 友達検索中のローディング画面 */
        searchFriendLoading: false,
        /** 友達検索画面のエラー表示 */
        searchFriendErrorMessage: '',
        /** 環境変数 */
        env: null as any,
        /** 承認待ち友達リスト */
        acceptList: [] as any,
        /** 承認してほしい友達がいるダイアログ */
        acceptDialog: false,
        /** setIntervalしたものをクリアする用 */
        updateLocationInterval: null as any,
        /** 友達リスト */
        friendList: [] as any[],
        /** 設定ストア */
        settings: useSettingsStore(),
        cards: useCardsStore(),
        /** カード情報詳細ダイアログ */
        detailDialog: false,
        detailDialogTarget: null as number | null,
        /** 銀行口座情報詳細ダイアログ */
        detailBankDialog: false,
        detailBankDialogTarget: null as number | null,
        /** どのタイプのカードを登録するか？ */
        createTypeSelectDialog: false,
        /** 銀行コード */
        zenginCode,
        /** クレカ削除確認ダイアログ */
        deleteDialog: false,
        /** 銀行口座削除確認ダイアログ */
        deleteBankDialog: false,
      }
    },
    computed: {
      detailDialogTargetBrand () {
        if (this.detailDialogTarget === null
        ) {
          return null
        }
        const cardNumber = this.cards.cards[this.detailDialogTarget]?.cardNumber
        if (cardNumber == undefined) {
          return null
        }
        return this.searchBrand(cardNumber) ?? '不明なブランド'
      },
      detailBankDialogTargetBank () {
        if (this.detailBankDialogTarget === null) {
          return null
        }
        const bankCode = this.cards.bank[this.detailBankDialogTarget]?.bankName
        return zenginCode[bankCode]
      },
      detailBankDialogTargetBranch () {
        if (this.detailBankDialogTarget === null) {
          return null
        }
        const branchCode = this.cards.bank[this.detailBankDialogTarget]?.shopName
        return this.detailBankDialogTargetBank.branches[branchCode]
      },
    },
    watch: {
      /** ようこそ画面の表示状態を保存 */
      optionsDialog: {
        handler: async function (dialog: boolean) {
          localStorage.setItem('welcomeDialog', String(dialog))
        },
      },
    },
    async mounted () {
      // @ts-ignore
      this.env = import.meta.env as any

      /** ようこその復活 */
      const welcomeDialog = localStorage.getItem('welcomeDialog')
      if (welcomeDialog && welcomeDialog.toLowerCase() == 'true') {
        this.optionsDialog = true
      }

      /** ログイン情報 */
      if (this.myProfile.$state.guest == false) {
        setTimeout(async () => {
          const token = this.myProfile.userToken
          const profile: any = await this.getProfile(this.myProfile.userId ?? '')
          if (profile) {
            profile.userToken = token
            profile.guest = false
            this.myProfile = {
              ...this.myProfile,
              ...profile,
            }
          }
        }, 100)
      } else {
        this.myProfile.reset()
      }

      /** バックボタンのリスナーを追加 */
      App.addListener('backButton', () => {
        if (this.searchFriendDialog) {
          // 友達検索ダイアログを閉じる
          this.searchFriendDialog = false
        } else if (this.acceptDialog) {
          // 友達承認しろダイアログを閉じる
          this.acceptDialog = false
        } else if (this.optionsDialog) {
          /** オプションダイアログを閉じる */
          this.optionsDialog = false
        } else if (this.detailDialog) {
          /** 詳細ダイアログを閉じる */
          this.detailDialog = false
        } else if (this.detailBankDialog) {
          /** 詳細ダイアログを閉じる */
          this.detailBankDialog = false
        } else if (this.deleteDialog) {
          /** 削除ダイアログを閉じる */
          this.deleteDialog = false
        } else if (this.deleteBankDialog) {
          /** 削除ダイアログを閉じる */
          this.deleteBankDialog = false
        } else if (this.createTypeSelectDialog) {
          /** どの種類のカードを登録する？のダイアログを閉じる */
          this.createTypeSelectDialog = false
        } else if (this.$route.path === '/') {
          /** ルートページならアプリを最小化 */
          App.minimizeApp()
          // Toast.show({ text: 'アプリはバックグラウンドで実行されます' })
        } else {
          /** ルート以外のページなら1つ戻る */
          this.$router.back()
        }
      })

      // 承認していない友達リクエストがあったらポップアップを表示
      const res: any = await this.sendAjaxWithAuth('/getMyFriendList.php', {
        id: this.myProfile.userId,
        token: this.myProfile.userToken,
        withLocation: true,
      })
      if (res && res.body) {
        const allFriendList: any[] = res.body.friendList
        this.acceptList = []
        if (allFriendList && allFriendList[0]) {
          for (const friend of allFriendList) {
            friend.friendProfile.userId = friend.friendRealId
            if (friend.status == 'request' && friend.fromUserId != res.body.mySecretId) {
              this.acceptList.push(friend.friendProfile)
            }
          }
        }
      }
      if (this.acceptList.length > 0 && history.length <= 2) {
        this.acceptDialog = true
      }
    },
    unmounted () {
      App.removeAllListeners()
    },
    methods: {
      /** クリップボードにコピー */
      async copy (content: string) {
        await Clipboard.write({
          string: content,
        })
      },
      /** 秒比較 */
      diffSeconds (date: Date | null | undefined) {
        if (!date) {
          return 999_999
        }

        const now = new Date()
        /** 差分秒 */
        const diff = (now.getTime() - date.getTime()) / 1000
        return diff
      },
      /** 現在時刻と位置情報を最後に取得した時間を比較 */
      diffLastGetTime (date: Date | null | undefined) {
        if (!date) {
          return ''
        }
        /** 差分秒 */
        const diff = this.diffSeconds(date)
        if (diff < 30) {
          return 'たった今'
        } else if (diff < 60) {
          return `${Math.floor(diff)}秒前`
        } else if (diff < 60 * 60) {
          return `${Math.floor(diff / 60)}分前`
        } else if (diff < 60 * 60 * 24) {
          return `${Math.floor(diff / 60 / 60)}時間前`
        } else {
          return `${Math.floor(diff / 60 / 60 / 24)}日前`
        }
      },
      openGoogleMaps (latlng: [
        number, number,
      ]) {
        this.openURL(
          `https://www.google.com/maps/search/?api=1&query=${latlng[0]},${latlng[1]}`,
        )
      },
      /** URLをブラウザで開く */
      async openURL (url: string) {
        await Browser.open({ url: url })
      },
      /** 友達を検索 */
      async searchFriend (searchId: string) {
        searchId = searchId.replace('@', '')
        this.searchFriendLoading = true
        if (!searchId) {
          this.searchFriendErrorMessage = '検索するIDを入力してください'
          this.searchFriendLoading = false
          return
        }
        const userData = await this.getProfile(
          searchId,
          this.myProfile.userId,
          this.myProfile.userToken,
        )
        if (userData) {
          this.$router.push(`/user/${userData.userId}`)
        } else {
          this.searchFriendErrorMessage = '友達が見つかりませんでした'
          this.searchFriendLoading = false
          return
        }
        this.searchFriendLoading = false
      },
      /**
       * 2つのDateオブジェクトの差が10秒以内か判定する
       * @param {Date} date1 - 比較する1つ目の日付
       * @param {Date} date2 - 比較する2つ目の日付
       * @returns {boolean} 10秒以内ならtrue
       */
      isWithin10Seconds (date1: Date, date2: Date) {
        // 10秒 = 10000ミリ秒
        const diffInMilliseconds = Math.abs(date1.getTime() - date2.getTime())
        return diffInMilliseconds <= 10_000
      },
      /** シェアダイアログ */
      async share (content: string, title = '') {
        await Share.share({
          url: content,
          title: title,
        })
      },
      /**
       * ## 生体認証
       * await必須
       * @returns boolean 認証OK→true、NG→false
       */
      async auth (): Promise <boolean> {
        const isAvailable = await BiometricAuth.checkBiometry()
        if (isAvailable.isAvailable == false) {
          // 認証がそもそも機能しない場合は、仕方ないので承認する
          return true
        }
        try {
          await BiometricAuth.authenticate({
            reason: '認証してください',
            cancelTitle: 'キャンセル',
            allowDeviceCredential: true,
            androidTitle: '生体認証ログイン',
            androidSubtitle: '本人確認をしてください',
            androidConfirmationRequired: true,
            androidBiometryStrength: AndroidBiometryStrength.weak,
          })
          return true
        } catch (error) {
          if (error instanceof BiometryError && error.code !== BiometryErrorType.userCancel) {
            // エラーを表示
            Toast.show({ text: error.message })
          } else {
            console.log(error)
          }
          return false
        }
      },
      deleteBank () {
        this.deleteBankDialog = false
        this.detailBankDialog = false
        // ここは0.5秒以上遅延させないとバグる
        setTimeout(() => {
          if (this.detailBankDialogTarget === null) {
            return null
          }
          this.cards.bank.splice(this.detailBankDialogTarget, 1)
        }, 500)
      },
      deleteCard () {
        this.deleteDialog = false
        this.detailDialog = false
        // ここは0.5秒以上遅延させないとバグる
        setTimeout(() => {
          if (this.detailDialogTarget === null) {
            return null
          }
          this.cards.cards.splice(this.detailDialogTarget, 1)
        }, 500)
      },
      async openBank (index: number) {
        const authResult = await this.auth()
        if (authResult) {
          this.detailBankDialogTarget = index
          this.detailBankDialog = true
          return true
        } else {
          Toast.show({ text: '生体認証に失敗しました' })
          return false
        }
      },
      async openCard (index: number) {
        const authResult = await this.auth()
        if (authResult) {
          this.detailDialogTarget = index
          this.detailDialog = true
          return true
        } else {
          Toast.show({ text: '生体認証に失敗しました' })
          return false
        }
      },
      async jumpToExport () {
        const authResult = await this.auth()
        if (authResult) {
          this.$router.push('/export')
          return true
        } else {
          Toast.show({ text: '生体認証に失敗しました' })
          return false
        }
      },
    },
  }
</script>

<style lang="scss" scoped>
.right-bottom-buttons {
  position: fixed;
  right: 16px;
  bottom: calc(16px + 4em);
  display: flex;
  flex-direction: column;
  gap: 8px;
  z-index: 1000;

  .current-button {
    background-color: white;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  }
}
.right-top-buttons {
  position: fixed;
  right: 16px;
  top: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  z-index: 1000;

  .account-button {
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  }
}
.left-top-buttons {
  position: fixed;
  left: 16px;
  top: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  z-index: 1000;

  .account-button {
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  }
}

.name-space {
  font-size: 16px;
  font-weight: 500;
  white-space: nowrap;
  -webkit-text-stroke: 2px black;
  paint-order: stroke;
  color: white;
  transition: all 1s;
}

.action-bar{
  position: fixed;
  bottom: 0;
  left: 0;
  background-color: rgb(var(--v-theme-surface));
  z-index: 500;
  width: 100%;
  align-items: center;
  box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.3);
  .buttons{
    width: 100%;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-evenly;
    height: 4em;
    .button {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      width: 6em;
      border-radius: 1em;
      height: 80%;
      cursor: pointer;
      color: rgb(var(--v-theme-on-surface));

      v-icon {
        font-size: 24px;
      }

      p {
        font-size: 10px;
        margin: 0;
        padding: 0;
      }
    }
  }
  .bottom-android-15-or-higher {
    width: 100%;
  }
}

.bottom-android-15-or-higher {
  height: 16px;
}
.top-android-15-or-higher {
  height: 40px;
}

.options-list {
  .item {
    padding : 12px 16px;
    border-radius: 12px!important;
    margin: 8px 0;
    cursor: pointer;
    &:hover {
      background-color: rgba(var(--v-theme-primary), 0.1);
    }
    .icon-and-text {
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 16px;
      v-icon {
        font-size: 24px;
      }
    }
  }
}

.detail-card-target {
  .info{
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 1em;
    margin: 1em 0;
  }
}

.opacity05 {
  opacity: 0.7;
}

.settings-list {
  display: flex;
  flex-direction: column;
  gap: 1em;
  .setting-item {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 1em;
    padding: 1em;
    border-radius: 8px;
    cursor: pointer;
    transition: all .2s;
    &:hover {
      background-color: rgba(var(--v-theme-primary), 0.1);
    }
    .icon {
      background: rgba(var(--v-theme-on-surface), 0.1);
      border-radius: 50%;
      width: 40px;
      min-width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .text {
      .title {
        font-weight: bold;
        font-size: 1.1em;
      }
      .description {
        font-size: 0.9em;
        color: #666;
      }
    }
  }
}

</style>
