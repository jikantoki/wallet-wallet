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
$lat = $_SERVER['HTTP_LAT'];
$lng = $_SERVER['HTTP_LNG'];
if (isset($_SERVER['HTTP_BATTERYLEVEL'])) {
  $batteryLevel = $_SERVER['HTTP_BATTERYLEVEL'];
} else {
  $batteryLevel = 'null';
}
if (isset($_SERVER['HTTP_BATTERYCHARGING'])) {
  $batteryCharging = $_SERVER['HTTP_BATTERYCHARGING'];
} else {
  $batteryCharging = 'null';
}
$secretId = idToSecretId($id);
$ipAddress = $_SERVER['REMOTE_ADDR'];
$unixtime = time();

//既にデータが入っているか確認
$res = SQLfind('location_list', 'secretId', $secretId);
if (!$res) {
  SQLinsert('location_list', [
    'secretId' => $secretId,
    'lat' => 0,
    'lng' => 0,
    'unixtime' => 0,
    'ipAddress' => ''
  ]);
}
$sql = "
  update location_list set
  lat = {$lat},
  lng = {$lng},
  unixtime = {$unixtime},
  ipAddress = '{$ipAddress}',
  batteryLevel = {$batteryLevel},
  batteryCharging = {$batteryCharging}
  where secretId = '{$secretId}';
";
SQL($sql);

echo json_encode([
  'status' => 'ok',
  'id' => $id,
  'unixtime' => $unixtime,
]);
