<?php
// 必要な変数の定義
$host ='localhost';
$username = 'root';
$password = 'root';
$dbname ='BeerList';

$err_msg = [];
$data = [];

$item_id = $_GET['id'];
$img_dir = './img/';

$user_name = '';
$user_id = '';
$amount = '';

//セッション処理
session_start();
if (isset($_SESSION['user_name']) === TRUE){
$user_name = $_SESSION['user_name'];
$user_id   = $_SESSION['user_id'];
} else{
  $user_name = 'ゲスト';
}


//MySQLのDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host;

//データベース接続
try{
  $dbh = new PDO($dsn, $username, $password);
  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
//GETで送られたIDから商品データを取得
  try{
    $sql = 'SELECT * FROM item_master WHERE item_id = ?;';
    //SQL文の準備
    $stmt = $dbh->prepare($sql);
    //プレースホルダーにIDをバインドする
    $stmt->bindValue(1,$item_id, PDO::PARAM_INT);
    //SQL文実行
    $stmt->execute();
    //レコード取得
    $data[] = $stmt->fetch();
  } catch (PDOEcption $e){
    throw $e;
  }
  //カートに入れられた時の処理
  // POSTでカートに入れられた時のエラーチェック
  // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  //   if(isset($_POST['amount']) === TRUE){
  //     $amount = $_POST['amount'];
  //     if($amount > $stock){
  //       $err_msg[] = '在庫数が足りません！';
  //     }
  //   }
  // }

  if(count($err_msg) === 0 && $_SERVER['REQUEST_METHOD'] === 'POST'){
  try{
    $amount = $_POST['amount'];
    $create_datetime = date('Y-m-d H:i:s');
    $sql = 'INSERT INTO cart (user_id, item_id, amount, create_datetime) VALUES(?,?,?,?)';
    //SQL文の実行雨準備
    $stmt = $dbh->prepare($sql);
    //SQL文のプレースホルダーに値をバインド
    $stmt->bindValue(1, $user_id,PDO::PARAM_INT);
    $stmt->bindValue(2, $item_id,PDO::PARAM_INT);
    $stmt->bindValue(3, $amount,PDO::PARAM_INT);
    $stmt->bindValue(4, $create_datetime,PDO::PARAM_INT);
    // SQL文の実行
    $stmt->execute();
  } catch (PDOException $e){
    throw $e;
  }
}else {
  foreach($err_msg as $value){
    print 'エラー発生：'.$value;
    var_dump($stock);
    var_dump($amount);
  }
}

} catch (PDOException $e){
  //接続失敗の場合
  echo 'DB処理でエラー発生：'.$e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>Beer Detail</title>
  <link rel="stylesheet" href="html5reset-1.6.1-2.css">
  <link rel="stylesheet" href="beerlist_detail.css">
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
<!-- 中央部分のコンテナ -->
<div class="container">
  <!-- ビール情報表示ボックス -->
  <div class="beer_detail">
    <!-- 画像と商品情報をフレックスで横並びにする -->
    <!-- 画像の箱 -->
    <div class="img_box">
      <!-- foreachで商品情報を表示 -->
    <?php foreach($data as $value) {?>
      <img class="beer_img" src="<?php print $img_dir . $value['img'];?>">
    </div>
    <!-- 商品情報の箱 -->
    <div class="detail_box">
      <h2 class="item_name"><?php print $value['item_name'];?></h2>
      <div class="item_box font20">
        <h3 class="category">カテゴリー  : <?php print $value['category'];?></h3>
        <h3 class="taste font20">味  : <?php print $value['taste'];?></h3>
        <h3 class="body font20">重さ  : <?php print $value['body'];?></h3>
        <h3 class="color font20">カラー  : <?php print $value['color'];?></h3>
        <h3 class="srock font20">在庫数  : <?php print $value['stock'];?></h3>
        <h3 class="price font20"><?php print $value['price'];?>円/1本</h3>
      </div>
      <!-- Formボタンの箱　フレックスする -->
      <div class="form_box">
        <!-- 在庫が0の場合は表示しないで売り切れを表示 -->
        <?php if($value['stock'] !== 0){?>
          <form class="amount_b" method = "post">
            <!-- 注文数のフォーム -->
            <select name ="amount">
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5">5</option>
              <option value="6">6</option>
              <option value="7">7</option>
              <option value="8">8</option>
              <option value="9">9</option></select>
              <!-- hiddenでitem_idを送る -->
              <input type="hidden" name="item_id" value="<?php print $value['item_id'];?>">
              <!-- カートに入れるボタン -->
              <input class ="cart_b" type="submit" value="カートに入れる">
          </form>
        <?php }else{ ?>
          <p class="sold_out"><?php print '売り切れ';?></p>
        <?php }?>
      </div>
      <div class="detail">
        <h3>商品詳細</h3>
        <h4><?php print $value['detail'];?></h4>
      </div>
    <?php }?>
    </div>
  </div>
  <!-- ＊ビール詳細情報終わり -->
</div>
<!-- ＊コンテナ終わり -->

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
