# Capacitor Template

Androidアプリ用のパッケージ群

<img src="./public/icon.png" width="256px" alt="アイコン">

[最新版ダウンロード](https://raw.githubusercontent.com/jikantoki/capacitor-template/refs/heads/main/app-release.apk)

## Nuxt4 Template

Nuxt を簡単にインストールしてすぐ使うためのテンプレート

- NOLICENSED ご自由にお使いください

## 前提

Node.js と npm と yarn くらい入ってるよね！（投げやり）
デプロイ先は Vercel を想定してるけど多分どこでも動きます
あと PHP の composer も用意してね

## INCLUDED

- Vue CLI Service
- Vue3
- Vuetify3
- Vuetify ダークテーマ
- Nuxt4
- Vue-router
- VSCode、Git、Eslint、Prettier 周りの設定ファイル
- Pug と SASS
- PWA Preset
- Google Fonts
- Vue Content Loader

## 独自実装

- Cookie API
- Ajax API
- 画面を右スワイプでメニュー表示
- イイカンジにカスタマイズされた SCSS ファイル
- コピペで使える pug テンプレート
- 汎用性の高い関数群
- ダークテーマ切り替えボタン
- Push API（使いやすいように改良）
- Notification API（使いやすいように改良）
- アカウント登録時のメールアドレス認証、アクセストークンの発行
- MySQL 用 API

## 制作予定

- リッチエディタ

## 注意

ポート 12345 で動くようにしてあります  
VSCode での利用を推奨

~~Vue3 慣れてなくて Options API 使ってるけど許して~~

## 参考資料

WebPush <https://tech.excite.co.jp/entry/2021/06/30/104213>

## Setup

このプログラムは、表示用サーバーと処理用サーバーの 2 つが必要です

### 表示用サーバー

```shell
git clone git@github.com:jikantoki/nuxt4temp.git
echo 'これだけでセットアップ完了！'
echo 'Vercelとかでデプロイしたらそのまま動く'
```

### WebPush 用の鍵を作成

ここで作れます <https://web-push-codelab.glitch.me/>

#### ストレージを操作できる環境の場合

ルートに.env ファイルを作成し、以下のように記述（クォーテーション不要）

```env
VUE_APP_WEBPUSH_PUBLICKEY=パブリックキーをコピー
VUE_APP_WEBPUSH_PRIVATEKEY=プライベートキーをコピー

VUE_APP_API_ID=default
VUE_APP_API_TOKEN=後のPHPで作成するアクセストークン
VUE_APP_API_ACCESSKEY=後のPHPで作成するアクセスキー

VUE_APP_API_HOST=APIサーバーのホスト
```

#### それ以外（Vercel デプロイ等）

Project Settings → Enviroment Variables を開く  
上記.env ファイルと同じ感じで設定

### PHP サーバー（内部処理用）

サーバーサイドは PHP で開発しているため、一部処理を実行するには PHP サーバーの用意が必要です  
とりあえずレンタルサーバーでも借りれば実行できます

1. API 用のドメインをクライアント側（Vercel 等）とは別で用意する
2. このリポジトリの php フォルダをドメインのルートにする（.htaccess 等で）
3. （準備中！！！）に API 用のドメインを記述
4. リポジトリルート直下に/env.php を用意し、以下の記述をする

```php
<?php
define('DIRECTORY_NAME', '/プロジェクトルートのディレクトリ名');

define('VUE_APP_WebPush_PublicKey', 'パブリックキー');
define('VUE_APP_WebPush_PrivateKey', 'プライベートキー');
define('WebPush_URL', 'プッシュ通知を使うドメイン');
define('WebPush_URL_dev', 'プッシュ通知を使うドメイン（開発用）');//この行は無くても良い
define('WebPush_icon', 'プッシュ通知がスマホに届いたときに表示するアイコンURL');
define('Default_user_icon', 'アイコン未設定アカウント用の初期アイコンURL');

define('MySQL_Host', 'MySQLサーバー');
define('MySQL_DBName', 'DB名');
define('MySQL_User', 'DB操作ユーザー名');
define('MySQL_Password', 'DBパスワード');

define('SMTP_Name', '自動メール送信時の差出名');
define('SMTP_Username', 'SMTPユーザー名');
define('SMTP_Mailaddress', '送信に使うメールアドレス');
define('SMTP_Password', 'SMTPパスワード');
define('SMTP_Server', 'SMTPサーバー');
define('SMTP_Port', 587); //基本は587を使えば大丈夫

$mailHeader = "<p>
いつも Capacitor Template をご利用いただきありがとうございます。
<hr>
</p>";
$mailFooter = "<p>
<hr>
このメールに返信することはできません。
<br>
また、このメールに身に覚えのない場合は、エノキ電気までお問い合わせください。
<br>
<a href=\"https://enoki.xyz\">Capacitor Template</a> by <a href=\"https://enoki.xyz\">エノキ電気</a>
</p>";

```

#### PHP サーバー用の.htaccess の用意

大体こんな感じで設定する

```htaccess
#トップページを/capacitor-template/php にする
<IfModule mod_rewrite.c>
RewriteEngine on
RewriteBase /
RewriteRule ^$ capacitor-template/php/ [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.+)$ capacitor-template/php/$1 [L]
</IfModule>
# 外部からのAPIへのアクセスを許可
Header append Access-Control-Allow-Origin: "*"

```

### MySQL の用意

#### /database.sql ファイルをインポートする

PHPMyAdmin が使える環境なら DB 直下にインポートして終わり、コマンドラインでやる方法は知らん

#### ※インポートでエラーが出たら

/database_VIEW.sql の中身をコピーして phpmyadmin で直接実行

### デフォルト API のトークンを用意する

このプログラムは独自のアクセストークンを利用して API にアクセスします。  
そのため、初回 API を登録する作業が必要です。

1. セットアップした API 用サーバーの/makeApiForAdmin.php にアクセス
2. 初回アクセス時のみ MySQL で登録作業が行われるので、出てきた画面の内容をコピー
3. .env にｲｲｶﾝｼﾞに内容を記述（書き方はさっき説明した）
4. 以後、その値を使って API を操作できます

**忘れたらリセット**するしかないので注意！（一部データは暗号化されており、管理者でも確認できません）

#### デフォルト API トークンのリセット方法

1. MySQL の api_list テーブルの secretId='default'を削除
2. api_listForView の secretId='default'も同様に削除
3. 初回登録と同じ感じでやる
4. データベースに再度 default が追加されていることを確認

## コンソール側で初期化

```shell
yarn install
composer install #PHP用
```

### 実行

```shell
yarn run dev
```

### 設定方法

| 項目           | 設定箇所                     |
| -------------- | ---------------------------- |
| アプリ名       | /package.json                |
| フォント       | /layout/default.vue          |
| ナビゲーション | /items/itemNavigationList.js |
| 404 ページ     | /error.vue                   |

### Compiles and minifies for production

```shell
yarn build
```

### Lints and fixes files

```shell
yarn lint
```

### Customize configuration

See [Configuration Reference](https://cli.vuejs.org/config/).

## トラブルシューティング

### PHP がおかしい

composer ちゃんと入れた？

## Vuetify (Default)

This is the official scaffolding tool for Vuetify, designed to give you a head start in building your new Vuetify application. It sets up a base template with all the necessary configurations and standard directory structure, enabling you to begin development without the hassle of setting up the project from scratch.

## ❗️ Important Links

- 📄 [Docs](https://vuetifyjs.com/)
- 🚨 [Issues](https://issues.vuetifyjs.com/)
- 🏬 [Store](https://store.vuetifyjs.com/)
- 🎮 [Playground](https://play.vuetifyjs.com/)
- 💬 [Discord](https://community.vuetifyjs.com)

## 💿 Install

Set up your project using your preferred package manager. Use the corresponding command to install the dependencies:

| Package Manager                                           | Command        |
| --------------------------------------------------------- | -------------- |
| [yarn](https://yarnpkg.com/getting-started)               | `yarn install` |
| [npm](https://docs.npmjs.com/cli/v7/commands/npm-install) | `npm install`  |
| [pnpm](https://pnpm.io/installation)                      | `pnpm install` |
| [bun](https://bun.sh/#getting-started)                    | `bun install`  |

After completing the installation, your environment is ready for Vuetify development.

## ✨ Features

- 🖼️ **Optimized Front-End Stack**: Leverage the latest Vue 3 and Vuetify 3 for a modern, reactive UI development experience. [Vue 3](https://v3.vuejs.org/) | [Vuetify 3](https://vuetifyjs.com/en/)
- 🗃️ **State Management**: Integrated with [Pinia](https://pinia.vuejs.org/), the intuitive, modular state management solution for Vue.
- 🚦 **Routing and Layouts**: Utilizes Vue Router for SPA navigation and vite-plugin-vue-layouts-next for organizing Vue file layouts. [Vue Router](https://router.vuejs.org/) | [vite-plugin-vue-layouts-next](https://github.com/loicduong/vite-plugin-vue-layouts-next)
- 💻 **Enhanced Development Experience**: Benefit from TypeScript's static type checking and the ESLint plugin suite for Vue, ensuring code quality and consistency. [TypeScript](https://www.typescriptlang.org/) | [ESLint Plugin Vue](https://eslint.vuejs.org/)
- ⚡ **Next-Gen Tooling**: Powered by Vite, experience fast cold starts and instant HMR (Hot Module Replacement). [Vite](https://vitejs.dev/)
- 🧩 **Automated Component Importing**: Streamline your workflow with unplugin-vue-components, automatically importing components as you use them. [unplugin-vue-components](https://github.com/antfu/unplugin-vue-components)
- 🛠️ **Strongly-Typed Vue**: Use vue-tsc for type-checking your Vue components, and enjoy a robust development experience. [vue-tsc](https://github.com/johnsoncodehk/volar/tree/master/packages/vue-tsc)

These features are curated to provide a seamless development experience from setup to deployment, ensuring that your Vuetify application is both powerful and maintainable.

## 💡 Usage

This section covers how to start the development server and build your project for production.

### Starting the Development Server

To start the development server with hot-reload, run the following command. The server will be accessible at [http://localhost:3000](http://localhost:3000):

```bash
yarn dev
```

(Repeat for npm, pnpm, and bun with respective commands.)

> Add NODE_OPTIONS='--no-warnings' to suppress the JSON import warnings that happen as part of the Vuetify import mapping. If you are on Node [v21.3.0](https://nodejs.org/en/blog/release/v21.3.0) or higher, you can change this to NODE_OPTIONS='--disable-warning=5401'. If you don't mind the warning, you can remove this from your package.json dev script.

### Building for Production

To build your project for production, use:

```bash
yarn build
```

(Repeat for npm, pnpm, and bun with respective commands.)

Once the build process is completed, your application will be ready for deployment in a production environment.

## 💪 Support Vuetify Development

This project is built with [Vuetify](https://vuetifyjs.com/en/), a UI Library with a comprehensive collection of Vue components. Vuetify is an MIT licensed Open Source project that has been made possible due to the generous contributions by our [sponsors and backers](https://vuetifyjs.com/introduction/sponsors-and-backers/). If you are interested in supporting this project, please consider:

- [Requesting Enterprise Support](https://support.vuetifyjs.com/)
- [Sponsoring John on Github](https://github.com/users/johnleider/sponsorship)
- [Sponsoring Kael on Github](https://github.com/users/kaelwd/sponsorship)
- [Supporting the team on Open Collective](https://opencollective.com/vuetify)
- [Becoming a sponsor on Patreon](https://www.patreon.com/vuetify)
- [Becoming a subscriber on Tidelift](https://tidelift.com/subscription/npm/vuetify)
- [Making a one-time donation with Paypal](https://paypal.me/vuetify)

## 📑 License

[MIT](http://opensource.org/licenses/MIT)

Copyright (c) 2016-present Vuetify, LLC
