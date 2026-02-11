<template lang="pug">
.login(v-if="isShow")
  v-card-actions
    p.ml-2(style="font-size: 1.3em") アカウント作成
    v-spacer
    v-btn(
      text
      @click="$router.push('/')"
      icon="mdi-close"
      )
  v-form.center(@submit.prevent)
    img.ma-8(src="/icon.png")
    p.form-p.text-h6 アカウント新規登録
    v-container
      o.success.pa-4.ma-4.relative(v-if="page === 1")
        v-icon mdi-check
        p.px-4 新しいアカウントを追加しました！
      .error.pa-4.mb-4.relative(v-if="errorMessage && page === 0")
        v-icon mdi-alert-circle-outline
        p.px-4 {{ errorMessage }}
        v-icon.v-ripple.absolute.close-error(
          v-ripple
          @click="errorMessage=false"
          ) mdi-close-circle-outline
      v-text-field(
        v-if="page === 0"
        v-model="userName"
        name="id"
        label="ID"
        counter="32"
        prepend-inner-icon="mdi-account-outline"
        type="text"
        :rules="[rules.required]"
        hint="3～32文字、半角英数字アンダーバーのみ"
        ref="formId"
        autocomplete="username"
        @keydown.enter="$refs.formMail.focus()"
        clearable
        )
      v-text-field(
        v-if="page === 0"
        v-model="mailAddress"
        name="mail"
        label="メールアドレス"
        prepend-inner-icon="mdi-email-outline"
        type="email"
        :rules="[rules.required]"
        ref="formMail"
        autocomplete="email"
        @keydown.enter="$refs.formPass.focus()"
        clearable
        )
      v-text-field(
        v-if="page === 0"
        v-model="password"
        name="password"
        label="パスワード"
        prepend-inner-icon="mdi-lock-outline"
        :type="showPassword ? 'text' : 'password'"
        :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
        @click:append-inner="showPassword = !showPassword"
        ref="formPass"
        autocomplete="new-password"
        @keydown.enter="$refs.formRePass.focus()"
        :rules="[rules.required]"
        )
      v-text-field(
        v-if="page === 0"
        v-model="confirmPassword"
        name="confirmPassword"
        label="パスワード（確認用）"
        prepend-inner-icon="mdi-lock-outline"
        :type="showConfirmPassword ? 'text' : 'password'"
        :append-inner-icon="showConfirmPassword ? 'mdi-eye' : 'mdi-eye-off'"
        @click:append-inner="showConfirmPassword = !showConfirmPassword"
        ref="formRePass"
        autocomplete="new-password"
        @keydown.enter="agreement = true;$refs.formRegistar.$el.focus();"
        :rules="[rules.required]"
        )
      v-checkbox(
        v-if="page === 0"
        v-model="agreement"
        label="利用規約に同意します"
        ref="formAgree"
        )
      .btns
        v-btn.round.submit(
          v-if="page === 0"
          @click="registar"
          :disabled="!userName || !mailAddress || !password || !confirmPassword"
          ref="formRegistar"
          ) 新規作成
        v-btn.round(
          v-if="page === 0"
          @click="a('/login')"
          ) 既にアカウントを持っています
        v-btn.round.submit(
          v-if="page === 1"
          @click="a('/login')"
          ) ログイン
</template>

<script lang="ts">
  import mixins from '@/mixins/mixins'
  export default {
    mixins: [mixins],
    data () {
      return {
        /** 将来的にdivの一つとしてフォームを埋め込む用 */
        isShow: true,
        userName: '',
        mailAddress: '',
        password: '',
        confirmPassword: '',
        showPassword: false,
        showConfirmPassword: false,
        agreement: false,
        errorMessage: '',
        page: 0,
        rules: {
          required: (value: string) => !!value || 'Field is required',
        },
      }
    },
    watch: {
      password () {
        this.errorMessage = ''
      },
      confirmPassword () {
        this.errorMessage = ''
      },
      userName () {
        this.errorMessage = ''
      },
      mailAddress () {
        this.errorMessage = ''
      },
      agreement () {
        this.errorMessage = ''
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
      registar () {
        if (
          !this.userName
          || !this.mailAddress
          || !this.password
          || !this.confirmPassword
        ) {
          this.errorMessage = '入力されていない項目があります'
          return false
        }
        if (
          this.userName.length < 3
          || this.userName.length > 32
          || !/^[0-9a-zA-z_]+$/.test(this.userName)
        ) {
          this.errorMessage = 'IDが正しくありません'
          return false
        }
        if (!/.+@.+\..+/.test(this.mailAddress)) {
          this.errorMessage = 'メールアドレスが正しくありません'
          return false
        }
        if (this.password !== this.confirmPassword || this.password.length < 3) {
          this.errorMessage = 'パスワードが正しくありません'
          return false
        }
        if (!this.agreement) {
          this.errorMessage = '利用規約に同意してください'
          return false
        }
        // @ts-ignore
        this.sendAjaxWithAuth('/createAccount.php', {
          username: this.userName,
          password: this.password,
          mailaddress: this.mailAddress,
        })
          .then(e => {
            if (e.body.status === 'ok') {
              this.page = 1
            } else {
              this.errorMessage = '既に存在するアカウントです'
            }
          })
          .catch((error: any) => {
            console.error(error)
            this.errorMessage = 'ネットワークエラー'
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
.error {
  background-color: var(--color-error);
  color: white;
  display: flex;
  border-radius: 4px;
}
.success {
  background-color: var(--color-success);
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
</style>
