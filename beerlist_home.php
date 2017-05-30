<?php
//必要な変数の定義
$host ='localhost';
$username = 'root';
$password = 'root';
$dbname ='BeerList';

$err_msg = [];
$data = [];

$img_dir = './img/';

$user_name = '';
$user_id = '';

//MySQLのDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host;

//データベース接続
try{
  $dbh = new PDO($dsn, $username, $password);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  //item_masterからitem_id,img,price,item_nameを取得
  try{
    $sql = 'SELECT item_id,img,price,item_name FROM item_master ';
    //SQL文の実行準備
    $stmt = $dbh->prepare($sql);
    //SQL実行
    $stmt->execute();
    $rows = $stmt->fetchAll();
    foreach($rows as $row){
      //商品ID、価格、商品名、画像を格納
      $data[] = $row;
    }
  } catch (PDOException $e){
    throw $e;
  }
} catch (PDOException $e){
  echo $e->getMessage();
}

//セッション処理
session_start();
if (isset($_SESSION['user_name']) === TRUE){
$user_name = $_SESSION['user_name'];
$user_id   = $_SESSION['user_id'];
} else{
  $user_name = 'ゲスト';
}
// ログアウト処理
if($_POST['pro_kind'] === 'logout'){
$session_name = session_name();
// セッション変数を全て削除
$_SESSION = array();
// ユーザのCookieに保存されているセッションIDを削除
if (isset($_COOKIE[$session_name])) {
  setcookie($session_name, '', time() - 42000);
}
// セッションIDを無効化
session_destroy();
header('Location: beerlist_home.php');
exit;
}

?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>Beer List</title>
  <link rel="stylesheet" href="html5reset-1.6.1-2.css">
  <link rel="stylesheet" href="beerlist-top.css">
</head>
<body>
  <!-- 一番上のロゴ部分 ここにログインボタンなどを入れる -->
  <div class="top_fixed">
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
  </div>
<main>
<!-- 画像がスライドする場所 -->
<!-- <div id="main_img_box"> -->
  <div><img id="main_img" src="image_1.jpeg"></div>
<div class="container">
  <!-- 中央部分の黄色の箱 -->
  <div class="beer_box">
    <h2>BEERS LIST</h2>
    <!-- foreach でビール情報を展開 -->
    <div class="detail_box">
      <?php foreach($data as $value) {?>
        <!-- aタグでitem_idを飛ばす -->
        <a class="detail_botton" href="beerlist_detail.php?id=<?php print $value['item_id'];?>">
          <img class="beer_img" src="<?php print $img_dir . $value['img'];?>">
          <p id ="black" class="beer_name"><?php print $value['item_name'];?></p>
          <p id ="black"  class="price"><?php print $value['price'];?></p>
        </a>
      <?php }?>
    </div>
  </div>
</div>
</main>
<div class="wave"></div>
<footer>
  <img class="stop"src="stop_logo.jpeg">
  <p class = "stop_p" > 飲酒は20歳を過ぎてから。飲酒運転は法律で禁止されていま す。<br>
      妊娠中や授乳時の飲酒は、胎児乳児に悪影響を与えるおそれがあります。<br>
     お酒は何よりも適量です。<br>
  </p>
</footer>
</body>
</html>
