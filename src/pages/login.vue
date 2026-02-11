<template lang="pug">
.login(v-if="isShow")
  v-card-actions
    p.ml-2(style="font-size: 1.3em") ログイン
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
        @keydown.enter="$refs.password.focus()"
        )
      v-text-field(
        v-if="page === 0"
        v-model="password"
        label="パスワード"
        prepend-inner-icon="mdi-lock-outline"
        :type="showPassword ? 'text' : 'password'"
        :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
        @click:append-inner="showPassword = !showPassword"
        required
        ref="password"
        autocomplete="current-password"
        @keydown.enter="requestToken()"
        )
      a.forgot-password(v-if="page === 0" href="/password_reset") パスワードを忘れました
      v-text-field(
        v-if="page === 1"
        v-model="token"
        type="phone"
        placeholder="XXX-XXX"
        label="アクセストークン"
        prepend-inner-icon="mdi-key-outline"
        required
        clearable
        @keydown.enter="login()"
        )
      .btns.mt-4
        v-btn.round.submit(
          v-if="page === 0"
          @click="requestToken()"
          :disabled="!userName || !password"
          :loading="loading"
          ref="submit"
          ) ログイン
        v-btn.round.mr-2(
          v-if="page === 0"
          @click="a('/registar')"
          v-show="!loading"
          ) アカウント作成
        v-btn.round.submit(
          v-if="page === 1"
          @click="login()"
          :disabled="!token"
          :loading="loadingToken"
          ref="submitToken"
          ) ログイン
</template>

<script lang="ts">
  import mixins from '@/mixins/mixins'
  import { useMyProfileStore } from '@/stores/myProfile'
  import { useSettingsStore } from '@/stores/settings'
  export default {
    mixins: [mixins],
    data () {
      return {
        /** 将来的にv-dialogとかでフォームを埋め込む用 */
        isShow: true,
        userName: '',
        password: '',
        token: '',
        showPassword: false,
        loading: false,
        loadingToken: false,
        errorMessage: '',
        page: 0,
        pageTitle: 'ログインして、世界とつながろう',
        myProfile: useMyProfileStore(),
        settings: useSettingsStore(),
      }
    },
    watch: {
      token (now) {
        const replaced = now.toString().replace('-', '')
        if (replaced.length >= 6) {
          this.login()
        }
      },
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
        this.sendAjaxWithAuth('/requestToken.php', {
          id: this.userName,
          password: this.password,
        })
          .then((e: { [key: string]: any }) => {
            if (e.body.status === 'ok') {
              this.page = 1
              this.errorMessage = ''
              this.pageTitle = 'メールに送信したトークンを入力'
            } else {
              console.error(e)
              this.errorMessage = 'ユーザー名またはパスワードが間違っています'
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
        console.log('login start')
        this.loadingToken = true
        this.token = this.token.replace('-', '')
        // @ts-ignore
        this.sendAjaxWithAuth('/loginAccount.php', {
          id: this.userName,
          password: this.password,
          token: this.token,
        })
          .then(async e => {
            console.log(e)
            if (e.body.status === 'ok') {
              const now = new URL(window.location.href)
              const profile = await this.getProfile(e.body.id)
              if (!profile) {
                throw new Error('プロフィールを取得できませんでした')
              }
              profile.userToken = e.body.token
              profile.guest = false
              this.myProfile.$state = profile
              const redirect = now.searchParams.get('redirect')
              if (redirect && redirect !== '') {
                this.a(redirect)
              } else {
                this.a('/')
              }
            } else {
              console.error(e.body)
              this.errorMessage = 'ワンタイムトークンが違います'
              this.token = ''
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
