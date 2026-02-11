<template lang="pug">
.login(v-if="isShow")
  v-card-actions
    p.ml-2(style="font-size: 1.3em") パスワードリセット
    v-spacer
    v-btn(
      text
      @click="$router.push('/')"
      icon="mdi-close"
      )
  v-form.center(@submit.prevent)
    img.ma-8(src="/icon.png")
    p.form-p.text-h6 {{ pageTitle }}
    v-container
      .error.pa-4.mb-4.relative(v-if="errorMessage")
        v-icon mdi-alert-circle-outline
        p.px-4 {{ errorMessage }}
        v-icon.v-ripple.absolute.close-error(
          v-ripple
          @click="errorMessage=false"
          ) mdi-close-circle-outline
      v-text-field(
        v-if="page === 0"
        v-model="userName"
        label="ID"
        prepend-inner-icon="mdi-account-outline"
        required
        clearable
        ref="userName"
        autocomplete="username"
        @keydown.enter="$refs.mailAddress.focus()"
        )
      v-text-field(
        v-if="page === 0"
        v-model="mailAddress"
        label="メールアドレス"
        placeholder="mail@example.com"
        :rules="rules"
        prepend-inner-icon="mdi-email-outline"
        type="email"
        clearable
        required
        ref="maillAddress"
        autocomplete="email"
        @keydown.enter="requestToken()"
        )
      a.forgot-password(v-if="page === 0" href="/login") パスワードを思い出した
      v-text-field(
        v-if="page === 1"
        v-model="token"
        label="アクセストークン"
        prepend-inner-icon="mdi-key-outline"
        required
        clearable
        @keydown.enter="$refs.newPassword.focus()"
        )
      v-text-field(
        v-if="page === 1"
        v-model="newPassword"
        label="新しいパスワード"
        prepend-inner-icon="mdi-lock-outline"
        required
        clearable
        ref="newPassword"
        :type="showPassword ? 'text' : 'password'"
        :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
        @click:append-inner="showPassword = !showPassword"
        @keydown.enter="$refs.newRePassword.focus()"
        autocomplete="new-password"
        )
      v-text-field(
        v-if="page === 1"
        v-model="newRePassword"
        label="新しいパスワード（確認）"
        prepend-inner-icon="mdi-lock-outline"
        required
        clearable
        ref="newRePassword"
        :type="showRePassword ? 'text' : 'password'"
        :append-inner-icon="showRePassword ? 'mdi-eye' : 'mdi-eye-off'"
        @click:append-inner="showRePassword = !showRePassword"
        @keydown.enter="login()"
        autocomplete="new-password"
        )
      .btns
        v-btn.round.submit(
          v-if="page === 0"
          @click="requestToken()"
          :disabled="!userName || !mailAddress"
          :loading="loading"
          ref="submit"
          ) リクエストを送信
        v-btn.round.submit(
          v-if="page === 1"
          @click="login()"
          :disabled="!token"
          :loading="loadingToken"
          ref="submitToken"
          ) パスワードをリセット
        v-btn.round.submit(
          v-if="page === 2"
          @click="a('/login')"
          ref="jumpToLogin"
          ) ログイン
</template>

<script lang="ts">
  import mixins from '@/mixins/mixins'
  export default {
    mixins: [mixins],
    data () {
      return {
        /** 将来的にv-dialogとかでフォームを埋め込む用 */
        isShow: true,
        userName: '',
        mailAddress: '',
        token: '',
        loading: false,
        loadingToken: false,
        errorMessage: '',
        newPassword: null,
        newRePassword: null,
        showPassword: false,
        showRePassword: false,
        page: 0,
        pageTitle: 'パスワードをリセットする',
        /** メアド検証用 */
        mailRules: [(v: string) => !!v || '', (v: string) => /.+@.+\..+/.test(v) || ''],
      }
    },
    mounted () {
      if (localStorage.userIdForLogin) {
        this.userName = localStorage.userIdForLogin
      }
    },
    unmounted () {
      localStorage.userIdForLogin = this.userName ?? ''
    },
    methods: {
      /** ログイン前の二段階認証をリクエスト */
      async requestToken () {
        this.loading = true
        // @ts-ignore
        this.sendAjaxWithAuth('/requestTokenForgotAccount.php', {
          id: this.userName,
          mailAddress: this.mailAddress,
        })
          .then(e => {
            if (e.body.status === 'ok') {
              this.page = 1
              this.errorMessage = ''
              this.pageTitle = 'メールに送信したトークンと新規パスワードを入力'
            } else {
              this.errorMessage = 'ユーザー名またはメールアドレスが間違っています'
            }
            this.loading = false
          })
          .catch((error: any) => {
            console.error(error)
            this.errorMessage = 'ネットワークエラー'
            this.loading = false
          })
      },
      async login () {
        this.loadingToken = true
        if (this.newPassword !== this.newRePassword) {
          this.errorMessage = 'パスワードが一致しません'
          return false
        }
        // @ts-ignore
        this.sendAjaxWithAuth('/resetPassword.php', {
          id: this.userName,
          mailAddress: this.mailAddress,
          token: this.token,
          newPassword: this.newPassword,
        })
          .then(async e => {
            if (e.body.status === 'ok') {
              this.pageTitle = 'パスワードをリセットしました！'
              this.page = 2
            } else {
              this.errorMessage
                = 'ワンタイムトークンが違うか、無効なパスワードです'
            }
            this.loadingToken = false
          })
          .catch((error: any) => {
            console.error(error)
            this.errorMessage = 'ネットワークエラー'
            this.loadingToken = false
          })
      },
    },
  }
</script>

<style lang="scss" scoped>
.center{
  width: 100%;
}

.login {
  position: relative;
  display: contents;
  .center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    flex-direction: column;
    max-width: 32em;
  }
}
.btns {
  display: flex;
  flex-direction: row-reverse;
  .round {
    border-radius: 9999px;
  }
  .submit {
    background-color: var(--accent-color);
    color: var(--accent-text-color);
  }
}
img {
  height: 8em;
  object-fit: contain;
}
.form-p {
  text-align: center;
}
.v-btn:disabled {
  opacity: 0.7;
}
.error {
  background-color: var(--color-error);
  color: white;
  display: flex;
  border-radius: 4px;
}
.v-ripple {
  border-radius: 9999px;
  cursor: pointer;
}
.close-error {
  right: 16px;
}
.forgot-password {
  color: inherit;
  opacity: 0.7;
}
</style>
