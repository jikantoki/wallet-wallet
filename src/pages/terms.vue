<template lang="pug">
v-card(
  style="width: 100%; height: 100%;"
  :class="settings.hidden.isAndroid15OrHigher ? 'top-android-15-or-higher' : ''"
  )
  v-card-actions
    p.ml-2(style="font-size: 1.3em") 利用規約
    v-spacer
    v-btn(
      text
      @click="$router.back()"
      icon="mdi-close"
      )
  v-card-text(style="height: inherit; overflow-y: auto;")
    h1.my-4 利用規約
    h2.my-2 1. ざっくり、こういうのはやめてね！
    ul.ml-8
      li 他人の権利を侵害する行為
      li 不正アクセスやシステムの妨害行為
      li 違法なコンテンツの配布や共有
      li スパム行為や迷惑行為
    hr.my-4
    h2.my-2 2. 何かあった時は
    ul.ml-8
      li とりあえず連絡してね！
      li IPアドレスの開示は出来るだけ協力するよ！
      li 余程のことがないと賠償金的なやつは払わないよ！
    hr.my-4
    h2.my-2 3. 運営が被害を受けたと認定した場合
    ul.ml-8
      li 常にIPアドレスを保存しているので、それを使って開示請求を試みるよ！
      li いけそうなら、民事と刑事で訴訟するかも！
      li その前に謝ってくれたら和解も検討するよ！
    hr.my-4
    h1.my-4 プライバシーポリシー
    p プライバシーポリシーについては、以下のリンクをご参照ください。
    v-btn.my-4(
      @click="openURL('https://enoki.xyz/privacy')"
      style="background-color: rgb(var(--v-theme-primary)); color: white;"
    ) プライバシーポリシー
    .my-16
</template>

<script lang="ts">
  import { Browser } from '@capacitor/browser'
  import { useSettingsStore } from '@/stores/settings'

  export default {
    data () {
      return {
        settings: useSettingsStore(),
      }
    },
    async mounted () {},
    methods: {
      /** URLをブラウザで開く */
      async openURL (url: string) {
        await Browser.open({ url: url })
      },
    },
  }
</script>

<style lang="scss" scoped>
.top-android-15-or-higher {
  height: calc(100vh - 40px - 16px)!important;
}
</style>
