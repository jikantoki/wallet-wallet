<?php
require_once '../env.php'; //環境変数読み込み
require_once './settings.php'; //ルートディレクトリ読み込み
require_once DIR_ROOT . '/php/myAutoLoad.php'; //自動読み込み
require_once DIR_ROOT . '/php/functions/authAPIforUse.php'; //APIが有効かどうか自動判定

if (
  !isset($_SERVER['HTTP_ID']) ||
  !isset($_SERVER['HTTP_PASSWORD'])
) {
  echo json_encode([
    'status' => 'invalid',
    'reason' => 'invalid authentication information',
    'errCode' => 10
  ]);
  exit;
}

$id = $_SERVER['HTTP_ID'];
$password = $_SERVER['HTTP_PASSWORD'];
$otp = requestOnetimeToken($id, $password);
if ($otp) {
  $otpString = (string) $otp;
  $otpFirst = substr($otpString, 0, 3);
  $otpSecond = substr($otpString, 3, 6);
  $otpForMail = $otpFirst . '-' . $otpSecond;
  echo json_encode([
    'status' => 'ok',
    'reason' => 'Send mail to your mailaddress!',
    'id' => $id
  ]);
  $secretId = idToSecretId($id);
  $mailAddress = secretIdToMailAddress($secretId);
  sendMail($mailAddress, 'アクセストークンのお知らせ', "
  <p>アクセストークンはこちら！</p>
  <h1>{$otpForMail}</h1>
  <p>このコードは一回のみ有効です。</p>
  <br>
  <p>もし身に覚えがない場合は、今すぐパスワードを変更してください。</p>
  ");
} else {
  echo json_encode([
    'status' => 'ng',
    'reason' => 'Unknown user',
    'errCode' => 20
  ]);
}
