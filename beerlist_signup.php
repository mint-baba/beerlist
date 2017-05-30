<?php
//必要関数の定義
$host     = 'localhost';
$username = 'root';   // MySQLのユーザ名
$password = 'root';   // MySQLのパスワード
$dbname   = 'BeerList';   // MySQLのDB名

$user_name = '';
$email     = '';
$user_pass = '';
$err_msg   = [];
$data      = [];
$sign      = '';

//MySQLのDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host;

//POSTされた時の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  //＊フォームで送られた値のチェック
  // user_name チェック
  if(isset($_POST['user_name']) === TRUE){
    $user_name = $_POST['user_name'];
  }
  // user_name エラーチェック
  if(is_numeric($user_name) !== TRUE){
    if($user_name === ""){
      $err_msg[] = 'ユーザーネームの入力をしてください。';
    }
  }else {
    $err_msg[] = 'ユーザーネームは文字列でお願いします。';
  }

  // email チェック
  if(isset($_POST['email']) === TRUE){
    $email = $_POST['email'];
  }
  // email アドレスチェック
  $pattern = '/^[a-zA-Z0-9_.+-]+[@][a-zA-Z0-9.-]+$/';
  if (!preg_match($pattern, $email)){
    $err_msg[] = 'メールアドレスに誤りがあります。';
  }

  //user_pass チェック
  // 確認用のパスワードと同じ値なら処理を行う。
  if(isset($_POST['pass1']) === TRUE && isset($_POST['pass2']) === TRUE && $_POST['pass1'] === $_POST['pass2']){
      $user_pass = $_POST['pass1'];
      //空文字チェック
      if($user_pass === ''){
        $err_msg[] = 'パスワードの入力をお願いします。';
      }
  } else{
    $err_msg[] = 'パスワードに誤りがあります。';
  }
}
// ＊エラーチェック終わり！！

try{
//DB接続
$dbh = new PDO($dsn, $username, $password);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
//エラーがなくPOSTされたデータがある場合のDB処理
if(count($err_msg) === 0 && $_SERVER['REQUEST_METHOD'] === 'POST'){

  // sql文の作成
  try{
    //$user_name 　$email  　$user_passをDBに入れる
    $create_datetime = date('Y-m-d H:i:s');
    $sql = 'INSERT INTO user (user_name, email, password, create_datetime) VALUES(?,?,?,?)';
    // SQL文をプリペアで準備
    $stmt = $dbh->prepare($sql);
    //SQLのプレースホルダーに値をバインド
    $stmt->bindValue(1, $user_name,PDO::PARAM_STR);
    $stmt->bindValue(2, $email,PDO::PARAM_STR);
    $stmt->bindValue(3, $user_pass,PDO::PARAM_STR);
    $stmt->bindValue(4, $create_datetime,PDO::PARAM_STR);
    //SQL実行
    $stmt->execute();
    $sign = '登録が完了しました';
  } catch(PDOException $e){
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
  <meta charset = "UTF-8">
  <title>Beer List 新規登録</title>
  <link rel="stylesheet" href="html5reset-1.6.1.css">
  <link rel="stylesheet" href="beerlist_signup.css">
</head>
<body>
<header>
  <div  class="top">
    <a class= "a_box" href = "beerlist_home.php">
      <img class="main-logo" src="beer_icon.jpeg">
      <h1 class= "top_name" > Beer List </h1>
    </a>
  </div>
    <h1 class="signup_h">新規会員登録</h1>
    <p class="signup_p" >以下のフォームに必要事項をご入力ください。</p>
    <?php foreach ($err_msg as $value){?>
    <p><?php print $value;?></p>
    <?php }?>
</header>
<!-- フォーム部分 -->
  <div class="form_box">
    <p class="form_title white">ユーザーネーム/メールアドレス/パスワード</p>
    <div class="form_main">
      <form method="post">
        <!-- user_name -->
        <p class="text form_name white">ネーム：<input type="text" name="user_name"></p>
        <p class="text form_mail white">アドレス：<input type="text" name="email"></p>
        <p class="text form_pass white">パスワード：<input type="text" name="pass1"></p>
        <p class="text form_pass2 white">パスワード（確認用）：<input type="text" name="pass2"></p>
        <input class="sub"  type="submit" value="登録">
      </form>
      <div class = 'sign'><?php if ($_SERVER['REQUEST_METHOD'] === 'POST'){ print $sign; }?></div>
    </div>
</div>
</body>
</html>
