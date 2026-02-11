<?php
require_once '../env.php'; //環境変数読み込み
require_once './settings.php'; //ルートディレクトリ読み込み
require_once DIR_ROOT . '/php/myAutoLoad.php'; //自動読み込み
require_once DIR_ROOT . '/php/functions/authAPIforUse.php'; //APIが有効かどうか自動判定
require_once DIR_ROOT . '/php/functions/authAccountforUse.php'; //ログイン状態が有効かどうか判定

if (
  !isset($_SERVER['HTTP_ID']) &&
  !isset($_SERVER['HTTP_REQUESTUSERID'])
) {
  echo json_encode([
    'status' => 'invalid',
    'reason' => 'invalid authentication information',
    'errCode' => 10
  ]);
  exit;
}

$fromId = $_SERVER['HTTP_ID'];
$toId = $_SERVER['HTTP_REQUESTUSERID'];

if ($fromId === $toId) {
  echo json_encode([
    'status' => 'cannot',
    'reason' => 'cannot follow yourself',
    'errCode' => 6000
  ]);
  exit;
}

$fromRandId = idToSecretId($fromId);
$toRandId = idToSecretId($toId);
$unixtime = time();

//既に同じリクエストが来ていないか？逆リクエストも含めて探す
$alreadyRequest1 = SQLfindSome('follow_list', [
  [
    'key' => 'fromUserId',
    'value' => $toRandId,
    'func' => '='
  ],
  [
    'key' => 'toUserId',
    'value' => $fromRandId,
    'func' => '='
  ]
]);
$alreadyRequest2 = SQLfindSome('follow_list', [
  [
    'key' => 'toUserId',
    'value' => $toRandId,
    'func' => '='
  ],
  [
    'key' => 'fromUserId',
    'value' => $fromRandId,
    'func' => '='
  ]
]);
if ($alreadyRequest1 || $alreadyRequest2) {
  echo json_encode([
    'status' => 'cannot',
    'reason' => 'the request already exists',
    'errCode' => 7000
  ]);
  exit;
}

$followId = SQLmakeRandomId('follow_list', 'followId');

SQLinsert('follow_list', [
  'followId' => $followId,
  'fromUserId' => $fromRandId,
  'toUserId' => $toRandId,
  'unixtime' => $unixtime,
  'status' => 'request'
]);

$toMailaddress = SQLfind('user_mail_list', 'secretId', $toRandId);
sendMail($toMailaddress['mailAddress'], '友達申請リクエストのお知らせ', "
<p>{$fromId}より友達申請のリクエストが来ています。</p>
<br>
<p>アプリを開いて、友達リクエストを承認してください。</p>
");

echo json_encode([
  'status' => 'ok',
  'fromId' => $fromId,
  'toId' => $toId,
  'followStatus' => 'request',
  'unixtime' => $unixtime
]);
