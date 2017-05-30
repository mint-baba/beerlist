<?php
//必要な変数の定義

$host ='localhost';
$username = 'root';
$password = 'root';
$dbname ='BeerList';
$img_dir = './img/';

$err_msg    = [];
$data       = [];

$user_name = '';
$user_id = '';

//セッション処理
session_start();
if (isset($_SESSION['user_name']) === TRUE){
$user_name = $_SESSION['user_name'];
$user_id   = $_SESSION['user_id'];
} else{
  $user_name = 'ゲスト';
}

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
            history.create_datetime, history.amount,
            history.price,
            item_master.img, item_master.item_name
            FROM
              history
            JOIN item_master
            ON history.item_id = item_master.item_id
            WHERE history.user_id = ?';

    //SQL文実行準備
    $stmt = $dbh->prepare($sql);
    //プレースホルダーに値をバインド
    $stmt->bindValue(1,$user_id,PDO::PARAM_INT);
    // sql実行
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

// // ログアウト処理
// if($_POST['pro_kind'] === 'logout'){
// $session_name = session_name();
// // セッション変数を全て削除
// $_SESSION = array();
// // ユーザのCookieに保存されているセッションIDを削除
// if (isset($_COOKIE[$session_name])) {
//   setcookie($session_name, '', time() - 42000);
// }
// // セッションIDを無効化
// session_destroy();
// header('Location: beerlist_home.php');
// exit;
// }

?>


<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>Beer Detail</title>
  <link rel="stylesheet" href="html5reset-1.6.1-2.css">
  <link rel="stylesheet" href="beerlist_userhistory.css">
</head>
<body>
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

      <h1 class="signup_h">新規会員登録</h1>
      <p class="user"><?php print $user_name ;?>さんの購入履歴</p>

  <table class="item_table">
    <tr>
      <th>購入日時</th>
      <th>商品</th>
      <th>個数</th>
      <th>金額</th>
    </tr>
    <!-- foreachで表示 -->
    <?php foreach ($data as $value) {?>
    <tr class="cart_ele">
      <!-- 購入日時 -->
      <td>
        <p><?php print $value['create_datetime'];?></p>
      </td>
      <!-- 商品 -->
      <td>
        <img class="beer_img" src="<?php print $img_dir . $value['img'];?>">
        <p class="item_name"><?php print $value['item_name'];?></p>
      </td>
      <!-- 個数-->
      <td>
        <p><?php print $value['amount']?></p>
      </td>
      <!-- 合計金額 -->
      <td>
        <p><?php print $value['price'];?>  </p>
      </td>
    </tr>
      <?php }?>
    </table>
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
