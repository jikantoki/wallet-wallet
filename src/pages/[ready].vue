<template lang="pug">
  h1 このページは準備中です
  v-btn(
    @click="$router.back()"
  ) 戻る
  v-btn(
    @click="$router.push('/')"
  ) トップページ
</template>
