<?php
//DB接続に必要な変数
$host ='localhost';
$username = 'root';
$password = 'root';
$dbname ='BeerList';
//MySQLのDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host;

//その他必要な変数の定義
$err_msg = [];
$rows = [];
$data = [];
$search_kind = '';

// バインド変数
$bind_one = '';
$bind_two = '';
$bind_three = '';
$bind_four = '';
$bind_five = '';
$bind_six = '';

// ビールの好み情報
$color = '';
$body = '';
$taste = '';

$img_dir = './img/';
// セッションからのデータ
$user_name = '';
$user_id = '';

// POSTで送られた値の変数代入
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // colorがPOSTされた場合
    if(isset($_POST['color']) === TRUE){
      $color = $_POST['color'];
    }
    // bodyがPOSTされた場合
    if(isset($_POST['body']) === TRUE){
      $body = $_POST['body'];
    }
    // tasteがPOSTされた場合
    if(isset($_POST['taste']) === TRUE){
      $taste = $_POST['taste'];
    }

  //radioボタンのチェックが1個の処理
    // colorのみの選択
    if($color !== '' && $body === '' && $taste === '') {
      // 検索項目数を確認する変数
      $search_kind = 'one';
      // バインドでプレースホルダーに入れる値
      $bind_one = 'color';
      $bind_two = $color;
    }

    // bodyのみの選択
    if($color === '' && $body !== '' && $taste === '') {
      // 検索項目数を確認する変数
      $search_kind = 'one';
      // バインドでプレースホルダーに入れる値
      $bind_one = 'body';
      $bind_two = $body;
    }

    // tasteのみの選択
    if($color === '' && $body === '' && $taste !== '') {
      // 検索項目数を確認する変数
      $search_kind = 'one';
      // バインドでプレースホルダーに入れる値
      $bind_one = 'taste';
      $bind_two = $taste;
    }

  //radioボタンのチェックが2つの処理
    //colorとbodyの場合
    if($color !== '' && $body !== '' && $taste === '') {
      // 検索項目数を確認する変数
      $search_kind = 'two';
      // バインドでプレースホルダーに入れる値
      $bind_one = 'color';
      $bind_two = $color;
      $bind_three = 'body';
      $bind_four = $body;
    }
    //colorとtasteの場合
    if($color !== '' && $body === '' && $taste !== '') {
      // 検索項目数を確認する変数
      $search_kind = 'two';
      // バインドでプレースホルダーに入れる値
      $bind_one = 'color';
      $bind_two = $color;
      $bind_three = 'taste';
      $bind_four = $taste;
    }
    //bodyとtasteの場合
    if($color === '' && $body !== '' && $taste !== '') {
      // 検索項目数を確認する変数
      $search_kind = 'two';
      // バインドでプレースホルダーに入れる値
      $bind_one = 'taste';
      $bind_two = $taste;
      $bind_three = 'body';
      $bind_four = $body;
    }

  //radioボタンのチェックが3つの処理
    //全ての項目が入力されている
    if($color !== '' && $body !== '' && $taste !== '') {
    // 検索項目数を確認する変数
    $search_kind = 'three';
    // バインドでプレースホルダーに入れる値
    $bind_one = 'color';
    $bind_two = $color;
    $bind_three = 'body';
    $bind_four = $body;
    $bind_five = 'taste';
    $bind_six = $taste;
  }
    // 項目が選択されていない場合
    if($color === '' && $body === '' && $taste === '') {
      $err_msg[] ='検索項目が選択されていません';
    }
}


try {
  // データベースに接続
  $dbh = new PDO($dsn, $username, $password);
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  // エラーがなくPOSTされたデータがある場合のDB処理
  if($_SERVER['REQUEST_METHOD'] === 'POST'){
    try{
      $sql = 'SELECT * FROM item_master
              WHERE 1';
              if($color !== ''){
                $sql .= ' AND color = :color';
              }
              if($body !== ''){
                $sql .= ' AND body = :body';
              }
              if($taste !== ''){
                $sql .= ' AND taste = :taste';
              }
      $stmt = $dbh->prepare($sql);
      // 値をバインド
      if($color !== ''){
        $stmt->bindValue('color',$color, PDO::PARAM_STR);
      }
      if($body !== ''){
        $stmt->bindValue('body',$body, PDO::PARAM_STR);
      }
      if($taste !== ''){
        $stmt->bindValue('taste',$taste, PDO::PARAM_STR);
      }
      // SQL実行
      $stmt->execute();
      // レコード取得
      $data = $stmt->fetchAll();

    } catch (PDOException $e){
      throw $e;
    }
  }
}catch (PDOException $e){
  echo $e->getMessage();
}




//       if($search_kind === 'one'){
// // 選択項目が1つのSQL文
//         $sql = 'SELECT * FROM item_master
//                 WHERE ? = ? ';
//         // SQLをプリペアして準備
//         $stmt = $dbh->prepare($sql);
//         // POSTデータが格納された変数をバインド
//         $stmt->bindValue(1,$bind_one, PDO::PARAM_STR);
//         $stmt->bindValue(2,$bind_two, PDO::PARAM_STR);
//         // SQL実行
//         $stmt->execute();
//         $rows = $stmt->fetchAll();
//         foreach($rows as $row){
//           //商品情報の取得
//           $data[]  = $row;
//         }
//       }else if ($search_kind === 'two'){
// // 選択項目が2つのSQL文
//         $sql = 'SELECT * FROM item_master
//                 WHERE ? = ?
//                 AND ? = ?';
//         // SQLをプリペアして準備
//         $stmt = $dbh->prepare($sql);
//         // POSTデータが格納された変数をバインド
//         $stmt->bindValue(1,$bind_one, PDO::PARAM_STR);
//         $stmt->bindValue(2,$bind_two, PDO::PARAM_STR);
//         $stmt->bindValue(3,$bind_three, PDO::PARAM_STR);
//         $stmt->bindValue(4,$bind_four, PDO::PARAM_STR);
//         // SQL実行
//         $stmt->execute();
//         $rows = $stmt->fetchAll();
//         foreach($rows as $row){
//           //商品情報の取得
//           $data[]  = $row;
//         }
//       }else if ($search_kind === 'three'){
// // 選択項目が3つのSQL文
//         $sql = 'SELECT * FROM item_master
//                 WHERE ? = ?
//                 AND ? = ?
//                 AND ? = ?';
//         // SQLをプリペアして準備
//         $stmt = $dbh->prepare($sql);
//         // POSTデータが格納された変数をバインド
//         $stmt->bindValue(1,$bind_one, PDO::PARAM_STR);
//         $stmt->bindValue(2,$bind_two, PDO::PARAM_STR);
//         $stmt->bindValue(3,$bind_three, PDO::PARAM_STR);
//         $stmt->bindValue(4,$bind_four, PDO::PARAM_STR);
//         $stmt->bindValue(5,$bind_five, PDO::PARAM_STR);
//         $stmt->bindValue(6,$bind_six, PDO::PARAM_STR);
//         // SQL実行
//         $stmt->execute();
//         $rows = $stmt->fetchAll();
//         foreach($rows as $row){
//           //商品情報の取得
//           $data[]  = $row;
//         }
//       }
// var_dump($bind_one);
// var_dump($bind_two);
// var_dump($search_kind);
// var_dump($bind_three);
// var_dump($bind_four);
// var_dump($color);
// var_dump($body);
// var_dump($data);
// var_dump($err_msg);
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>Beer List</title>
  <link rel="stylesheet" href="html5reset-1.6.1-2.css">
  <link rel="stylesheet" href="beerlist_search.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script>
  $(function() {
    $('input[type="radio"][name="color"]').change(function() {
      $('#beer_body').addClass(val);
    });
   });
  </script>
  <!-- <link rel="stylesheet" href="beerlist-top.css"> -->
</head>
<body>
  <!-- 一番上のロゴ部分 ここにログインボタンなどを入れる -->
<div class= "top_fixed">
  <div class="top">
    <img class="main-logo" src="beer_icon.lprg">
    <h1> Beer List </h1>
    <p  >ようこそ<?php print $user_name;?>さん！</p>
    <p>IDは<?php print $user_id ;?>です</p>
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
<!-- 中央の検索部分 重要部分！ -->
<div id = "form_box">
<!-- ビールの形の絵 -->
  <div class="beer_pict_box">
    <!-- 泡　部分 -->
    <div id="beer_cream"></div>
    <!-- ビールグラス部分 -->
    <!-- <div id="beer_body"></div> -->
    <!-- 下の足部分 -->
    <div class="beer_stand"></div>
    <!-- ビールグラス最下部分 -->
    <div class="beer_bottom"></div>
  </div>
  <!--  -->
  <!-- フォーム部分の枠 -->
  <div class="beer_form_box">
    <h3>〜 CHOOSE YOUR TASTE 〜</h3>
    <!-- フォーム開始 -->
    <form method="post">
<!-- color の箱 -->
      <div class="color-box">
        <!-- フォームの名前 -->
        <p class="white">color :</p>
        <!-- フォームボタン部分 1 -->
        <div class = "color_botton_box" >
          <input class= "radio" type = "radio" name="color" value="white">
          <p class="white col_p">White</p>
        </div>
        <!-- フォームボタン部分 2 -->
        <div class = "color_botton_box" >
          <input class= "radio" type = "radio" name="color" value="yellow">
          <p class="white col_p">Yellow</p>
        </div>
        <!-- フォームボタン部分 3 -->
        <div class = "color_botton_box" >
          <input class= "radio" type = "radio" name="color" value="orange">
          <p class="white col_p">Orange</p>
        </div>
        <div id="beer_body"></div>

        <!-- フォームボタン部分 4 -->
        <div class = "color_botton_box" >
          <input class= "radio" type = "radio" name="color" value="black">
          <p class="white col_p">Black</p>
        </div>
      </div>
<!-- body の箱 -->
      <div class="color-box">
        <!-- フォームの名前 -->
        <p class="white">Body :</p>
        <!-- body フォームボタン部分 1 -->
        <div class = "body_botton_box" >
          <input class= "radio" type = "radio" name="body" value="Full">
          <p class="white">Full</p>
        </div>
        <!-- bodyフォームボタン部分 2 -->
        <div class = "body_botton_box" >
          <input class= "radio" type = "radio" name="body" value="Medium">
          <p class="white medi">Medium</p>
        </div>
        <!-- bodyフォームボタン部分 3 -->
        <div class = "body_botton_box" >
          <input class= "radio" type = "radio" name="body" value="Light">
          <p class="white">Light</p>
        </div>
      </div>
<!-- taste の箱 -->
      <div class="color-box">
        <!-- フォームの名前 -->
        <p class="white">Taste :</p>
        <!-- taste フォームボタン部分 1 -->
        <div class = "taste_botton_box" >
          <input class= "radio" type = "radio" name="taste" value="Dry">
          <p class="white">Dry</p>
        </div>
        <!-- tasteフォームボタン部分 2 -->
        <div class = "taste_botton_box" >
          <input class= "radio" type = "radio" name="taste" value="Medium">
          <p class="white medi">Medium</p>
        </div>
        <!-- tasteフォームボタン部分 3 -->
        <div class = "taste_botton_box" >
          <input class= "radio" type = "radio" name="taste" value="Sweet">
          <p class="white">Sweet</p>
        </div>
      </div>
      <!-- 送信ボタン部分 -->
      <div class="sub">
        <input class="submit" type="submit" value="検索">
      </div>
    </form>
  </div>
</div>
<!-- 検索情報表示部分 -->
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
          <p class ="black beer_name"><?php print $value['item_name'];?></p>
          <p class ="black price"><?php print $value['price'];?></p>
        </a>
      <?php }?>
    </div>
  </div>
</div>
</main>
<div class="wave"></div>
<footer>

</footer>

</body>
</html>
