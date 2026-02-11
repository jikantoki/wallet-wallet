/**
 * どのファイル内でも使い回したいって変数はここに書く
 */
export default {
  /**
   * 文字列の先頭がアルファベット小文字なら、先頭を大文字に変換
   * @param {string} string 変換したい文字列
   * @returns 変換した or されなかった文字列
   */
  ifEnglishStartUpper: (string: string) => {
    const startString = string.slice(0, 1)
    if (/^[a-z]*$/.test(startString)) {
      const otherString = string.slice(1)
      return startString.toUpperCase() + otherString
    } else {
      return string
    }
  },
  /**
   * パスがルートを指していたらT、そうでなければF
   * @param {string} path 検証したいパス
   * @returns 説明の通り
   */
  isRoot: (path: string) => {
    switch (path) {
      case '':
      case '/':
      case 'index':
      case 'index.html':
      case 'index.php': {
        return true
      }
    }
    return false
  },
}
