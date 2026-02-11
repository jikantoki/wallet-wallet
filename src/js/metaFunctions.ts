import PackageJson from '../../package.json'
import Functions from './Functions'

export default {
  /**
   * メタタグを書き換える
   * @param {string} metaKey メタタグのキー（og:titleとかdescriptionとか）
   * @param {string} property セットしたい値
   */
  updateMeta: (metaKey: string, property: string) => {
    let returnCode
    const metaName = document.querySelector('meta[name="' + metaKey + '"]')
    if (metaName) {
      metaName.setAttribute('content', property)
      returnCode = 0
    } else {
      returnCode = 1
    }
    const metaProperty = document.querySelector(
      'meta[property="' + metaKey + '"]',
    )
    if (metaProperty) {
      metaProperty.setAttribute('content', property)
      returnCode += 0
    } else {
      returnCode += 2
    }
    return returnCode
  },
  /**
   * PWA/TWAでステータスバーの色を変更
   * @param {string} color カラーコードまたは色名
   * @returns 更新できたら0、無理だったら1
   */
  setStatusColor: (color: string) => {
    const theme = document.querySelector('meta[name="theme-color"]')
    if (theme) {
      theme.setAttribute('content', color)
      return 0
    }
    return 1
  },
  /**
   * このプロジェクトの説明とか入ってる
   */
  PackageJson,
  /**
   * 前述した関数たち
   */
  Functions,
}
