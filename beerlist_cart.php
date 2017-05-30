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
$data = [];

$img_dir = './img/';

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

  // update処理
  if(isset($_POST['pro_kind']) && $_POST['pro_kind'] === 'update'){
    try{
      // POSTのamountとcart_idを変数化
      $amount = $_POST['amount'];
      $cart_id = $_POST['cart_id'];
      //cartのamountを更新するSQL文
      $sql = 'UPDATE cart SET amount = ? WHERE cart_id = ?';
      //SQL文を準備
      $stmt = $dbh->prepare($sql);
      //プレースホルダーに値をバインド
      $stmt->bindValue(1,$amount,PDO::PARAM_INT);
      $stmt->bindValue(2,$cart_id,PDO::PARAM_INT);
      //SQL実行
      $stmt->execute();
    }catch(PDOException $e){
      throw $e;
    }
  }
  // 削除ボタンを押した時
  if(isset($_POST['pro_kind']) && $_POST['pro_kind'] === 'delete'){
    try{
      //POSTで受け取ったcart_idを代入
      $cart_id = $_POST['cart_id'];
      //指定したcart_idのテーブルを削除
      $sql = 'DELETE
              FROM cart
              WHERE cart_id = ?';
      //SQL文の実行準備
      $stmt = $dbh->prepare($sql);
      //プレースホルダーに値をバインド
      $stmt->bindValue(1,$cart_id,PDO::PARAM_INT);
      //SQL実行
      $stmt->execute();
    }catch(PDOException $e){
      throw $e;
    }
  }
  //カートを空にするのボタンを押した時の処理
  if(isset($_POST['pro_kind']) && $_POST['pro_kind'] === 'all_delete'){
    try{
      //POSTで受け取ったcart_idを代入
      $user_id = $_POST['user_id'];
      //指定したcart_idのテーブルを削除
      $sql = 'DELETE
              FROM cart
              WHERE user_id = ?';
      //SQL文の実行準備
      $stmt = $dbh->prepare($sql);
      //プレースホルダーに値をバインド
      $stmt->bindValue(1,$user_id,PDO::PARAM_INT);
      //SQL実行
      $stmt->execute();
    }catch(PDOException $e){
      throw $e;
    }
  }
// sessionのuser_idから使う情報を取得
// INNER JOINしたデータはWHEREで特定できる？
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
            }
    }catch (PDOException $e){
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
  <link rel="stylesheet" href="beerlist_cart.css">
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
<main>
<!-- 以下カート一覧表示部分 -->
<div class="list">
  <h2 class="white">カート一覧</h2>
  <h3 class="white"><?php print $user_name?>さんのカート</h3>
  <!-- foreachで商品一覧表示 -->
  <table class="item_table">
    <tr>
      <th>商品名</th>
      <th>数量</th>
      <th>価格</th>
      <th>変更</th>
      <th>削除</th>
    </tr>
    <!-- foreachで表示 -->
    <?php foreach ($data as $value) {?>
    <!-- $priceは1カートの総合金額を表す -->
    <?php $price = ($value['amount'] * $value['price']);?>
    <!-- 各priceの値を$sumに加算 -->
    <?php $sum += $price ;?>
    <tr class="cart_ele">
      <!-- 画像と商品名 -->
      <td>
        <img src="<?php print $img_dir . $value['img'];?>">
        <p class="item_name"><?php print $value['item_name'];?></p>
      </td>
      <!-- 数量変更フォーム -->
      <td>
        <form method = "POST">
          <!-- 注文数のフォーム -->
          <!-- 商品在庫が0の場合は売り切れ表示 -->
          <?php if($value['stock'] !== 0) {?>
          <select name ="amount">
            <option value="<?php print $value['amount'];?>"><?php print $value['amount'];?></option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <option value="8">8</option>
            <option value="9">9</option></select>
          <?php }else{?>
            <p class="sold_out"><?php print '売り切れ';?></p>
          <?php }?>
      </td>
      <!-- 価格 -->
      <td>
        <?php print $price;?>/ 円
      </td>
      <!-- 変更フォームボタン -->
      <!-- hiddenでpro_kindでupdate と cart_idを送りカートを特定して更新 -->
      <td>
        <?php if($value['stock'] !== 0) {?>
        <input type="hidden" name="cart_id" value="<?php print $value['cart_id'];?>">
        <input type="hidden" name="pro_kind" value="update">
        <input type="submit" value="変更">
      </form>
        <?php }else{?>
          <?php $err_msg[] = '売り切れ商品を削除してください'; ?>
          <p class="sold_out"><?php print '売り切れ';?></p>
        <?php }?>
      </td>
      <!-- カート削除ボタン -->
      <!-- hiddenでpro_kindでdelete と cart_idを送り削除命令 -->
      <td>
        <form method="post">
          <input type="hidden" name="cart_id" value="<?php print $value['cart_id'];?>">
          <input type="hidden" name="pro_kind" value="delete">
          <input type="submit" value="削除">
        </form>
      </td>
    </tr>
    <?php }?>
  </table>
</main>
  <div class="buy_box">
    <a href="beerlist_home.php">買い物を続ける</a>
    <!-- cartを空にするボタン -->
    <form class="all_delete" method="post">
      <input type="hidden" name="pro_kind" value="all_delete">
      <input type="hidden" name="user_id" value="<?php print $user_id;?>">
      <input type="submit" value="カートを空にする">
    </form>
    <!-- 配送日の選択、合計金額表示、購入ボタン -->
    <div class="buy_button">
      <!-- 配送日選択フォーム -->
      <?php if(count($err_msg) ===0) {?>
      <form action="beerlist_result.php" method="post" >
        <select name="send_day">
          <option value="1">1<option>
          <option value="2">2<option>
          <option value="3">3<option>
          <option value="4">4<option></select>
      <!-- 合計金額の表示 -->
      <p class = 'sum'>
        合計 <?php print $sum;?> 円
      </p>
      <!-- 購入ボタン　hiddenでpro_kind buyを送る -->
        <input type="hidden" name="pro_kind" value="buy">
        <input type="submit" value="購入する">
      </form>
      <?php }else{?>
        <p><?php print '売り切れのものが含まれています';?></p>
      <?php }?>
    </div>
  </div>
  <div class="wave"></div>

  <!-- フッタースタート -->
  <footer>
    <img class="stop"src="stop_logo.jpeg">
    <p class = "stop_p" > 飲酒は20歳を過ぎてから。飲酒運転は法律で禁止されていま す。<br>
        妊娠中や授乳時の飲酒は、胎児乳児に悪影響を与えるおそれがあります。<br>
       お酒は何よりも適量です。<br>
    </p>
  </footer>

</body>
</html>
