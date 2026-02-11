<?php
//APIが有効かどうかをGET要素から自動判定し、ダメそうなら大元から処理を停止

if (
  !isset($_SERVER['HTTP_APIID']) ||
  !isset($_SERVER['HTTP_APITOKEN']) ||
  !isset($_SERVER['HTTP_APIPASSWORD'])
) {
  echo json_encode([
    'status' => 'invalid',
    'reason' => 'invalid authentication information',
    'errCode' => 1000
  ]);
  exit;
}
$apiid = $_SERVER['HTTP_APIID'];
$apitoken = $_SERVER['HTTP_APITOKEN'];
$apipassword = $_SERVER['HTTP_APIPASSWORD'];

$isAPI = authAPI($apiid, $apitoken, $apipassword);
if (!$isAPI) {
  echo json_encode([
    'status' => 'ng',
    'reason' => 'invalid API',
    'errCode' => 1001
  ]);
  exit;
}
