<?php

// 必要変数の定義
$host ='localhost';
$username = 'root';
$password = 'root';
$dbname ='BeerList';

$user_name = '';
$user_id = '';
$amount = '';
$price = 0;
$sum = 0;
$err_msg = [];	//エラーメッセージ
$views = [];
$data = [];
$rows = [];
$send_day = 0;

$img_dir = './img/';

//POSTで値が送られていたら代入
if(isset($_POST['pro_kind']) === TRUE && $_POST['pro_kind'] === 'buy'){
  $send_day = $_POST['pro_kind'];
}
// MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host;

//セッション処理
session_start();
if (isset($_SESSION['user_name']) === TRUE){
$user_name = $_SESSION['user_name'];
$user_id   = $_SESSION['user_id'];
} else{
  $user_name = 'ゲスト';
}

//データベース接続
try{
  $dbh = new PDO($dsn, $username, $password);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

// cartに入っているデータの表示
  if($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_SESSION['user_name']) === TRUE){
    try{
    $sql = 'SELECT
            item_master.img, item_master.item_name, item_master.price, item_master.item_id, item_master.stock,
            cart.amount, cart.cart_id
            FROM
              cart
            JOIN item_master
            ON cart.item_id = item_master.item_id
            WHERE cart.user_id = ?';
            //SQL文実行準備
            $stmt = $dbh->prepare($sql);
            //プレースホルダーに値をバインド
            $stmt->bindValue(1,$user_id,PDO::PARAM_INT);
            //SQL文実行
            $stmt->execute();
            //レコード取得
            $rows = $stmt->fetchAll();
            foreach($rows as $row){
              $data[] = $row;
              $views[] = $row;
            }

    }catch (PDOException $e){
      throw $e;
    }
    }
    // トランザクションでhistoryにインサート、cartを削除
    if($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_SESSION['user_name']) === TRUE){
        // トランザクション開始
        $dbh->beginTransaction();
        try{
          foreach($data as $value){
            $sql = 'INSERT INTO
                      history(item_id,user_id,amount,price,send_date)
                    VALUES(?,?,?,?,?)';
            // プリペアして値をバインド
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $value['item_id'],PDO::PARAM_INT);
            $stmt->bindValue(2, $user_id,PDO::PARAM_INT);
            $stmt->bindValue(3, $value['amount'],PDO::PARAM_INT);
            $stmt->bindValue(4, $value['price'],PDO::PARAM_INT);
            $stmt->bindValue(5, $send_day,PDO::PARAM_INT);
            // SQL文実行
            $stmt->execute();
          }
            // cartのデータを削除
            $sql ='DELETE
                  FROM
                    cart
                  WHERE
                  user_id = ?';
            // プリペアして値をバインド
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1,$user_id,PDO::PARAM_INT);
            // SQL実行
            $stmt->execute();
            //二つの命令を実行
            $dbh->commit();
        } catch (PDOException $e) {
          //ロールバック処理
          $dbh->rollback();
          print $e->getMessage();
          throw $e;
        }
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
  <link rel="stylesheet" href="beerlist_result.css">
</head>
<body>
  <!-- 一番上のラグインなどのページに移行できるボタン -->
  <div class="top">
    <a class ="beerlist_a"  href = "beerlist_home.php">
    <img class="main-logo" src="beer_icon.jpeg">
    <h1 class= "top_name" > Beer List </h1>
    </a>
    <div class="hello">
      <p>ようこそ<?php print $user_name;?>さん！</p>
      <p>IDは<?php print $user_id ;?>です</p>
    </div>
    <a class="login_b" href="beerlist_login.php">ログイン</a>
    <a class="signup_b" href="beerlist_signup.php">新規登録</a>
    <a class = "history_b" href="beerlist_userhistory.php">購入履歴</a>
    <form method="post">
      <input type="hidden" name="pro_kind" value="logout">
      <input class="logout"  type="submit" value="ログアウト">
    </form>
  </div>
  <!-- 動くヘッダー部分 -->
  <header>
    <div class="botton_box">
      <a class="botton right right_line" href="beerlist_search.php">
        <img class= "icon" src="./Bottle-icon.png" ><p class = "a_name">ビール検索</p>
      </a>
      <a class="botton left right_line" href="">
        <img class= "icon" src="./Bottle-icon.png" ><p class = "a_name">グッズ</p>
      </a>
      <a class="botton left right_line" href="">
        <img class= "icon" src="./Bottle-icon.png"><p class = "a_name">その他</p>
      </a>
      <a class="cart_line" href="beerlist_cart.php">
        <img class="cart_img" src="./cart_img.png">
        <p class="cart">カートを見る</p>
      </a>
    </div>
  </header>
<!-- 以下カート一覧表示部分 -->
<main>
<div class="list">
  <div class="h_box">
    <h2 class="white">ご購入ありがとうございます！</h2>
    <h3 class="white"><?php print $user_name;?>さんのカート</h3>
  </div>
  <!-- foreachで商品一覧表示 -->
  <table class="item_table">
    <tr>
      <th>購入結果 一覧</th>
    </tr>
    <!-- foreachで表示 -->
    <?php foreach ($views as $view) {?>
    <!-- $priceは1カートの総合金額を表す -->
    <?php $price = ($view['amount'] * $view['price']);?>
    <!-- 各priceの値を$sumに加算 -->
    <?php $sum += $price ;?>
    <tr class="cart_ele">
      <!-- 画像と商品名 -->
      <td>
        <img class="beer_img"src="<?php print $img_dir . $view['img'];?>">
        <p class="item_name"><?php print $view['item_name'];?></p>
      </td>
      <!-- 注文本数 -->
      <td>
        <p class="amount"><?php print $view['amount'];?>本</p>
        <p class="item_price">(<?php print $view['price'];?>/本)</p>
      </td>
      <!-- 総額 -->
      <td>
        <p class="item_sum"><?php print $price ;?></p>
      </td>
    </tr>
    <?php }?>
    <!-- 配送日と合計金額表示 -->
    <tr>
      <td>
        <p class="send_day">配送日：<?php print $send_day;?></p>
        <p class="all_sum">合計 <?php print $sum;?> 円</p>
      </td>
    </tr>
  </table>
</div>
</main>
    <!-- ホームに戻るボタン -->
<a href="beerlist_home.php">ホームに戻る</a>
    <!-- footer -->
    <div class="wave"></div>

    <!-- フッタースタート -->
    <footer>
      <img class="stop"src="stop_logo.jpeg">
      <p class = "stop_p" > 飲酒は20歳を過ぎてから。飲酒運転は法律で禁止されていま す。<br>
          妊娠中や授乳時の飲酒は、胎児乳児に悪影響を与えるおそれがあります。<br>
         お酒は何よりも適量です。<br>
      </p>
    </footer>

<!-- </footer> -->
</body>
</html>
