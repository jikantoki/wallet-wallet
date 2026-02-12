/**
 * Transfer encryption utility for wallet data transfer between devices
 * Uses SecureLS for AES encryption with user-provided password
 */
import SecureLS from 'secure-ls'

/**
 * Encrypt data with password
 * @param data - Data object to encrypt
 * @param password - User password
 * @returns Encrypted string
 */
export function encryptData(data: any, password: string): string {
  const ls = new SecureLS({
    isCompression: false,
    encodingType: 'aes',
    encryptionSecret: password,
  })
  
  // Use a temporary key to encrypt the data
  ls.set('_temp_transfer_data', data)
  const encrypted = localStorage.getItem('_temp_transfer_data') || ''
  localStorage.removeItem('_temp_transfer_data')
  
  return encrypted
}

/**
 * Decrypt data with password
 * @param encryptedData - Encrypted string
 * @param password - User password
 * @returns Decrypted data object or null if decryption fails
 */
export function decryptData(encryptedData: string, password: string): any | null {
  try {
    const ls = new SecureLS({
      isCompression: false,
      encodingType: 'aes',
      encryptionSecret: password,
    })
    
    // Store the encrypted data temporarily and decrypt it
    localStorage.setItem('_temp_transfer_data', encryptedData)
    const decrypted = ls.get('_temp_transfer_data')
    localStorage.removeItem('_temp_transfer_data')
    
    return decrypted
  } catch (error) {
    console.error('Decryption failed:', error)
    return null
  }
}
