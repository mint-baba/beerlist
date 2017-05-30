<?php
// 必要な変数の定義
$host     = 'localhost';
$username = 'root';   // MySQLのユーザ名
$password = 'root';   // MySQLのパスワード
$dbname   = 'BeerList';   // MySQLのDB名

$user_name = '';
$pass = '';
$err_msg = [];
$data    = [];
$message = '';

//MysqlのDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host;

//POSTされた時の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  //POSTの値を変数に代入
  //user_name
  if(isset($_POST['user_name']) === TRUE){
    $user_name = $_POST['user_name'];
  }
  //pass
  if(isset($_POST['pass']) === TRUE){
    $pass = $_POST['pass'];
  }
  //エラーチェック
  //user_name
  if ($user_name === ""){
    $err_msg[] = 'ユーザーネームを入力してください';
  }
  //pass
  if ($pass === ""){
    $err_msg[] = 'パスワードを入力してください';
  }
}

try{
  //データベース接続
  $dbh = new PDO($dsn, $username, $password);
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //エラーがなければDB処理を開始
  if (count($err_msg) === 0 && $_SERVER['REQUEST_METHOD'] === 'POST'){
    try{
      //ユーザーネームとパスワードからユーザーのテーブルを取得する
      $sql = 'SELECT * FROM user WHERE user_name = ? AND password = ?;';
      //SQL実行準備
      $stmt = $dbh->prepare($sql);
      //プレースホルダーに値をバインド
      $stmt->bindValue(1, $user_name, PDO::PARAM_STR);
      $stmt->bindValue(2, $pass, PDO::PARAM_STR);
      //SQL文実行
      $stmt->execute();
      $data = $stmt->fetch();
      //ユーザーデータが存在する場合はセッション開始
      if(count($data) !== 0){
        session_start();
        $_SESSION['user_id'] = $data['user_id'];
        $_SESSION['user_name'] = $data['user_name'];
        header('Location: beerlist_home.php');
        exit();
      } else {
        $message = 'ユーザーネームまたはパスワードに誤りがあります';
      }
    } catch (PDOException $e) {
      throw $e;
    }
  }
} catch (PDOException $e){
  echo $e->getMessage();
}



?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ログイン</title>
  <link rel="stylesheet" href="html5reset-1.6.1.css">
  <link rel="stylesheet" href="beerlist_login.css">
</head>
<body>
<header>
  <div  class="top">
    <a class= "a_box" href = "beerlist_home.php">
      <img class="main-logo" src="beer_icon.jpeg">
      <h1 class= "top_name" > Beer List </h1>
    </a>
  </div>
  <h1 class="signup_h">ログイン</h1>
  <p class="signup_p" >以下のフォームに必要事項をご入力ください。</p>
</header>
<!-- フォーム部分 -->
  <div class="form_box">
    <!-- ジョッキ取っ手部分 -->
    <div class="handdle"></div>
    <!-- 取っ手終わり -->
    <!-- ビール本体部分 -->
    <div class="beer_style">
      <p class="form_title white">ユーザーネーム / パスワードを入力してください</p>
      <!-- 入力フォーム -->
      <div class="form_main">
        <!-- エラーメッセージ表示 -->
        <p class="erro"><?php print $message ;?><p>
        <?php foreach ($err_msg as $value) {?>
          <p class="erro"><?php print $value ;?></p>
        <?php }?>
        <form method="post">
          <p class="text form_name white">ユーザーネーム：<input type="text" name="user_name"></p>
          <p class="text form_pass white">パスワード：<input type="text" name="pass"></p>
          <input type="submit" value="ログイン">
        </form>
        <a class="signin_a" href="beerlist_signup.php" >新規会員登録</a>
        <!-- フォーム終わり -->
      </div>
    </div>
  </div>

</body>
</html>
