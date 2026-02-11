<?php
require_once '../env.php'; //環境変数読み込み
require_once './settings.php'; //ルートディレクトリ読み込み
require_once DIR_ROOT . '/php/myAutoLoad.php'; //自動読み込み
require_once DIR_ROOT . '/php/functions/authAPIforUse.php'; //APIが有効かどうか自動判定
require_once DIR_ROOT . '/php/functions/authAccountforUse.php'; //ログイン状態が有効かどうか判定

if (
  !isset($_SERVER['HTTP_ID'])
) {
  echo json_encode([
    'status' => 'invalid',
    'reason' => 'invalid authentication information',
    'errCode' => 10
  ]);
  exit;
}

$id = $_SERVER['HTTP_ID'];
$secretId = idToSecretId($id);

/** 友達に関しては位置情報も送信する */
$withLocation = false;
if(isset($_SERVER['HTTP_WITHLOCATION']) &&
  $_SERVER['HTTP_WITHLOCATION'] == 'true'){
  $withLocation = true;
}

$res = SQLfetchAll("
select * from follow_list where
fromUserId = '{$secretId}'
or
toUserId = '{$secretId}'
;
");

$cnt = 0;
foreach ($res as $friend) {
  $friendRealId = null;
  if ($friend['fromUserId'] == $secretId) {
    $friendRealId = secretIdToId($friend['toUserId']);
    $res[$cnt]['friendProfile'] = SQLfind('user_profile_list', 'secretId', $friend['toUserId']);

    if($withLocation && $friend['status'] == 'friend') {
      $res[$cnt]['location'] = SQLfind('location_list','secretId',$friend['toUserId']);
    }
  } else {
    $friendRealId = secretIdToId($friend['fromUserId']);
    $res[$cnt]['friendProfile'] = SQLfind('user_profile_list', 'secretId', $friend['fromUserId']);

    if($withLocation && $friend['status'] == 'friend') {
      $res[$cnt]['location'] = SQLfind('location_list','secretId',$friend['fromUserId']);
    }
  }
  $res[$cnt]['friendRealId'] = $friendRealId;
  $cnt++;
}


echo json_encode([
  'status' => 'ok',
  'id' => $id,
  'friendList' => $res,
  'mySecretId' => $secretId
]);
