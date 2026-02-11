<?php
require_once '../env.php'; //環境変数読み込み
require_once './settings.php'; //ルートディレクトリ読み込み
require_once DIR_ROOT . '/php/myAutoLoad.php'; //自動読み込み
require_once DIR_ROOT . '/php/functions/authAPIforUse.php'; //APIが有効かどうか自動判定
require_once DIR_ROOT . '/php/functions/authAccountforUse.php'; //ログイン状態が有効かどうか判定

if (
  !isset($_SERVER['HTTP_ID']) ||
  !isset($_SERVER['HTTP_TOKEN']) ||
  !isset($_SERVER['HTTP_ENDPOINT']) ||
  !isset($_SERVER['HTTP_PUBLICKEY']) ||
  !isset($_SERVER['HTTP_PUSHTOKEN'])
) {
  echo json_encode([
    'status' => 'invalid',
    'reason' => 'invalid authentication information',
    'errCode' => 1
  ]);
  exit;
}
$id = $_SERVER['HTTP_ID'];
$secretId = idToSecretId($id);
$endpoint = $_SERVER['HTTP_ENDPOINT'];
$publickey = $_SERVER['HTTP_PUBLICKEY'];
$pushtoken = $_SERVER['HTTP_PUSHTOKEN'];
$res = SQLfindSome('push_token_list', [
  [
    'key' => 'secretId',
    'value' => $secretId,
    'func' => '='
  ],
  [
    'key' => 'push_endPoint',
    'value' => $endpoint,
    'func' => '='
  ],
  [
    'key' => 'push_publicKey',
    'value' => $publickey,
    'func' => '='
  ],
  [
    'key' => 'push_authToken',
    'value' => $pushtoken,
    'func' => '='
  ]
]);
if ($res) {
  //Pushトークンが登録されているので削除
  SQLdeleteSome('push_token_list', [
    [
      'key' => 'secretId',
      'value' => $secretId,
      'func' => '='
    ],
    [
      'key' => 'push_endPoint',
      'value' => $endpoint,
      'func' => '='
    ],
    [
      'key' => 'push_publicKey',
      'value' => $publickey,
      'func' => '='
    ],
    [
      'key' => 'push_authToken',
      'value' => $pushtoken,
      'func' => '='
    ]
  ]);
  echo json_encode([
    'status' => 'ok',
    'reason' => 'deleted',
    'id' => $id
  ]);
  exit;
} else {
  echo json_encode([
    'status' => 'ng',
    'reason' => 'unknown push token',
    'errCode' => 20
  ]);
}
