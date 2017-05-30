<?php
//必要な変数の定義

$host ='localhost';
$username = 'root';
$password = 'root';
$dbname ='BeerList';
$img_dir = './img/';

$err_msg    = [];
$data       = [];

//DNSの取得
$dsn = 'mysql:dbname='.$dbname.';host'.$host;
//データベース処理
try {
  //データベースに接続
  $dbh = new PDO($dsn, $username, $password);
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  try{
    $sql = 'SELECT
             *
            FROM
              user';

    //SQL文実行準備
    $stmt = $dbh->prepare($sql);
    //SQL文実行準備
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    //レコード取得
    $rows = $stmt->fetchAll();
    foreach($rows as $row){
      $data[] = $row;
    }
}catch (PDOException $e){
throw $e;
}
} catch (PDOException $e){
echo 'DB接続失敗：'.$e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>Beer Detail</title>
  <link rel="stylesheet" href="html5reset-1.6.1-2.css">
  <link rel="stylesheet" href="beerlist_users_history.css">
</head>
<body>
  <header>
    <div  class="top">
      <a class= "a_box" href = "beerlist-tool.php">
        <img class="main-logo" src="beer_icon.jpeg">
        <h1 class= "top_name" > Beer List 管理ページ </h1>
      </a>
    </div>
    <h1 class="signup_h">ユーザー 一覧表</h1>
  </header>
<main>
  <table class="item_table">
    <tr>
      <th>ユーザーID</th>
      <th>ユーザー名</th>
      <th>アドレス</th>
      <th>パスワード</th>
      <th>登録日</th>
    </tr>
    <!-- foreachで表示 -->
    <?php foreach ($data as $value) {?>
    <tr class="cart_ele">
      <!-- ユーザーID -->
      <td>
        <p><?php print $value['user_id'];?></p>
      </td>
      <!-- ユーザー名 -->
      <td>
        <p><?php print $value['user_name'];?>  </p>
      </td>
      <!-- アドレス -->
      <td>
        <p class="email"><?php print $value['email'];?></p>
      </td>
      <!-- パスワード -->
      <td>
        <p><?php print $value['password'];?>  </p>
      </td>
      <!-- 登録日 -->
      <td>
        <p><?php print $value['create_datetime'];?>  </p>
      </td>
    </tr>
      <?php }?>
    </table>
</main>
</body>
</html>
