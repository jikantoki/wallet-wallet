<?php
require_once '../env.php'; //環境変数読み込み
require_once './settings.php'; //ルートディレクトリ読み込み
require_once DIR_ROOT . '/php/myAutoLoad.php'; //自動読み込み
require_once DIR_ROOT . '/php/functions/authAPIforUse.php'; //APIが有効かどうか自動判定

if (
  !isset($_SERVER['HTTP_TARGETID']) &&
  !isset($_SERVER['HTTP_ACCEPT'])
) {
  echo json_encode([
    'status' => 'invalid',
    'reason' => 'invalid authentication information',
    'errCode' => 10
  ]);
  exit;
}

$myId = $_SERVER['HTTP_ID'];
$targetId = $_SERVER['HTTP_TARGETID'];
$accept = $_SERVER['HTTP_FRIENDACCEPT'] == 'true';

$mySecretId = idToSecretId($myId);
$targetSecretId = idToSecretId($targetId);

$friendStatus = $accept ? true : false;

$unixtime = time();

if ($friendStatus) {
  SQL("
    update follow_list set
    status = 'friend',
    unixtime = {$unixtime}
    where
    fromUserId = '{$targetSecretId}'
    and
    toUserId = '{$mySecretId}';
  ");

  $toMailaddress = SQLfind('user_mail_list', 'secretId', $targetSecretId);
  sendMail($toMailaddress['mailAddress'], '友達申請リクエスト承認のお知らせ', "
  <p>{$myId}が友達申請を承認しました。</p>
  <br>
  <p>アプリを開いてこう！</p>
  ");
} else {
  SQL("
    delete from follow_list
    where
    fromUserId = '{$targetSecretId}'
    and
    toUserId = '{$mySecretId}';
  ");
  SQL("
    delete from follow_list
    where
    fromUserId = '{$mySecretId}'
    and
    toUserId = '{$targetSecretId}';
  ");
}

echo json_encode([
  'status' => 'ok',
  'reason' => 'Thank you!',
  'friendStatus' => $friendStatus
]);
