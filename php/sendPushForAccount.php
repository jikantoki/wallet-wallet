<?php
require_once '../env.php'; //環境変数読み込み
require_once './settings.php'; //ルートディレクトリ読み込み
require_once DIR_ROOT . '/php/myAutoLoad.php'; //自動読み込み
require_once DIR_ROOT . '/php/functions/authAPIforUse.php'; //APIが有効かどうか自動判定
require_once DIR_ROOT . '/php/functions/authAccountforUse.php'; //ログイン状態が有効かどうか判定

if (
  !isset($_SERVER['HTTP_FOR']) ||
  !isset($_POST['title'])
) {
  echo json_encode([
    'status' => 'invalid',
    'reason' => 'invalid authentication information',
    'errCode' => 10
  ]);
  exit;
}
/** 送信元のアカウントID */
$fromId = $_SERVER['HTTP_ID'];
/** 送信元のプロフィール */
$fromProfile = getProfile($fromId);
/** 送信元のアイコン */
$fromIcon = $fromProfile['icon'];
if ($fromIcon && $fromIcon !== '') {
  $icon = $fromIcon;
} else {
  $icon = Default_user_icon;
}
/** 送信先のアカウントID */
$forId = $_SERVER['HTTP_FOR'];
/** 送信先のシークレットID */
$forSecretId = idToSecretId($forId);
if (!$forSecretId) {
  echo json_encode([
    'status' => 'ng',
    'reason' => 'unknown user',
    'errCode' => 10
  ]);
  exit;
}
/** 通知タイトル */
$title = $_POST['title'];
/** 通知本文 */
if (isset($_POST['message'])) {
  $message = $_POST['message'];
} else {
  $message = '';
}
/** 通知に添付する画像 */
if (isset($_POST['image'])) {
  $image = $_POST['image'];
} else {
  $image = '';
}
/** 通知のオプション */
if (isset($_POST['options'])) {
  $options = $_POST['options'];
} else {
  $options = [];
}

$res = sendPushForAccount($forSecretId, $title, $message, $image, $icon, $options);
if ($res) {
  echo json_encode([
    'status' => 'ok',
    'reason' => 'thank you!',
    'deviceCount' => $res
  ]);
} else {
  echo json_encode([
    'status' => 'nothing',
    'reason' => 'system donot have this accounts push info',
    'errCode' => 20
  ]);
}
