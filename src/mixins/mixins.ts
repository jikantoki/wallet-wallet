/**
 * ここに記述したものはVueの機能として使える
 * しかもどのファイルでも読み込める
 */
import { defineComponent } from 'vue'
import ajaxFunctions from '@/js/ajaxFunctions'
// import Functions from '~/js/Functions'
import PackageJson from '../../package.json'

export type Brands = null | 'JCB' | 'Diners' | 'AMEX' | 'VISA' | 'MasterCard'

export default defineComponent({
  components: {},
  data () {
    return {
      cookieAllowed: false,
      PackageJson,
      env: null as any,
    }
  },
  computed: {
    // availableLocales () {
    //   return this.$i18n.availableLocales.filter(
    //     i => i.code !== this.$i18n.locale,
    //   )
    // },
  },
  mounted () {
    const isAllow = localStorage.cookieAllowed === 'true'
    this.cookieAllowed = isAllow
    const aTags = document.querySelectorAll('a')
    for (const aTag of aTags) {
      if (aTag.href !== '') {
        aTag.addEventListener('click', () => {
          const now = new URL(window.location.href).host
          const next = new URL(aTag.href).host
          let to = aTag.href
          if (now === next) {
            const next = new URL(aTag.href)
            to = next.pathname + next.hash + next.search
          }
          this.a(to)
          event?.preventDefault()
          return false
        })
      }
    }
    this.env = import.meta.env
  },
  methods: {
    searchBrand (cardNumber: string): Brands {
      if (cardNumber == '') {
        return null
      }
      // 先頭2文字はカード番号が0の場合があるので削除
      if (cardNumber.slice(0, 1) == '0') {
        cardNumber = cardNumber.slice(1, 15)
        if (cardNumber.slice(0, 1) == '0') {
          cardNumber = cardNumber.slice(1, 14)
        }
      }
      /** クレカ番号の先頭文字 */
      const firstString = cardNumber.slice(0, 1)
      switch (Number(firstString)) {
        case 3: {
          /** クレカ番号の先頭2文字 */
          const secondString = cardNumber.slice(0, 2)
          switch (Number(secondString)) {
            case 35: {
              return 'JCB'
            }
            case 30:
            case 36:
            case 38:
            case 39: {
              return 'Diners'
            }
            case 34:
            case 37: {
              return 'AMEX'
            }
            default: {
              return null
            }
          }
        }
        case 4: {
          return 'VISA'
        }
        case 2:
        case 5: {
          return 'MasterCard'
        }
        default: {
          return null
        }
      }
    },
    sendAjax: ajaxFunctions.send,
    /**
     * APIトークンを同時に送信するAjax（内部処理用）
     * ### ヘッダ送付方法
     * ```js
    header = {
      id: 'hogefuga',
      password: 'qwerty'
    }
     * ```
     * ___
     * @param {string} url 送信先URL（ドメインは自動で付きます）
     * @param {array} header ヘッダ情報
     * @param {object} sendObject 送りたいオブジェクト
     * @param {bool} isPost true（デフォ）ならPOST、falseでGET
     */
    sendAjaxWithAuth (url: string, header = null as any, sendObject = null as any, isPost = true) {
      if (!this.env.VUE_APP_API_HOST) {
        return null
      }
      const authHeader = {
        apiid: this.env.VUE_APP_API_ID,
        apitoken: this.env.VUE_APP_API_TOKEN,
        apipassword: this.env.VUE_APP_API_ACCESSKEY,
      }
      let hd = []
      hd = this.isObject(header) ? Object.assign(header, authHeader) : authHeader
      // @ts-ignore
      const res: {
        then: (arg0: (e: { [key: string]: any }) => void) => unknown
        body: any
        isJSON: boolean
      } = this.sendAjax(
        this.env.VUE_APP_API_HOST + url,
        sendObject,
        hd,
        isPost,
      )
      return res
    },
    /**
     * <p>aタグと同じ動きをするし、pjaxになる</p>
     * <p>外部URLの場合、新しいタブで開く</p>
     * @param {string} url 転送したいURL（ルートからのパス）
     * @returns {int} 内部リンクなら0、外部ドメインなら1
     */
    a (url: string) {
      if (url.slice(0, 4) === 'http') {
        window.open(url, '_blank')
        return 1
      } else {
        this.$router.push(url)
        // if (this.drawer) {
        //   this.drawer = false
        // }
        return 0
      }
    },
    /**
     * ファイルをダウンロードさせる
     * @param {string} filePath ファイルのパス
     * @returns 0
     */
    download (filePath: string, name: string) {
      if (!name) {
        name = filePath
      }
      const element = document.createElement('a')
      element.href = filePath
      element.download = name
      element.target = '_blank'
      element.click()
      return 0
    },
    /**
     * 0未満なら-1で乗算する
     * @param {int, float} number 変換したい数値
     * @returns 正の値
     */
    unsigned (number: number) {
      return number < 0 ? number * -1 : number
    },
    /**
     * クッキーの特定のキーを取得
     * @param {string} name 取得したいCookieのキー
     * @returns キーがあればvalue、無ければnull
     */
    getCookie (name: string) {
      const c = new RegExp(name + '=[^;]+').exec(document.cookie)
      return c ? c[0].replace(name + '=', '') : null
    },
    /**
     * 全てのクッキーを連想配列で返す
     * @returns cookie
     */
    getAllCookie () {
      const cookie = document.cookie
      if (!cookie || cookie === '') {
        return null
      }
      const cookieArray = cookie.split(';')
      const newCookieArray = []
      for (const keyAndValue of cookieArray) {
        const keyValue = keyAndValue.split('=')
        newCookieArray.push(keyValue)
      }
      return newCookieArray
    },
    /**
     * クッキーをセットする
     * @param {string} key 設定したいキー
     * @param {*} value 設定したい値
     * @returns OKだったらTrue、許可がなかったらFalse
     */
    setCookie (key: string, value: any) {
      if (this.cookieAllowed) {
        // eslint-disable-next-line unicorn/no-document-cookie
        document.cookie = `${key}=${value};`
        return true
      } else {
        return false
      }
    },
    checkCookie () {
      let isAllow = localStorage.cookieAllowed
      isAllow = isAllow ? true : false
      this.cookieAllowed = isAllow
      return isAllow
    },
    /**
     * Cookieを許可する
     */
    allowCookie () {
      localStorage.cookieAllowed = true
      this.cookieAllowed = true
    },
    /**
     * Cookieを拒否する
     */
    denyCookie () {
      localStorage.cookieAllowed = false
      this.cookieAllowed = false
    },
    /**
     * アカウントのプロフィールを取得
     * @param {string} userId 欲しいユーザーのID
     * @param {string} myUserId 自分のユーザーのID
     * @returns アカウントの公開情報
     */
    async getProfile (targetUserId: string,
      myUserId = null as string | null,
      myUserToken = null as string | null) {
      const profile = await this.sendAjaxWithAuth('/getProfile.php', {
        id: myUserId,
        token: myUserToken,
        targetId: targetUserId,
      }) as any
      const res = profile.body.res
      // eslint-disable-next-line unicorn/prefer-ternary
      if (res) {
        return {
          userId: res.userId === '' ? null : res.userId,
          createdAt: res.createdAt === '' ? null : res.createdAt,
          status: res.status === '' ? null : res.status,
          icon: res.icon === '' ? null : res.icon,
          coverImg: res.coverImg === '' ? null : res.coverImg,
          name: res.name === '' ? null : res.name,
          message: res.message === '' ? null : res.message,
          friendStatus: profile.body.friendStatus,
          userToken: null as any,
          lastGetLocationTime: null as any,
          location: null as any,
          battery: null as any,
          guest: null as any,
        }
      } else {
        // 存在しない
        return null
      }
    },
    /**
     * 省略された言語名を展開
     * @param {string} locale 言語名
     * @returns string
     */
    arrangeLocale (locale: string) {
      if (!locale) {
        return null
      }
      switch (locale) {
        case 'ja': {
          return '日本語'
        }
        case 'en': {
          return 'English'
        }
        case 'cn': {
          return '中文'
        }
        default: {
          return locale
        }
      }
    },
    /** 連想配列かどうか？（T/F） */
    isObject (obj: any) {
      return obj instanceof Object && !(Array.isArray(obj)) ? true : false
    },

    // ここからは優先度低いやつ

    /**
     * 変数が使われてません！を無効化
     * @param {*} obj エラーを無効化したい変数
     * @returns objがtrueなら1
     */
    nulling (obj: any) {
      if (obj) {
        return 1
      }
    },
    crack () {
      alert('さてはオメー、ソースコードを見ているな！？！？！？')
      return 7_095_110
    },
  },
})
