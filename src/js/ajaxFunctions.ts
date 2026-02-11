/**
 * ### ajaxFunctionsモジュールの中身
 * - send: URLとメソッドを指定し、送信してpromiseを返す
 */
export default {
  /**
   * ## Ajax処理の実行
   * 注意: POSTに対応していないURLにPOSTした場合、404が返されます
   * ___
   * ### 使い方
   * ```js
    ajaxFunctions.send(url,{example:'example'}).then((value) => {
      console.log(value)
    }).catch((error) => {
      console.error(error)
    })
   * ```
   * ___
   * ### header記入例
   * ```js
    header = {
      id: 'hogefuga',
      password: 'qwerty'
    }
   * ```
   * ___
   * ### returnの中身
   * ```js
    return {
      ajaxで貰った内容,
      body: ajax先から貰ったデータ（JSONならObjectに変換）,
      isJSON: bool, //JSONだったらTrue、違うならFalse
      ajaxInfo: {
        status: int, //HTTPステータス
        statusText: string, //Not Foundとかが入る
        url: string, //リクエストしたURL
        timeout: int //タイムアウトしたらなんか入ると思う
      }
    }
   * ```
   *
   * @param url {string} URL（フルパスかルートディレクトリから入力）
   * @param isPost {bool} trueならPOST、false（default）ならGET
   * @returns object Webサイトから貰ったデータはobject.bodyに格納
   */
  send: (url: string, obj = null as any, header = null as null | any[], isPost = false) => {
    const methods = isPost ? 'POST' : 'GET'
    /**
     * resolve(hogehoge)で実行環境のthenが発火する
     * reject(piyopiyo)で実行環境のcatchが発火する
     */
    return new Promise((resolve, reject) => {
      const request = new XMLHttpRequest()
      request.addEventListener('readystatechange', () => {
        if (request.readyState == 4) {
          if (request.status === 200) {
            let response = {}
            try {
              response = {
                body: JSON.parse(request.response),
                isJSON: true,
              }
            } catch {
              response = {
                body: request.response,
                isJSON: false,
              }
            }
            // @ts-ignore
            response.ajaxInfo = {
              status: request.status,
              statusText: request.statusText,
              url: request.responseURL,
              timeout: request.timeout,
            }
            resolve(response as {
              body: any
              isJSON: boolean
            })
          } else {
            reject({
              ajaxInfo: {
                status: request.status,
                statusText: request.statusText,
                url: request.responseURL,
                timeout: request.timeout,
              },
            })
          }
        }
      })
      let params = ''
      let postMethod = ''
      if (obj) {
        const objKeys = Object.keys(obj)
        for (const key of objKeys) {
          const encoded = encodeURIComponent(obj[key])
          params += `${key}=${encoded}&`
        }
        params = params.slice(0, -1)
        if (isPost) {
          postMethod = params
        } else {
          url += '?' + params
        }
      }
      request.open(methods, url, true)
      request.setRequestHeader(
        'content-type',
        'application/x-www-form-urlencoded;charset=UTF-8',
      )
      // ヘッダ情報の追加
      // @ts-ignore
      const keys = Object.keys(header)
      for (const key of keys) {
        // @ts-ignore
        const value = header[key]
        request.setRequestHeader(key, value)
      }
      request.send(postMethod)
    })
  },
}

// is this object? t/f
// function isObject (obj: any) {
//   return obj instanceof Object && !(Array.isArray(obj)) ? true : false
// }
