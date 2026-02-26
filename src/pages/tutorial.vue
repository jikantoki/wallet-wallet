<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
)
  .top-android-15-or-higher(v-if="settings.hidden.isAndroid15OrHigher")
  //-- 56px: ナビボタン, 48px: ページインジケーターとマージン --
  v-window(
    v-model="currentPage"
    style="height: calc(100% - 56px - 48px);"
  )
    //-- ページ1: ようこそ --
    v-window-item(:value="0")
      .tutorial-page
        .tutorial-hero
          v-icon.tutorial-icon(color="primary") mdi-wallet
        h1.tutorial-title Wallet Wallet へようこそ！
        p.tutorial-subtitle カードと銀行口座を安全に管理するアプリです。
        .feature-cards
          .feature-card
            v-icon(color="blue" size="32") mdi-credit-card
            p クレジットカード管理
          .feature-card
            v-icon(color="green" size="32") mdi-bank
            p 銀行口座管理
          .feature-card
            v-icon(color="orange" size="32") mdi-shield-lock
            p セキュリティ保護
          .feature-card
            v-icon(color="purple" size="32") mdi-swap-horizontal
            p バックアップ・復元
    //-- ページ2: クレジットカードの登録 --
    v-window-item(:value="1")
      .tutorial-page
        .tutorial-hero
          v-icon.tutorial-icon(color="blue") mdi-credit-card-plus
        h1.tutorial-title クレジットカードの登録
        p.tutorial-subtitle カード情報を安全に保管しましょう。
        .step-list
          .step-item
            .step-badge 1
            .step-text
              p.step-title ＋ボタンをタップ
              p.step-desc 画面右下の追加ボタンをタップします
          .step-item
            .step-badge 2
            .step-text
              p.step-title 「クレジットカード」を選択
              p.step-desc 登録するカードの種類を選びます
          .step-item
            .step-badge 3
            .step-text
              p.step-title カード情報を入力
              p.step-desc カード番号・有効期限・セキュリティコードを入力
          .step-item
            .step-badge 4
            .step-text
              p.step-title 保存して完了！
              p.step-desc 「保存」をタップするとカードが登録されます
    //-- ページ3: 銀行口座の登録 --
    v-window-item(:value="2")
      .tutorial-page
        .tutorial-hero
          v-icon.tutorial-icon(color="green") mdi-bank-plus
        h1.tutorial-title 銀行口座の登録
        p.tutorial-subtitle 銀行口座情報を一元管理しましょう。
        .step-list
          .step-item
            .step-badge 1
            .step-text
              p.step-title ＋ボタンをタップ
              p.step-desc 画面右下の追加ボタンをタップします
          .step-item
            .step-badge 2
            .step-text
              p.step-title 「銀行口座」を選択
              p.step-desc 登録する口座の種類を選びます
          .step-item
            .step-badge 3
            .step-text
              p.step-title 口座情報を入力
              p.step-desc 金融機関・支店・口座番号・名義人を入力
          .step-item
            .step-badge 4
            .step-text
              p.step-title 保存して完了！
              p.step-desc 「保存」をタップすると口座が登録されます
    //-- ページ4: セキュリティ --
    v-window-item(:value="3")
      .tutorial-page
        .tutorial-hero
          v-icon.tutorial-icon(color="orange") mdi-shield-lock
        h1.tutorial-title セキュリティについて
        p.tutorial-subtitle 大切なデータをしっかり守ります。
        .feature-list
          .feature-row
            .feature-icon-wrap(style="background: rgba(255,152,0,0.15);")
              v-icon(color="orange" size="28") mdi-lock
            .feature-text
              p.feature-title デバイス内に安全保存
              p.feature-desc データは外部サーバーに送信されません
          .feature-row
            .feature-icon-wrap(style="background: rgba(255,152,0,0.15);")
              v-icon(color="orange" size="28") mdi-fingerprint
            .feature-text
              p.feature-title 生体認証対応
              p.feature-desc 指紋・顔認証でデータをロック可能
          .feature-row
            .feature-icon-wrap(style="background: rgba(255,152,0,0.15);")
              v-icon(color="orange" size="28") mdi-cloud-off
            .feature-text
              p.feature-title オフライン動作
              p.feature-desc インターネット不要でいつでも閲覧可能
          .feature-row
            .feature-icon-wrap(style="background: rgba(255,152,0,0.15);")
              v-icon(color="orange" size="28") mdi-eye-off
            .feature-text
              p.feature-title プライバシー保護
              p.feature-desc カード番号は伏字で安全に表示
    //-- ページ5: インポート・エクスポート --
    v-window-item(:value="4")
      .tutorial-page
        .tutorial-hero
          v-icon.tutorial-icon(color="purple") mdi-swap-horizontal
        h1.tutorial-title インポート・エクスポート
        p.tutorial-subtitle データのバックアップと復元ができます。
        .feature-list
          .feature-row
            .feature-icon-wrap(style="background: rgba(156,39,176,0.15);")
              v-icon(color="purple" size="28") mdi-export
            .feature-text
              p.feature-title エクスポート（バックアップ）
              p.feature-desc JSON形式または暗号化形式でデータを保存
          .feature-row
            .feature-icon-wrap(style="background: rgba(156,39,176,0.15);")
              v-icon(color="purple" size="28") mdi-import
            .feature-text
              p.feature-title インポート（復元）
              p.feature-desc バックアップファイルからデータを復元
          .feature-row
            .feature-icon-wrap(style="background: rgba(156,39,176,0.15);")
              v-icon(color="purple" size="28") mdi-shield-key
            .feature-text
              p.feature-title 暗号化バックアップ
              p.feature-desc パスワードで暗号化した安全なバックアップも可能
        .start-button-wrap
          v-btn(
            @click="$router.push('/')"
            color="primary"
            size="large"
            prepend-icon="mdi-check-circle"
            style="font-size: 1.1em; padding: 0 32px;"
          ) 使ってみる！
  //-- ページインジケーター --
  .page-indicators
    .dot(
      v-for="i in 5"
      :key="i"
      :class="{ active: currentPage === i - 1 }"
      @click="currentPage = i - 1"
    )
  //-- ナビゲーションボタン --
  .nav-buttons
    v-btn(
      v-if="currentPage > 0"
      variant="text"
      prepend-icon="mdi-chevron-left"
      @click="currentPage--"
    ) 前へ
    v-spacer
    v-btn(
      v-if="currentPage < 4"
      color="primary"
      append-icon="mdi-chevron-right"
      @click="currentPage++"
    ) 次へ
    v-btn(
      v-if="currentPage === 4"
      color="primary"
      prepend-icon="mdi-check-circle"
      @click="$router.push('/')"
    ) 使ってみる！
</template>

<script lang="ts">
  import { useSettingsStore } from '@/stores/settings'

  export default {
    data () {
      return {
        /** 現在表示しているページ（0〜4） */
        currentPage: 0,
        /** 設定ストア */
        settings: useSettingsStore(),
      }
    },
  }
</script>

<style lang="scss" scoped>
.top-android-15-or-higher {
  height: 40px;
}

.tutorial-page {
  padding: 24px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  height: 100%;
  overflow-y: auto;
}

.tutorial-hero {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background-color: rgba(var(--v-theme-primary), 0.1);
  margin-bottom: 4px;
}

.tutorial-icon {
  font-size: 56px !important;
}

.tutorial-title {
  font-size: 1.5em;
  font-weight: bold;
  text-align: center;
  margin: 0;
  padding: 0;
}

.tutorial-subtitle {
  font-size: 1em;
  text-align: center;
  opacity: 0.7;
  margin: 0 0 8px 0;
}

.feature-cards {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  width: 100%;

  .feature-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px 12px;
    border-radius: 12px;
    background-color: rgba(var(--v-theme-on-surface), 0.06);
    font-size: 0.85em;
    text-align: center;
    font-weight: 500;

    p {
      margin: 0;
    }
  }
}

.step-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  width: 100%;

  .step-item {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    gap: 14px;

    .step-badge {
      min-width: 32px;
      height: 32px;
      border-radius: 50%;
      background-color: rgb(var(--v-theme-primary));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 0.95em;
    }

    .step-text {
      flex: 1;

      .step-title {
        font-weight: bold;
        font-size: 1em;
        margin: 0 0 2px 0;
      }

      .step-desc {
        font-size: 0.85em;
        opacity: 0.7;
        margin: 0;
      }
    }
  }
}

.feature-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  width: 100%;

  .feature-row {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 14px;

    .feature-icon-wrap {
      min-width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .feature-text {
      flex: 1;

      .feature-title {
        font-weight: bold;
        font-size: 1em;
        margin: 0 0 2px 0;
      }

      .feature-desc {
        font-size: 0.85em;
        opacity: 0.7;
        margin: 0;
      }
    }
  }
}

.start-button-wrap {
  display: flex;
  justify-content: center;
  margin-top: 8px;
  width: 100%;
}

.page-indicators {
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  gap: 8px;
  height: 32px;

  .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: rgba(var(--v-theme-on-surface), 0.25);
    cursor: pointer;
    transition: all 0.2s;

    &.active {
      width: 20px;
      border-radius: 4px;
      background-color: rgb(var(--v-theme-primary));
    }
  }
}

.nav-buttons {
  display: flex;
  flex-direction: row;
  align-items: center;
  padding: 4px 12px;
  height: 56px;
}
</style>
