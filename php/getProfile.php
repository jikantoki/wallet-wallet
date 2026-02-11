<?php
require_once '../env.php'; //環境変数読み込み
require_once './settings.php'; //ルートディレクトリ読み込み
require_once DIR_ROOT . '/php/myAutoLoad.php'; //自動読み込み
require_once DIR_ROOT . '/php/functions/authAPIforUse.php'; //APIが有効かどうか自動判定

if (
  !isset($_SERVER['HTTP_TARGETID'])
) {
  echo json_encode([
    'status' => 'invalid',
    'reason' => 'invalid authentication information',
    'errCode' => 10
  ]);
  exit;
}

$mySecretId = null;
$getFriendStatus = false;
if (isset($_SERVER['HTTP_ID']) && isset($_SERVER['HTTP_TOKEN'])) {
  $id = $_SERVER['HTTP_ID'];
  $token = $_SERVER['HTTP_TOKEN'];
  $mySecretId = idToSecretId($id);
  if ($mySecretId) {
    $isAuthed = authAccount($mySecretId, $token);
    if ($isAuthed) {
      $getFriendStatus = true;
    }
  }
}

$targetId = $_SERVER['HTTP_TARGETID'];
$res = getProfile($targetId);
$myProfile = null;
if ($res) {
  $friendStatus = null;
  if ($mySecretId) {
    $friendStatus1 = SQLfindSome('follow_list', [
      [
        'key' => 'fromUserId',
        'value' => $res['secretId'],
        'func' => '='
      ],
      [
        'key' => 'toUserId',
        'value' => $mySecretId,
        'func' => '='
      ]
    ]);
    $friendStatus2 = SQLfindSome('follow_list', [
      [
        'key' => 'toUserId',
        'value' => $res['secretId'],
        'func' => '='
      ],
      [
        'key' => 'fromUserId',
        'value' => $mySecretId,
        'func' => '='
      ]
    ]);
    if ($friendStatus1) {
      $friendStatus = $friendStatus1['status'];
    } else if ($friendStatus2) {
      $friendStatus = $friendStatus2['status'];
    }
  }
  echo json_encode([
    'status' => 'ok',
    'reason' => 'Thank you!',
    'res' => $res,
    'id' => $targetId,
    'friendStatus' => $friendStatus
  ]);
} else {
  echo json_encode([
    'status' => 'ng',
    'reason' => 'Unknown user',
    'id' => $targetId,
    'errCode' => 20
  ]);
}
