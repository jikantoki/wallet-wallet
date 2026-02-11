<?php
require_once '../env.php'; //環境変数読み込み
require_once './settings.php'; //ルートディレクトリ読み込み
require_once DIR_ROOT . '/php/myAutoLoad.php'; //自動読み込み
require_once DIR_ROOT . '/php/functions/authAPIforUse.php'; //APIが有効かどうか自動判定

if (
  !isset($_SERVER['HTTP_ID']) ||
  !isset($_SERVER['HTTP_MAILADDRESS'])
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
$otp = requestOnetimeTokenForgotPassword($id, $mailAddress);
if ($otp) {
  echo json_encode([
    'status' => 'ok',
    'reason' => 'Send mail to your mailaddress!',
    'id' => $id
  ]);
  $secretId = idToSecretId($id);
  $mailAddress = secretIdToMailAddress($secretId);
  sendMail($mailAddress, 'アクセストークンのお知らせ', '<p>アクセストークンはこちら！</p><h1>' . $otp . '</h1><p>このコードは一回のみ有効やで<br>身に覚えがなかったらヤバいかも気を付けてね</p>');
} else {
  echo json_encode([
    'status' => 'ng',
    'reason' => 'Unknown user',
    'errCode' => 20
  ]);
}
