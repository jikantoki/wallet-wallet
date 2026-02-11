<?php
require_once '../env.php'; //環境変数読み込み
require_once './settings.php'; //ルートディレクトリ読み込み
require_once DIR_ROOT . '/php/myAutoLoad.php'; //自動読み込み
require_once DIR_ROOT . '/php/functions/authAPIforUse.php'; //APIが有効かどうか自動判定

if (
  !isset($_SERVER['HTTP_ID']) ||
  !isset($_SERVER['HTTP_MAILADDRESS']) ||
  !isset($_SERVER['HTTP_TOKEN']) ||
  !isset($_SERVER['HTTP_NEWPASSWORD']) ||
  $_SERVER['HTTP_TOKEN'] === ''
) {
  echo json_encode([
    'status' => 'invalid',
    'reason' => 'invalid authentication information',
    'errCode' => 10
  ]);
  exit;
}

$id = $_SERVER['HTTP_ID'];
$mailAddress = $_SERVER['HTTP_MAILADDRESS'];
$otp = $_SERVER['HTTP_TOKEN'];
$newPassword = $_SERVER['HTTP_NEWPASSWORD'];
if (mb_strlen($newPassword) < 8 || mb_strlen($newPassword) > 64) {
  echo json_encode([
    'status' => 'invalid',
    'reason' => 'invalid password',
    'errCode' => 15
  ]);
  exit;
}
$res = authTokenForResetPassword($id, $mailAddress, $otp, $newPassword);
if ($res) {
  echo json_encode([
    'status' => 'ok',
    'reason' => 'Thank you!',
    'id' => $id
  ]);
} else {
  echo json_encode([
    'status' => 'ng',
    'reason' => 'Unknown user',
    'errCode' => 20
  ]);
}
