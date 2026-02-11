export default {
  /**
   * WebPushを許可する仕組み
   * ```js
   * set().then((e) => {
   *   console.log(e) //requestが返り値
   * }).catch((e) => {
   *   console.error(e) //エラー理由
   * })
   * ```
   * @returns promise
   */
  set: async () => {
    // eslint-disable-next-line no-async-promise-executor
    return new Promise(async (resolve, reject) => {
      /**
       * サービスワーカーの登録
       */
      if ('serviceWorker' in navigator) {
        // @ts-ignore
        window.sw = await navigator.serviceWorker.register('/sw.js', {
          scope: '/',
        })
      }
      if ('Notification' in window) {
        const permission = Notification.permission

        if (permission === 'denied') {
          console.warn(
            'Push通知が拒否されているようです。ブラウザの設定からPush通知を有効化してください',
          )
          reject(false)
        }

        if (permission === 'granted') {
          console.log('すでにWebPushを許可済みです')
          // ここでreturnしてもシステム上問題ないが、トークンをconsole.logしたいので続行
          // return 'allowed
        }
        const request = await getRequest()

        if (request) {
          resolve(request)
        } else {
          reject(false)
        }
      } else {
        reject(false)
      }
    })
  },

  /**
   * ## プッシュ通知の許可が出ているか確認し、許可ならrequestに必要な鍵を返す
   * 要async / await
   *
   * 結果は.thenで見れる（エラーもthenに入る）
   *
   * ## 返り値リスト
   * * Object 成功！
   * * undefined 拒否
   * * null PWAのため取得せず
   * @param {bool} listenFlag Trueの場合はリクエストを出す、Falseなら現在の権限に委ねる
   * @returns object
   */
  get: async (listenFlag: boolean) => {
    return await getRequest(listenFlag)
  },
}
/**
 * トークンを変換するときに使うロジック
 * @param {*} base64String
 */
function urlB64ToUint8Array (base64String: string) {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4)
  const base64 = (base64String + padding).replace(/\\-/g, '+').replace(/_/g, '/')

  const rawData = window.atob(base64)
  const outputArray = new Uint8Array(rawData.length)

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.codePointAt(i) ?? 0
  }
  return outputArray
}

/**
 * リクエストを取得する
 * サービスワーカー登録済みならsetを使わずgetRequestで十分
 * @param ListenFlag {string} Trueの場合はユーザーに尋ねる、falseの場合は権限に委ねる
 * @returns リクエスト、エラーはfalse、PWAによるリクエスト不可はnull
 */
async function getRequest (listenFlag = false) {
  const env = import.meta.env
  /**
   * 共通変数
   */
  const PUBLIC_KEY = env.VUE_APP_WEBPUSH_PUBLICKEY
  // 取得したPublicKeyを「UInt8Array」形式に変換する
  const applicationServerKey = urlB64ToUint8Array(PUBLIC_KEY)

  // push managerにサーバーキーを渡し、トークンを取得
  let subscription

  const permission = Notification.permission
  if (permission === 'granted' || listenFlag) {
    try {
      // スマホで特定の環境だと止まる？？？
      if (permission !== 'granted') {
        if (window.matchMedia('(display-mode: standalone)').matches) {
          // ここにPWA環境下でのみ実行するコードを記述
          // PWAだとプッシュリクエストが出せないぽいのでreturn
          return null
        }
        Notification.requestPermission()
      }
      // @ts-ignore
      subscription = await window.sw.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey,
      })
    } catch (error) {
      // エラーで取得不可
      console.warn(error)
      return undefined
    }
  } else {
    // Permission denied
    console.warn('Permission denied')
    return undefined
  }

  // 必要なトークンを変換して取得（これが重要！！！）
  const key = subscription.getKey('p256dh')
  const token = subscription.getKey('auth')
  const request = {
    endpoint: subscription.endpoint,
    // @ts-ignore
    publicKey: btoa(String.fromCodePoint.apply(null, new Uint8Array(key))),
    // @ts-ignore
    authToken: btoa(String.fromCodePoint.apply(null, new Uint8Array(token))),
  }
  return request
}
