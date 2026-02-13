/**
 * デバイス間でのウォレットデータ転送用の暗号化ユーティリティ
 * ユーザー提供のパスワードでSecureLSを使用したAES暗号化を行う
 */
import SecureLS from 'secure-ls'

/**
 * パスワードでデータを暗号化
 * @param data - 暗号化するデータオブジェクト
 * @param password - ユーザーパスワード
 * @returns 暗号化された文字列
 */
export function encryptData (data: any, password: string): string {
  const ls = new SecureLS({
    isCompression: false,
    encodingType: 'aes',
    encryptionSecret: password,
  })

  try {
    // 一時キーを使用してデータを暗号化
    ls.set('_temp_transfer_data', data)
    const encrypted = localStorage.getItem('_temp_transfer_data') || ''
    return encrypted
  } finally {
    // エラーが発生してもクリーンアップを確実に実行
    localStorage.removeItem('_temp_transfer_data')
  }
}

/**
 * パスワードでデータを復号化
 * @param encryptedData - 暗号化された文字列
 * @param password - ユーザーパスワード
 * @returns 復号化されたデータオブジェクト、失敗時はnull
 */
export function decryptData (encryptedData: string, password: string): any | null {
  const ls = new SecureLS({
    isCompression: false,
    encodingType: 'aes',
    encryptionSecret: password,
  })

  try {
    // 暗号化されたデータを一時的に保存して復号化
    localStorage.setItem('_temp_transfer_data', encryptedData)
    const decrypted = ls.get('_temp_transfer_data')
    return decrypted
  } catch (error) {
    console.error('復号化に失敗しました:', error)
    return null
  } finally {
    // 復号化が失敗してもクリーンアップを確実に実行
    localStorage.removeItem('_temp_transfer_data')
  }
}
