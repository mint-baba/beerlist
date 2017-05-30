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
            history.history_id, history.amount,
            history.price,
            item_master.img, item_master.item_name,
            user.user_name
            FROM
              history
            JOIN item_master
            ON history.item_id = item_master.item_id
            JOIN user
            ON history.user_id = user.user_id';

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
  <link rel="stylesheet" href="beerlist_history.css">
</head>
<body>
  <header>
    <div  class="top">
      <a class= "a_box" href = "beerlist-tool.php">
        <img class="main-logo" src="beer_icon.jpeg">
        <h1 class= "top_name" > Beer List 管理ページ </h1>
      </a>
    </div>
    <h1 class="signup_h">購入履歴一覧表</h1>
  </header>
<main>
  <table class="item_table">
    <tr>
      <th>履歴ID</th>
      <th>ユーザー</th>
      <th>商品</th>
      <th>個数</th>
      <th>合計金額</th>
    </tr>
    <!-- foreachで表示 -->
    <?php foreach ($data as $value) {?>
    <tr class="cart_ele">
      <!-- 履歴ID -->
      <td>
        <p class="id"><?php print $value['history_id'];?></p>
      </td>
      <!-- ユーザー -->
      <td>
        <p class="user_name"><?php print $value['user_name'];?>  </p>
      </td>
      <!-- 画像と商品名 -->
      <td>
        <img class= "beer_img" src="<?php print $img_dir . $value['img'];?>">
        <p class="item_name"><?php print $value['item_name'];?></p>
      </td>
      <!-- 個数 -->
      <td>
        <p class="amount"><?php print $value['amount'];?>  </p>
      </td>
      <!-- 合計金額 -->
      <td>
        <p class="price"><?php print $value['price'];?>円</p>
      </td>
    </tr>
      <?php }?>
  </table>
</main>
</body>
</html>
