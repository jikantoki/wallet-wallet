<?php
require_once '../env.php'; //環境変数読み込み
require_once './settings.php'; //ルートディレクトリ読み込み
require_once DIR_ROOT . '/php/myAutoLoad.php'; //自動読み込み
$test = SQLcreateTable('tableAA', ['id' => 'text', 'name' => 'text', 'unixtime' => 'int']);
dump($test);
echo '<br><br><br><br><br>';
$test = SQLshowTable();
dump($test);
$test = SQLsearchTable('usersList');
dump($test);
if (!$test) {
  $test = SQL('create table usersList (id int, name varchar(10), col varchar(10))');
  dump($test);
}
$test = SQLshowTable();
dump($test);
