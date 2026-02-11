<?php
require_once DIR_ROOT . '/php/functions/functions.php';
require_once DIR_ROOT . '/php/functions/database.php';

/**
 * APIの有効性を調べる
 *
 * @param string $id
 * @param string $token
 * @param string $password
 * @return void アカウントが有効ならtrue
 */
function authAPI($id, $token, $password)
{
  $isFind = SQLfindSome('api_list', [
    [
      'key' => 'apiId',
      'value' => $id,
      'func' => '='
    ],
    [
      'key' => 'apiToken',
      'value' => $token,
      'func' => '='
    ]
  ]);
  if (!$isFind) {
    //アカウント不明
    return false;
  }

  if ($isFind) {
    if (password_verify($password, $isFind['apiAccessKey'])) {
      //APIアカウント有効
      return true;
    }
  }
  //パスワードマッチせず
  return null;
}
