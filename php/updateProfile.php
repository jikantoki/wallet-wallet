<?php
require_once '../env.php'; //環境変数読み込み
require_once './settings.php'; //ルートディレクトリ読み込み
require_once DIR_ROOT . '/php/myAutoLoad.php'; //自動読み込み
require_once DIR_ROOT . '/php/functions/authAPIforUse.php'; //APIが有効かどうか自動判定
require_once DIR_ROOT . '/php/functions/authAccountforUse.php'; //ログイン状態が有効かどうか判定


/**
 * Base64エンコードされた画像データをサーバーに保存し、そのURLを返します。
 *
 * @param string $base64_data Base64画像文字列（データURIスキーム含む場合あり）
 * @return string|false 保存された画像の公開URL、失敗した場合は false
 */
function save_base64_image_to_server($base64_data)
{
  /** そもそもURLのやつはそのままでいい */
  if (substr($base64_data, 0, 8) == 'https://') {
    return $base64_data;
  }
  // 1. 設定項目
  $upload_dir = 'uploads/';
  $base_url = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $upload_dir;

  if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
      error_log("Failed to create upload directory: " . $upload_dir);
      return false;
    }
  }

  // 2. データURIスキームのプレフィックスと純粋なBase64データを分離（正規表現を使用）
  $mime_type = 'image/png'; // デフォルト
  $encoded_image = $base64_data;

  // 正規表現でMIMEタイプとエンコード済みデータを抽出
  if (preg_match('/^data:(image\/(png|jpeg));base64,(.*)$/', $base64_data, $matches)) {
    $mime_type = $matches[1];
    $encoded_image = $matches[3];
  }
  // 上記にマッチしない場合は、入力全体が純粋なBase64文字列とみなす

  // 3. Base64文字列をバイナリデータにデコード
  $decoded_image = base64_decode($encoded_image);

  if ($decoded_image === false || strlen($decoded_image) === 0) {
    // デコード失敗または空データ
    error_log("Base64 decoding failed or resulted in empty data for input.");
    return false;
  }

  // 4. MIMEタイプに基づいてファイル拡張子を決定
  $extension = (strpos($mime_type, 'jpeg') !== false) ? '.jpg' : '.png';

  // 5. 一意なファイル名を生成し、保存パスとURLを構築
  $file_name = uniqid('img_') . $extension;
  $file_path = $upload_dir . $file_name;
  $file_url = $base_url . $file_name;

  // 6. ファイルをサーバーに保存
  // file_put_contentsは書き込んだバイト数を返す
  $bytes_written = file_put_contents($file_path, $decoded_image);

  if ($bytes_written !== false && $bytes_written > 0) {
    return $file_url;
  } else {
    error_log("Failed to write file to disk. Check permissions. Bytes written: " . ($bytes_written === false ? 'false' : $bytes_written));
    return false;
  }
}

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
$secretId = idToSecretId($id);

$icon = "";
if (isset($_POST['icon'])) {
  $icon = save_base64_image_to_server($_POST['icon']) ?? '';
}

$coverImg = "";
if (isset($_POST['coverImg'])) {
  $coverImg = save_base64_image_to_server($_POST['coverImg']) ?? '';
}

$name = "";
if (isset($_POST['name'])) {
  $name = str_replace("'", "\\'", $_POST['name']);
}

$message = "";
if (isset($_POST['message'])) {
  $message = str_replace("'", "\\'", $_POST['message']);
}

// 将来的な拡張用
$links = "";
if (isset($_POST['links'])) {
  $links = str_replace("'", "\\'", $_POST['links']);
}

SQL("
update user_profile_list set
icon = '{$icon}',
coverImg = '{$coverImg}',
name = '{$name}',
message = '{$message}'
where secretId = '{$secretId}';
");

echo json_encode([
  'status' => 'ok',
  'id' => $id,
  'iconUrl' => $icon,
  'coverImgUrl' => $coverImg,
]);
