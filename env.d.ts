/// <reference types="vite/client" />
/// <reference types="unplugin-vue-router/client" />
// vite-env.d.ts または同様のファイル
interface ImportMetaEnv {
  readonly VITE_APP_TITLE: string
  // 他の環境変数がある場合はここに追加します
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}
