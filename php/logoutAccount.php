<?php
require_once '../env.php'; //環境変数読み込み
require_once './settings.php'; //ルートディレクトリ読み込み
require_once DIR_ROOT . '/php/myAutoLoad.php'; //自動読み込み
require_once DIR_ROOT . '/php/functions/authAPIforUse.php'; //APIが有効かどうか自動判定

if (
  !isset($_SERVER['HTTP_ID']) ||
  !isset($_SERVER['HTTP_TOKEN'])
) {
  echo json_encode([
    'status' => 'invalid',
    'reason' => 'invalid authentication information',
    'errCode' => 10
  ]);
  exit;
}

$token = $_SERVER['HTTP_TOKEN'];
$userId = $_SERVER['HTTP_ID'];
//DBから削除
$secretId = idToSecretId($userId);
$res = SQLdeleteSome('user_accesstoken_list', [
  [
    'key' => 'secretId',
    'value' => $secretId,
    'func' => '='
  ],
  [
    'key' => 'token',
    'value' => $token,
    'func' => '='
  ]
]);
echo json_encode([
  'status' => 'ok',
  'reason' => 'Deleted!',
  'token' => $token,
  'id' => $userId
]);
