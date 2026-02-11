<?php
//アカウント作成用
require_once '../env.php'; //環境変数読み込み
require_once './settings.php'; //ルートディレクトリ読み込み
require_once DIR_ROOT . '/php/myAutoLoad.php'; //自動読み込み
require_once DIR_ROOT . '/php/functions/authAPIforUse.php'; //APIが有効かどうか自動判定

if (
  !isset($_SERVER['HTTP_USERNAME']) ||
  !isset($_SERVER['HTTP_PASSWORD']) ||
  !isset($_SERVER['HTTP_MAILADDRESS'])
) {
  echo json_encode([
    'status' => 'invalid',
    'reason' => 'invalid authentication information',
    'errCode' => 10
  ]);
  exit;
}
if (
  $_SERVER['HTTP_USERNAME'] === '' ||
  $_SERVER['HTTP_PASSWORD'] === '' ||
  $_SERVER['HTTP_MAILADDRESS'] === ''
) {
  echo json_encode([
    'status' => 'invalid',
    'reason' => 'invalid authentication information',
    'errCode' => 11
  ]);
  exit;
}
$username = $_SERVER['HTTP_USERNAME'];
$password = $_SERVER['HTTP_PASSWORD'];
$mailAddress = $_SERVER['HTTP_MAILADDRESS'];

//論理桁数でカウントするためmb_strlenしない
$passwordLength = strlen($password);
if ($passwordLength < 4 || $passwordLength > 64) {
  echo json_encode([
    'status' => 'invalid',
    'reason' => 'invalid password',
    'errCode' => 20
  ]);
  exit;
}

$response = makeAccount($username, $password, $mailAddress);
if (!$response) {
  sendMail(
    $mailAddress,
    'アカウント作成完了のお知らせ',
    "
    <p>アカウントの作成が完了しました。</p>
    <br>
    <h3>ユーザー名: {$username}</h3>
    <br>
    <p>引き続き Wallet Wallet をよろしくお願いいたします。</p>"
  );
  //アカウント作れた
  echo json_encode([
    'status' => 'ok',
    'reason' => 'Thank you! Please check your mailbox!'
  ]);
} else {
  //既に存在しているとか
  echo json_encode([
    'status' => 'ng',
    'reason' => 'This account already exists',
    'errCode' => 30
  ]);
}
