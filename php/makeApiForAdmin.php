<?php
require_once '../env.php'; //環境変数読み込み
require_once './settings.php'; //ルートディレクトリ読み込み
require_once DIR_ROOT . '/php/myAutoLoad.php'; //自動読み込み
const API_LIST = 'api_list';
const API_LIST_FORVIEW = 'api_listForView';
const API_SECRET_ID = 'secretId';
$availableApiList = SQLsearchTable(API_LIST);
if ($availableApiList) {
  //$rand = SQLmakeRandomId(API_LIST, API_SECRET_ID);
  //api_listテーブルにランダムなAPIトークンを作成
  $alreadySetUped = SQLfind(API_LIST, API_SECRET_ID, 'default');
  if (!$alreadySetUped) {
    $apiToken = randomString(16);
    $apiAccessKey = randomString(16);
    $apiAccessKeyHashed = password_hash($apiAccessKey, PASSWORD_DEFAULT);
    SQLinsert(API_LIST, [
      API_SECRET_ID => 'default',
      'apiId' => 'default',
      'apiToken' => $apiToken,
      'apiAccessKey' => $apiAccessKeyHashed
    ]);
    SQLinsert(API_LIST_FORVIEW, [
      API_SECRET_ID => 'default',
      'apiName' => 'webapp'
    ]);
    echo 'apiId = "default"<br>apiToken = "' . $apiToken . '"<br>apiAccessKey = "' . $apiAccessKey . '"';
  } else {
    echo 'Already setuped!';
  }
} else {
  echo 'DBのセットアップを先にやってね！';
}
