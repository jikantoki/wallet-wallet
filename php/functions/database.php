<?php

/**
 * ランダムな一文字を生成
 *
 * @return string 1文字
 */
function randomChar()
{
  $words = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '_'];
  return $words[array_rand($words)];
}
/**
 * ランダムな文字列を生成
 *
 * @param string $length 文字数
 * @return string ランダムな文字列
 */
function randomString($length)
{
  $text = '';
  for ($count = 0; $count < $length; $count++) {
    $text = $text . randomChar();
  }
  return $text;
}

/**
 * ランダムなOTPを生成
 *
 * @return int
 */
function randomOTP()
{
  return mt_rand(100000, 999999);
}

/**
 * SQLに接続して、そのPDOを返す
 *
 * @return object
 */
function SQLConnect()
{
  $sql = 'mysql:host=' . MySQL_Host . ';dbname=' . MySQL_DBName . ';charset=utf8mb4';
  try {
    $pdo = new PDO($sql, MySQL_User, MySQL_Password);
    return $pdo;
  } catch (Exception $e) {
    echo $e->getMessage();
    return false;
  }
}

/**
 * SQL文を実行し、最初の一つの結果を取得
 *
 * @param string $sql
 * @return object
 */
function SQL($sql)
{
  try {
    $pdo = SQLConnect();
    $stmt = $pdo->query($sql);
    return $stmt->fetch();
  } catch (\Throwable $th) {
    echo "SQL構文エラー:~~~~~ {$sql} ~~~~~は使用できません";
    throw $th;
  }
}

/**
 * SQL文を実行し、全ての結果を取得
 *
 * @param string $sql
 * @return object
 */
function SQLfetchAll($sql)
{
  try {
    $pdo = SQLConnect();
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
  } catch (\Throwable $th) {
    echo "SQL構文エラー:~~~~~ {$sql} ~~~~~は使用できません";
    throw $th;
  }
}

function SQLselectTable($tableName)
{
  return SQL('select * from ' . $tableName);
}

function SQLshowTable()
{
  return SQL('show tables');
}

function SQLsearchTable($tableName)
{
  return SQL('show tables like "' . $tableName . '"');
}

/**
 * ## 新規テーブルの作成
 * 既にテーブルが存在する場合は1を返す
 *
 * 上手くいったらfalseを返す（重要！！！）
 *
 * ### $arrayの使い方
 * ```php
 * $array = ['キーの名前1'=>'付けたい型','キーの名前2'=>'付けたい型'...];
 * ```
 * 型指定に使えるもの→int、float、varchar(自然数)、text、datetime
 *
 * @param string $tableName
 * @param object $array
 * @return void
 */
function SQLcreateTable($tableName, $array)
{
  if (SQLsearchTable(($tableName))) {
    return 1;
  }
  $array_word = '';
  foreach ($array as $key => $val) {
    $array_word = $array_word . $key . ' ' . $val . ',';
  }
  $array_word = mb_substr($array_word, 0, -1);
  return SQL('create table ' . $tableName . ' (' . $array_word . ')');
}

/**
 * ## 新規コンテンツの挿入
 *
 * ### $arrayの使い方
 * ```php
 * $array = ['列名1'=>'値1','列名2'=>'値2'...];
 * ```
 * @param string $table テーブル名を指定
 * @param object $array
 * @return void
 */
function SQLinsert($table, $array)
{
  if (!SQLsearchTable($table)) {
    return 1;
  }
  $keys = '';
  $values = '';
  foreach ($array as $key => $val) {
    $keys = $keys . $key . ',';
    $values = $values . '"' . $val . '"' . ',';
  }
  $keys = mb_substr($keys, 0, -1);
  $values = mb_substr($values, 0, -1);
  return SQL('insert into ' . $table . ' (' . $keys . ') values (' . $values . ')');
}

/**
 * テーブルの中の'key'列から$funcの演算記号で検索し、一致した項目の'updateKey'の項目を$updateValueに更新
 *
 * @param string $table 検索したいテーブル
 * @param string $key 検索したい列
 * @param * $value 見つけたい値
 * @param string $updateKey 更新したい列
 * @param * $updateValue 更新後の値
 * @param string $func 演算記号（=、<、>=、など）
 * @return void
 */
function SQLupdateEx($table, $updateKey, $updateValue, $key, $value, $func)
{
  $useValue = $value;
  if (is_string($value)) {
    $useValue = '"' . $value . '"';
  }
  $useUpdateValue = $updateValue;
  if (is_string($updateValue)) {
    $useUpdateValue = '"' . $updateValue . '"';
  }
  if (!$updateValue) {
    $useUpdateValue = 'null';
  }
  return SQL('update ' . $table . ' set ' . $updateKey . '=' . $useUpdateValue . ' where ' . $key . $func . $useValue);
}

/**
 * テーブルの中の'key'列から$valueを検索し、一致した項目の'updateKey'の項目を$updateValueに更新
 *
 * @param string $table 検索したいテーブル
 * @param string $key 検索したい列
 * @param * $value 見つけたい値
 * @param string $updateKey 更新したい列
 * @param * $updateValue 更新後の値
 * @return void
 */
function SQLupdate($table, $updateKey, $updateValue, $key, $value)
{
  return SQLupdateEx($table, $updateKey, $updateValue, $key, $value, '=');
}

/**
 * ## テーブルの中から複数条件で検索
 * ## 【注意】最初の一件のみ取得
 *
 * ### $arrayの中身
 * ```
 * $array = [
 *   [
 *     'key' => '検索したいキー',
 *     'value' => '検索したい文字列',
 *     'func' => '=' //演算記号
 *   ],
 *   [
 *     'key' => '検索したいキー',
 *     'value' => '検索したい文字列',
 *     'func' => '=' //演算記号
 *   ]
 * ]
 * ```
 *
 * @param string $table 検索したいテーブル
 * @param array $array 検索したい条件をまとめた配列
 * @return object 結果
 */
function SQLfindSome($table, $array)
{
  $words = 'select * from ' . $table . ' where ';
  foreach ($array as $obj) {
    $key = $obj['key'];
    $val = $obj['value'];
    $func = $obj['func'];
    if (is_string($val)) {
      $val = '"' . $val . '"';
    }
    $words = $words . $key . $func . ' ' . $val . ' and ';
  }
  $words = substr($words, 0, -4);
  return SQL($words);
}

/**
 * ## テーブルの中から複数条件で検索
 * ## 【注意】全件取得
 *
 * ### $arrayの中身
 * ```
 * $array = [
 *   [
 *     'key' => '検索したいキー',
 *     'value' => '検索したい文字列',
 *     'func' => '=' //演算記号
 *   ],
 *   [
 *     'key' => '検索したいキー',
 *     'value' => '検索したい文字列',
 *     'func' => '=' //演算記号
 *   ]
 * ]
 * ```
 *
 * @param string $table 検索したいテーブル
 * @param array $array 検索したい条件をまとめた配列
 * @return object 結果
 */
function SQLfindSomeAll($table, $array)
{
  $words = 'select * from ' . $table . ' where ';
  foreach ($array as $obj) {
    $key = $obj['key'];
    $val = $obj['value'];
    $func = $obj['func'];
    if (is_string($val)) {
      $val = '"' . $val . '"';
    }
    $words = $words . $key . $func . ' ' . $val . ' and ';
  }
  $words = substr($words, 0, -4);
  return SQLfetchAll($words);
}

/**
 * テーブルの中の'key'列から$funcの演算記号で検索する
 * ## 【注意】最初の一件のみ取得
 *
 * @param string $table 検索したいテーブル
 * @param string $key 検索したい列
 * @param * $value 見つけたい値
 * @param string $func 演算記号（=、<、>=、など）
 * @return void
 */
function SQLfindEx($table, $key, $value, $func)
{
  $useValue = $value;
  if (is_string($value)) {
    $useValue = '"' . $value . '"';
  }
  return SQL('select * from ' . $table . ' where ' . $key . $func . $useValue);
}

/**
 * テーブルの中の'key'列から$funcの演算記号で検索する
 * ## 【注意】全件取得
 *
 * @param string $table 検索したいテーブル
 * @param string $key 検索したい列
 * @param * $value 見つけたい値
 * @param string $func 演算記号（=、<、>=、など）
 * @return void
 */
function SQLfindExAll($table, $key, $value, $func)
{
  $useValue = $value;
  if (is_string($value)) {
    $useValue = '"' . $value . '"';
  }
  return SQLfetchAll('select * from ' . $table . ' where ' . $key . $func . $useValue);
}

/**
 * テーブルの中の'key'列から検索値'value'と完全一致するものを出す
 * ## 【注意】最初の一件のみ取得
 *
 * @param string $table 検索したいテーブル
 * @param string $key 検索したい列
 * @param * $value 見つけたい値
 * @return void
 */
function SQLfind($table, $key, $value)
{
  return SQLfindEx($table, $key, $value, '=');
}

/**
 * テーブルの中の'key'列から検索値'value'と完全一致するものを出す
 * ## 【注意】全件取得
 *
 * @param string $table 検索したいテーブル
 * @param string $key 検索したい列
 * @param * $value 見つけたい値
 * @return array
 */
function SQLfindAll($table, $key, $value)
{
  return SQLfindExAll($table, $key, $value, '=');
}

/**
 * テーブルの任意の列で未使用なダンダム英数字16文字以上を吐き出す
 *
 * @param [type] $table 使いたいテーブル
 * @param [type] $key IDを登録する列
 * @return string 未使用なID
 */
function SQLmakeRandomId($table, $key)
{
  $breakFlag = 0;
  do {
    $random = randomString(16) . time();
    $response = SQLfind($table, $key, $random);
    if (!$response) {
      $breakFlag = 1;
    }
  } while ($breakFlag == 0);
  return $random;
}

/**
 * 二つの表を比較し、baseTableをjoinTableで拡張した表を作成
 *
 * @param string $baseTable ベースにするテーブル
 * @param string $joinTable 拡張したい情報
 * @param string $baseKey baseTableのキー
 * @param string $joinKey 拡張テーブルのキー
 * @param string $where 命令後のwhere句以降（whereは勝手に入ります）
 * @return void
 */
function SQLjoin($baseTable, $joinTable, $baseKey, $joinKey, $where = null)
{
  $sql = 'select * from ' . $baseTable . ' left join ' . $joinTable . ' on ' . $baseKey . ' = ' . $joinKey;
  if ($where) {
    $sql = $sql . ' where ' . $where;
  }
  return SQL($sql);
}

/**
 * ## $tableから$keyが$valueに一致する項目を全て削除
 * 元から削除済みでもエラー出ません
 * ## 一致した項目全部消える注意！
 *
 * @param [type] $table 調べるテーブル
 * @param [type] $key 調べるキー（項目名）
 * @param [type] $value 調べる値
 * @return void
 */
function SQLdeleteFull($table, $key, $value)
{
  if (is_string($value)) {
    $value = '"' . $value . '"';
  }
  return SQL('delete from ' . $table . ' where ' . $key . ' = ' . $value);
}

/**
 * ## $tableから$keyが$valueに一致する項目を一件のみ削除
 * 元から削除済みでもエラー出ません
 *
 * @param [type] $table 調べるテーブル
 * @param [type] $key 調べるキー（項目名）
 * @param [type] $value 調べる値
 * @return void
 */
function SQLdelete($table, $key, $value)
{
  if (is_string($value)) {
    $value = '"' . $value . '"';
  }
  return SQL('delete from ' . $table . ' where ' . $key . ' = ' . $value . 'limit 1');
}

/**
 * ## テーブルの中から複数条件で削除
 * 元から削除済みでもエラー出ません
 *
 * ### $arrayの中身
 * ```
 * $array = [
 *   [
 *     'key' => '検索したいキー',
 *     'value' => '検索したい文字列',
 *     'func' => '=' //演算記号
 *   ],
 *   [
 *     'key' => '検索したいキー',
 *     'value' => '検索したい文字列',
 *     'func' => '=' //演算記号
 *   ]
 * ]
 * ```
 *
 * @param string $table 検索したいテーブル
 * @param array $array 検索したい条件をまとめた配列
 * @param [integer] $limit 最大削除数デフォルト1
 * @return object 結果
 */
function SQLdeleteSome($table, $array, $limit = 1)
{
  $words = 'delete from ' . $table . ' where ';
  foreach ($array as $obj) {
    $key = $obj['key'];
    $val = $obj['value'];
    $func = $obj['func'];
    if (is_string($val)) {
      $val = '"' . $val . '"';
    }
    $words = $words . $key . $func . ' ' . $val . ' and ';
  }
  $words = substr($words, 0, -4);
  $words = $words . ' limit ' . $limit;
  return SQL($words);
}

// ##############################
//
// ここから先はWebサイト固有の機能
//
// ##############################

/**
 * ## アカウント作成
 * @param string $userId ユーザー名
 * @param string $password パスワード
 * @param string $mailAddress メアド
 * @return string 既にアカウントがある場合はAlready、問題なく作れたら0
 */
function makeAccount($userId, $password, $mailAddress)
{
  $already = SQLfind('user_list', 'userId', $userId);
  if ($already) {
    //既にアカウントがある
    return 'Already';
  }
  $already = SQLfind('user_mail_list', 'mailAddress', $mailAddress);
  if ($already) {
    //既にアカウントがある
    return 'Already';
  }
  /** 未使用なランダムID */
  $secretId = SQLmakeRandomId('user_list', 'secretId');
  /** 現在のunixtime */
  $createdAt = time();
  /** アカウントステータス:未認証 */
  $status = 'uncertified';
  /** パスワード */
  $password = password_hash($password, PASSWORD_DEFAULT);
  /** メールアドレス */
  $mail = $mailAddress;

  SQLinsert('user_list', [
    'secretId' => $secretId,
    'userId' => $userId,
    'createdAt' => $createdAt,
    'status' => $status
  ]);

  SQLinsert('user_secret_list', [
    'secretId' => $secretId,
    'password' => $password,
    'otp' => null
  ]);

  SQLinsert('user_profile_list', [
    'secretId' => $secretId,
    'icon' => null,
    'coverImg' => null,
    'name' => null,
    'message' => null
  ]);
  SQLinsert('user_mail_list', [
    'secretId' => $secretId,
    'mailAddress' => $mail,
    'status' => 'Uncertified',
    'token' => '' . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9)
  ]);
  return 0;
}

/**
 * ユーザー用のアクセストークンを発行する
 *
 * @param string $id ユーザーID
 * @param string $password パスワード
 * @param string $otp ワンタイムトークン
 * @return void
 */
function createUserToken($id, $password, $otp)
{
  if (!$id || !$password || !$otp) {
    return false;
  }
  $secretId = idToSecretId($id);
  if (!$secretId) {
    return false;
  }
  $user = SQLfindSome('user_secret_list', [
    [
      'key' => 'secretId',
      'value' => $secretId,
      'func' => '='
    ]
  ]);
  if (!$user) {
    return false;
  }
  if (!password_verify($otp, $user['otp'])) {
    return false;
  }
  if (!password_verify($password, $user['password'])) {
    return false;
  }
  $token = randomString(64);
  $hashedToken = password_hash($token, PASSWORD_DEFAULT);
  SQLinsert('user_accesstoken_list', [
    'tokenId' => randomString(48) . time(),
    'token' => $hashedToken,
    'secretId' => $secretId,
    'createdAt' => time()
  ]);

  return $token;
}

/**
 * パスワードを新しくする（ログインできない人用）
 *
 * @param string $id ユーザーID
 * @param string $mailAddress メアド
 * @param string $otp ワンタイムトークン
 * @param string $newPassword 新規パスワード
 * @return bool
 */
function authTokenForResetPassword($id, $mailAddress, $otp, $newPassword)
{
  if (!$id || !$otp) {
    return false;
  }
  $secretId = idToSecretId($id);
  if (!$secretId) {
    return false;
  }
  $user = SQLfindSome('user_secret_list', [
    [
      'key' => 'secretId',
      'value' => $secretId,
      'func' => '='
    ]
  ]);
  if (!$user) {
    return false;
  }
  if (!password_verify($otp, $user['otp'])) {
    return false;
  }
  $user = SQLfindSome('user_mail_list', [
    [
      'key' => 'secretId',
      'value' => $secretId,
      'func' => '='
    ],
    [
      'key' => 'mailAddress',
      'value' => $mailAddress,
      'func' => '='
    ]
  ]);
  if (!$user) {
    return false;
  }
  $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
  SQLupdate('user_secret_list', 'password', $hashedPassword, 'secretId', $secretId);

  return true;
}

/**
 * ログイン用のワンタイムトークンを発行する
 *
 * @param string $id ユーザーID
 * @return void OTPまたは失敗したらfalse
 */
function requestOnetimeToken($id, $password)
{
  if (!$id) {
    return false;
  }
  $secretId = idToSecretId($id);
  if (!$secretId) {
    return false;
  }
  $user = SQLfind('user_secret_list', 'secretId', $secretId);

  if (!password_verify($password, $user['password'])) {
    return false;
  }
  $otp = randomOTP();
  $hashedOTP = password_hash($otp, PASSWORD_DEFAULT);
  SQLupdate('user_secret_list', 'otp', $hashedOTP, 'secretId', $secretId);
  return $otp;
}

/**
 * パスワードリセット用のワンタイムトークンを発行する
 *
 * @param string $id ユーザーID
 * @param string $mailAddress メアド
 * @return void OTPまたは失敗したらfalse
 */
function requestOnetimeTokenForgotPassword($id, $mailAddress)
{
  if (!$id) {
    return false;
  }
  $secretId = idToSecretId($id);
  if (!$secretId) {
    return false;
  }
  $user = SQLfindSome('user_mail_list', [
    [
      'key' => 'secretId',
      'value' => $secretId,
      'func' => '='
    ],
    [
      'key' => 'mailAddress',
      'value' => $mailAddress,
      'func' => '='
    ]
  ]);
  if (!$user) {
    return false;
  }
  $otp = randomString(64);
  $hashedOTP = password_hash($otp, PASSWORD_DEFAULT);
  SQLupdate('user_secret_list', 'otp', $hashedOTP, 'secretId', $secretId);
  return $otp;
}

/**
 * UserIdをSecretIdに変換
 *
 * @param string $id ユーザーID
 * @return String シークレットID
 */
function idToSecretId($id)
{
  if (!$id) {
    return false;
  }
  $sqlRes = SQLfind('user_list', 'userId', $id);
  if (!$sqlRes) {
    return false;
  }
  return $sqlRes['secretId'];
}

/**
 * SecretIdをUserIdに変換
 *
 * @param string $secretId シークレットID
 * @return String ユーザーID
 */
function secretIdToId($secretId)
{
  if (!$secretId) {
    return false;
  }
  $sqlRes = SQLfind('user_list', 'secretId', $secretId);
  if (!$sqlRes) {
    return false;
  }
  return $sqlRes['userId'];
}

/**
 * SecretIdからメールアドレスを特定
 *
 * @param string $secretId シークレットID
 * @return String メアド
 */
function secretIdToMailAddress($secretId)
{
  if (!$secretId) {
    return false;
  }
  $sqlRes = SQLfind('user_mail_list', 'secretId', $secretId);
  if (!$sqlRes) {
    return false;
  }
  return $sqlRes['mailAddress'];
}

/** 特定のユーザーのプロフィールをゲット */
function getProfile($id)
{
  return SQLfind('USER_PROFILE_VIEW', 'userId', $id);
}
