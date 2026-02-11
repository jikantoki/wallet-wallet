// THIS FILE SHOULD NOT BE VERSION CONTROLLED

// https://github.com/NekR/self-destroying-sw

/*
self.addEventListener('install', function (e) {
  self.skipWaiting()
})

self.addEventListener('activate', function (e) {
  self.registration.unregister()
    .then(function () {
      return self.clients.matchAll()
    })
    .then(function (clients) {
      clients.forEach(client => client.navigate(client.url))
    })
})
*/
const CACHE_NAME = 'pwa-sample-caches'
const CACHE_KEYS = new Set([
  CACHE_NAME,
])
/**
 * キャッシュしたいコンテンツ
 */
const urlsToCache = [
  '/img/default_cover.jpg',
  '/img/favicon.ico',
  '/img/icon.png',
  '/img/icon96.png',
  '/img/icon192.png',
  '/img/icon512.png',
  '/img/thumbnail.jpg',
  '/account_default.jpg',
  '/favicon.ico',
  '/icon.png',
  '/jikantoki-homepage.jpg',
  '/jikantoki.jpg',
]
self.addEventListener('install', function (event) {
  // console.log('sw event: install called')

  event.waitUntil(
    caches.open(CACHE_NAME).then(function (cache) {
      return cache.addAll(urlsToCache)
    }),
  )
})

self.addEventListener('fetch', function (event) {
  // ブラウザが回線に接続しているかをboolで返してくれる
  const online = navigator.onLine

  // 回線が使えるときの処理
  if (online) {
    event.respondWith(
      caches.match(event.request)
        .then(
          function (response) {
            if (response) {
              return response
            }
            // ローカルにキャッシュがあればすぐ返して終わりですが、
            // 無かった場合はここで新しく取得します
            return fetch(event.request)
              .then(function (response) {
              // 取得できたリソースは表示にも使うが、キャッシュにも追加しておきます
              // ただし、Responseはストリームなのでキャッシュのために使用してしまうと、ブラウザの表示で不具合が起こる(っぽい)ので、複製しましょう
                const cloneResponse = response.clone()
                if (response) {
                  if (response.status === 200) {
                    // === ここから画像キャッシュの条件分岐を追加 ===
                    const requestUrl = new URL(event.request.url)
                    // パス名を取得し、小文字にして拡張子をチェック
                    const path = requestUrl.pathname.toLowerCase()
                    const isImage = path.endsWith('.png') || path.endsWith('.jpg')

                    if (isImage) {
                      // 画像（png/jpg）の場合のみキャッシュに追加
                      caches.open(CACHE_NAME)
                        .then(function (cache) {
                          cache.put(event.request, cloneResponse)
                            .then(function () {
                              // 正常にキャッシュ追加できたときの処理(必要であれば)
                            })
                        })
                    }
                    // === ここまで画像キャッシュの条件分岐を追加 ===
                  } else {
                  // 正常に取得できなかったときにハンドリングしてもよい
                    return response
                  }
                  return response
                }
              })
              .catch(function (error) {
              // デバッグ用
                return console.error(error)
              })
          }),
    )
  } else {
    // オフラインのときの制御
    event.respondWith(
      caches.match(event.request)
        .then(function (response) {
          // キャッシュがあったのでそのレスポンスを返す
          if (response) {
            return response
          }
          // オフラインでキャッシュもなかったパターン
          return caches.match('offline.html')
            .then(function (responseNodata) {
              // 適当な変数にオフラインのときに渡すリソースを入れて返却
              // 今回はoffline.htmlを返しています
              return responseNodata
            })
        },
        ),
    )
  }
})

// 通知がクリックされたときの挙動はこれ
self.addEventListener('notificationclick', function (event) {
  /* console.log('sw event: notification clicked') */
  event.notification.close()

  // eslint-disable-next-line no-undef
  clients.openWindow('/')
  if (event.action && event.action !== '') {
    console.log('通知イベント' + event.action + 'を実行しました')
  } else {
    console.log('デフォルトの通知イベントを実行しました')
  }
})

self.addEventListener('push', function (event) {
  // console.log('sw event: push called')
  /**
   * ## 通知の実装
   * notificationDataObjの中身
   * ```json
   * {
   *   "title": "Notification title",
   *   "option": { Option }
   * }
   * ```
   */
  let notificationDataObj
  try {
    notificationDataObj = event.data.json()
  } catch {
    notificationDataObj = {
      title: 'Webサイトからの通知',
      option: {
        body: event.data.text(),
      },
    }
  }
  /**
   * titleの中身はstringな必要があります
   */
  const title = notificationDataObj.title
  /**
   * ## 通知オプションの書き方
   * actionsは2つまで実装可能
   *
   * actions.actionの中身をnotificationClickに渡します
   * ```json
   * {
   *   "body": "message",
   *   "icon": "icon_url",
   *   "badge": "badge_url",
   *   "image": "image_url",
   *   "tag": "tag(optional)",
   *   "actions": [
   *     {
   *       "action": "action_name",
   *       "title": "action_title"
   *     }
   *   ]
   * }
   * ```
   */
  const option = notificationDataObj.option
  event.waitUntil(self.registration.showNotification(title, option))

  /**
   * notification example
   * テスト時はこれコピーして使う
   */
  /*
    {
      "title": "通知テスト",
      "option": {
        "body": "メッセージサンプル",
        "icon": "/img/icon192.png",
        "tag": "tag, warn",
        "actions": [
          {
            "action": "testA",
            "title": "アクションA"
          },
          {
            "action": "testB",
            "title": "アクションB"
          }
        ]
      }
    }
   */
})
self.addEventListener('pushsubscriptionchange', e => {
  console.error(e)
})

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.filter(key => {
          return !CACHE_KEYS.has(key)
        }).map(key => {
          // 不要なキャッシュを削除
          return caches.delete(key)
        }),
      )
    }),
  )
})
