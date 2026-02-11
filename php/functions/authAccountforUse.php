<?php
//ログイン状態が有効かどうかをGET要素から自動判定し、ダメそうなら大元から処理を停止

if (
  !isset($_SERVER['HTTP_ID']) ||
  !isset($_SERVER['HTTP_TOKEN'])
) {
  echo json_encode([
    'status' => 'invalid',
    'reason' => 'invalid authentication information',
    'errCode' => 2000
  ]);
  exit;
}

$id = $_SERVER['HTTP_ID'];
$token = $_SERVER['HTTP_TOKEN'];
$secretId = idToSecretId($id);
if (!$secretId) {
  echo json_encode([
    'status' => 'invalid',
    'reason' => 'unknown account',
    'errCode' => 2001
  ]);
  exit;
}
$isAuthed = authAccount($secretId, $token);
if (!$isAuthed) {
  echo json_encode([
    'status' => 'ng',
    'reason' => 'authentication failed',
    'errCode' => 3000
  ]);
  exit;
}
