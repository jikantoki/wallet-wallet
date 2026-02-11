<?php
require_once DIR_ROOT . '/vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

const VAPID_SUBJECT = WebPush_URL;
const PUBLIC_KEY = VUE_APP_WebPush_PublicKey;
const PRIVATE_KEY = VUE_APP_WebPush_PrivateKey;

/**
 * ## プッシュ通知を送信する
 *
 * $actions使用例（最大二つのボタンを追加可能）
 * ```php
  $actions = [
    array(
      'action' => '/search?q=google',
      'title' => 'googleの検索結果を見る'
    )
  ]
 * ```
 *
 * @param string $endPoint
 * @param string $publickey
 * @param string $authToken
 * @param string $title 通知タイトル
 * @param string $message 通知の本文
 * @param string $image 通知に添付する画像URL
 * @param string $icon 通知に表示するアイコン（バッジとは別）
 * @param string $actions その他オプション
 * @return bool うまくいけばTrue、ダメならfalse
 */
function sendPush($endPoint, $publickey, $authToken, $title, $message = '', $image = '', $icon = null, $actions = [])
{
  if ($image !== '') {
    $image = $image;
  } else {
    $image = null;
  }

  // push通知認証用のデータ
  $subscription = Subscription::create([
    'endpoint' => $endPoint,
    'publicKey' => $publickey,
    'authToken' => $authToken,
  ]);

  // ブラウザに認証させる
  $auth = [
    'VAPID' => [
      'subject' => VAPID_SUBJECT,
      'publicKey' => PUBLIC_KEY,
      'privateKey' => PRIVATE_KEY,
    ]
  ];

  $webPush = new WebPush($auth);

  $report = $webPush->sendOneNotification(
    $subscription,
    json_encode(
      array(
        'title' => $title,
        'option' => array(
          'body' => $message,
          'image' => $image,
          'actions' => $actions,
          'icon' => $icon,
          'badge' => WebPush_icon
        )
      )
    )
  );

  $report->getRequest()->getUri()->__toString();
  if ($report->isSuccess()) {
    return true;
  } else {
    return false;
  }
}

/**
 * 特定のユーザーに通知を送信
 *
 * @param string $secretId
 * @param string $title
 * @param string $message
 * @param string $image
 * @param string $icon
 * @param array $actions
 * @return int 通知を送信した端末の数
 */
function sendPushForAccount($secretId, $title, $message = '', $image = '', $icon = null, $actions = [])
{
  $count = 0;
  $pushList = SQLfindAll('push_token_list', 'secretId', $secretId);
  foreach ($pushList as $push) {
    $endpoint = $push['push_endPoint'];
    $publicKey = $push['push_publicKey'];
    $authToken = $push['push_authToken'];
    $res = sendPush($endpoint, $publicKey, $authToken, $title, $message, $image, $icon, $actions);
    if ($res) {
      $count += 1;
    }
  }
  return $count;
}
