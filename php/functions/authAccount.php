<?php
require_once DIR_ROOT . '/php/functions/functions.php';
require_once DIR_ROOT . '/php/functions/database.php';

/**
 * アカウントにログインできているか確認する
 *
 * @param string $secretId
 * @param string $token
 * @return void トークンが有効ならtrue
 */
function authAccount($secretId, $token)
{
  $account = SQLfindAll('user_accesstoken_list', 'secretId', $secretId);
  if ($account) {
    foreach ($account as $ac) {
      if (password_verify($token, $ac['token'])) {
        //アカウント有効
        return true;
      }
    }
    //トークンちゃう
    return false;
  } else {
    //アカウント不明
    return false;
  }
}
