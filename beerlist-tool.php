<?php
//必要な変数の定義

$host ='localhost';
$username = 'root';
$password = 'root';
$dbname ='BeerList';

$item_name  = "";
$price      = "";
$status     = "";
$detail     = "";
$category   = "";
$beer_stock = "";
$taste      = "";
$body       = "";
$color      = "";
$process_kind = "";
$err_msg    = [];
$data       = [];


$img_dir    ='./img/';
$new_img_filename = '';


//DNSの取得
$dsn = 'mysql:dbname='.$dbname.';host'.$host;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 新規追加の認識
  if(isset($_POST['process_kind']) === TRUE){
    $process_kind = $_POST['process_kind'];
  }
  if($process_kind === "insert_item"){
    //POSTでファイルがアップロードされたかのチェック
    if(is_uploaded_file($_FILES['new_img']['tmp_name']) === TRUE){
      //画像の拡張子を取得
      $extension = pathinfo($_FILES['new_img']['name'],PATHINFO_EXTENSION);
      //指定の拡張子かのチェック
      if ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png'){
        //保存する新しいファイル名の生成（ユニーク値を設定）
        $new_img_filename = sha1(uniqid(mt_rand(),true)).'.'.$extension;
        //同名ファイルの存在するかのチェック
        if (is_file($img_dir . $new_img_filename) !== TRUE){
          //アップロードされたファイルを指定のディレクトリへ移動して保存
          if (move_uploaded_file($_FILES['new_img']['tmp_name'],$img_dir . $new_img_filename) !== TRUE){
              $err_msg[] = 'ファイルのアップロードに失敗しました。';
          }
        } else {
          $err_msg[] = 'ファイルアップロードに失敗しました。再度お試しください。';
        }
      } else {
        $err_msg[] = 'ファイル形式が異なります。画像ファイルはJPEGまたはPNGでお願いします。';
      }
    } else {
      $err_msg[] = 'ファイルを選択してください。';
    }
    // POSTの値を変数へ代入
    // 商品名受け取り
    if(isset($_POST["item_name"]) === TRUE){
      $item_name = trim(mb_convert_kana($_POST["item_name"],"s"));
    }
    // 価格受け取り
    if(isset($_POST['price']) === TRUE){
      $price = trim(mb_convert_kana($_POST['price'],"ns"));
    }
    //ステータス受け取り
    if(isset($_POST['status']) === TRUE){
      $status = $_POST['status'];
    }
    // 説明受け取り
    if(isset($_POST['detail']) === TRUE){
      $detail = $_POST['detail'];
    }
    // カテゴリー受け取り
    if(isset($_POST['category']) === TRUE){
      $category = $_POST['category'];
    }
    //  在庫数受け取り
    if(isset($_POST['stock']) === TRUE){
      $stock = trim(mb_convert_kana($_POST['stock'], "ns"));
    }
    // 味 受け取り
    if(isset($_POST['taste']) === TRUE){
      $taste = $_POST['taste'];
    }
    // 重さ 受け取り
    if(isset($_POST['body']) === TRUE){
      $body = $_POST['body'];
    }
    //  色 受け取り
    if(isset($_POST['color']) === TRUE){
      $color = $_POST['color'];
    }
    // POSTで送られた値のエラーチェック
    // 名前文字列がからでないかのチェック
    if(is_numeric($item_name) !== TRUE){
      if($item_name === ""){
        $err_msg[] = "商品名を入力してください。";
      }
    }else{
      $err_msg[] = '商品名は文字列でお願いします。';
    }
    // 以下エラーチェック（insert_item処理）


    // 更新値の処理スタート
  } else if ($process_kind === "update_status"){
    // 更新価格の受け取り
    if(isset($_POST['price']) === TRUE){
      $price = trim(mb_convert_kana($_POST['price'],"ns"));
    }
    //更新ステータス受け取り
    if(isset($_POST['status']) === TRUE){
      $status = $_POST['status'];
    }
    // 更新説明受け取り
    if(isset($_POST['detail']) === TRUE){
      $detail = $_POST['detail'];
    }
    // 更新カテゴリー受け取り
    if(isset($_POST['category']) === TRUE){
      $category = $_POST['category'];
    }
    //  更新在庫数受け取り
    if(isset($_POST['stock']) === TRUE){
      $stock = trim(mb_convert_kana($_POST['stock'], "ns"));
    }
    // 更新味 受け取り
    if(isset($_POST['taste']) === TRUE){
      $taste = $_POST['taste'];
    }
    // 更新重さ 受け取り
    if(isset($_POST['body']) === TRUE){
      $body = $_POST['body'];
    }
    //  更新色 受け取り
    if(isset($_POST['color']) === TRUE){
      $color = $_POST['color'];
    }
    // IDの受け取り
    if(isset($_POST['item_id']) === TRUE){
      $item_id = $_POST['item_id'];
    }
  }
  //価格が数字で0よりも大きいかのチェック
  $pattern = '/^[1-9][0-9]*$/';
  if(!preg_match($pattern, $price) ){
    $err_msg[] = '価格は0よりも大きい整数でお願いします';
  }
  //在庫数が0よりも大きいかのチェック
  if(!preg_match($pattern, $stock) ){
    $err_msg[] = '在庫数は0よりも大きい整数でお願いします。';
  }
  //ステータスのチェック
  $pattern = '/^[01]$/';
  if(!preg_match($pattern, $status) ){
    $err_msg[] = '公開ステータスに誤りがあります。';
  }
}


//データベース処理
try {
  //データベースに接続
  $dbh = new PDO($dsn, $username, $password);
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //エラーがない場合POSTされた値をデータベースで処理
  if(count($err_msg) === 0 && $_SERVER['REQUEST_METHOD'] === 'POST'){
    // 商品追加の処理
    if($process_kind === 'insert_item'){
      //例外処理
      try{
        //11項目をインサート
        $create_datetime = date('Y-m-d H:i:s');
        $update_datetime = date('Y-m-d H:i:s');

        //SQL文の作成
        $sql = 'INSERT INTO item_master (item_name, price, img, status, detail, category, stock, taste, body, color, create_datetime, update_datetime) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)';
        // SQL文の実行準備
        $stmt = $dbh->prepare($sql);
        //プレースホルダーに値をバインド
        $stmt->bindValue( 1,$item_name, PDO::PARAM_STR);
        $stmt->bindValue( 2,$price, PDO::PARAM_INT);
        $stmt->bindValue( 3,$new_img_filename, PDO::PARAM_STR);
        $stmt->bindValue( 4,$status, PDO::PARAM_INT);
        $stmt->bindValue( 5,$detail, PDO::PARAM_STR);
        $stmt->bindValue( 6,$category, PDO::PARAM_STR);
        $stmt->bindValue( 7,$stock, PDO::PARAM_STR);
        $stmt->bindValue( 8,$taste, PDO::PARAM_STR);
        $stmt->bindValue( 9,$body, PDO::PARAM_STR);
        $stmt->bindValue( 10,$color, PDO::PARAM_STR);
        $stmt->bindValue( 11,$create_datetime, PDO::PARAM_STR);
        $stmt->bindValue( 12,$update_datetime, PDO::PARAM_STR);

        //SQLの実行
        $stmt->execute();
      }catch (PDOException $e){
        echo $e->getMessage();
      }
    }
    // 商品情報更新処理
    if($process_kind === "update_status"){
      // 例外処理
      try{
        // 更新のSQL文
        $sql = 'UPDATE item_master SET price = ?, status = ?, detail = ?, category = ?, stock = ?, taste = ?, body = ?, color = ? WHERE item_id = ?';
        //SQL実行準備
        $stmt = $dbh->prepare($sql);
        //プレースホルダーに値をバインド
        $stmt->bindValue( 1, $price  , PDO::PARAM_INT);
        $stmt->bindValue( 2, $status  , PDO::PARAM_INT);
        $stmt->bindValue( 3, $detail  , PDO::PARAM_STR);
        $stmt->bindValue( 4, $category  , PDO::PARAM_STR);
        $stmt->bindValue( 5, $stock  , PDO::PARAM_INT);
        $stmt->bindValue( 6, $taste  , PDO::PARAM_STR);
        $stmt->bindValue( 7, $body  , PDO::PARAM_STR);
        $stmt->bindValue( 8, $color  , PDO::PARAM_STR);
        $stmt->bindValue( 9, $item_id  , PDO::PARAM_INT);
        //SQL実行
        $stmt->execute();
      }catch (PDOException $e) {
        echo $e->getMessage();
      }
    }
  }
}catch (PDOException $e){
  echo $e->getMessage();
}
//テーブル情報の取得
try{
  //item_masterのテーブルデータを配列で取得
  $sql = 'SELECT item_id, item_name, price, img, status, detail, category, stock, taste, body, color FROM item_master ';
  //SQL実行準備
  $stmt = $dbh->prepare($sql);
  //実行
  $stmt->execute();
  $rows = $stmt->fetchAll();
  foreach($rows as $row){
    $data[] = $row;
  }
}catch (PDOException $e){
  echo $e->getMessage();
}

?>

<!DOCTYPE html>
<head>
  <meta charset="utf-8">
  <title>BeerList商品管理ページ</title>
  <link rel="stylesheet" href="html5reset-1.6.1-2.css">
  <link rel="stylesheet" href="beerlist-tool.css">
</head>
<body>
<header>
   <div  class="top">
    <a class= "a_box" href = "beerlist-tool.php">
      <img class="main-logo" src="beer_icon.jpeg">
      <h1 class= "top_name" >Beer List 商品管理ページ</h1>
    </a>
  </div>
  <div class="a_box">
    <h1 class="signup_h">商品管理 一覧表</h1>
    <a href="beerlist_history.php">購入履歴画面</a>
    <a class= "his" href="beerlist_users_history.php">ユーザー登録履歴</a>
 </div>
</header>

<!-- フォームボタン部分 -->
<div class="botton">
  <form method = "post" enctype="multipart/form-data">
    <p class="please">追加する商品情報を入力</p>
    <div class="form_box">
      <div class="f_left">
        <label>商品名:<input type ="text" name ="item_name"></label><br>

        <label>値段:<input type ="text" name ="price"></label><br>

        <input type="file" name="new_img"><br>

        <label>ステータス: 公開<input type ="radio" name ="status" value=1> 非公開<input type ="radio" name ="status" value=0></label><br>

        <label>説明:<textarea name="detail" cols="40" rows="13" wrap="soft"></textarea></label><br>
      </div>
      <div class="f_right">
        <label>カテゴリー:
          <select name="category">
          <option value = "">選択してください</option>
          <option value = "ビール">ビール</option>
          <option value = "その他">その他</option>
        </select>
        </label><br><br>
        <label>個数:
          <input type ="text" name ="stock">
        </label><br>

        <label>味:
          <select name="taste">
          <option value="">選択してください</option>
          <option value = "Dry" >Dry</option>
          <option value = "Medium" >Medium</option>
          <option value = "Sweet" >Sweet</option></select>
        </label><br>

        <label>重さ:<select name="body">
          <option value="">選択してください</option>
          <option value = "Full" >Full</option>
          <option value = "Medium" >Medium</option>
          <option value = "Light" >Light</option></select>
        </label><br>

        <label>色:<select name="color">
          <option value="">選択してください</option>
          <option value = "white" >white</option>
          <option value = "yellow" >yellow</option>
          <option value = "orange" >orange</option>
          <option value = "black" >black</option></select>
        </label><br>
      </div>
    </div>
  <input type="hidden" name="process_kind" value="insert_item">
  <input class="sub_b" type="submit" value="追加">
</form>
</div>
<!-- 以下一覧表示部分 -->
<main>
<div class="list">
  <h2>商品一覧</h2>
  <!-- foreachで商品一覧表示 -->
  <table class="item_table">
    <tr>
      <th>画像</th>
      <th>商品名</th>
      <th>値段</th>
      <th>ステータス</th>
      <th>説明</th>
      <th>カテゴリー</th>
      <th>在庫</th>
      <th>味</th>
      <th>重さ</th>
      <th>色</th>
      <th>変更</th>
    </tr>
      <!-- foreachで表示 -->
      <?php foreach ($data as $value) {?>
      <tr class="<?php if($value['status'] === 0){ print "backGray";}?>">

        <!-- 画像 -->
        <td>
          <img class="beer_img" src="<?php print $img_dir . $value['img']; ?>">
        </td>
        <!-- 商品名 -->
        <td>
          <?php print $value['item_name'];?>
        </td>
        <!-- 値段 -->
        <td>
          <form method = "post">
            <input type="text" name="price" value="<?php print $value['price'];?>">円
        </td>
        <!-- ステータス -->
        <td>
          <input type="radio" name="status" value = 1 <?php
          if($value['status'] === 1){print "checked";}?>>公開<br>
          <input type="radio" name="status" value = 0 <?php
          if($value['status'] === 0){print "checked";}?>>非公開
        </td>
        <!-- 説明文 -->
        <td>
          <textarea name="detail" cols="40" rows="13" wrap="soft"><?php print $value['detail'];?></textarea>
          <!-- <input class="detail" type="text" name="detail" value="<?php print $value['detail'];?>"> -->
        </td>
        <!-- カテゴリー -->
        <td>
          <select name="category">
          <option value = "<?php print $value['category'];?>"><?php print $value['category'];?></option>
          <option value = "ビール">ビール</option>
          <option value = "その他">その他</option>
        </select>
        </td>
        <!-- 在庫数 -->
        <td>
          <input type="text" name="stock" value="<?php print $value['stock'];?>">個
        </td>
        <!-- 味 -->
        <td>
          <select name="taste">
          <option value="<?php print $value['taste'];?>"><?php print $value['taste'];?></option>
          <option value = "Dry" >Dry</option>
          <option value = "Medium" >Medium</option>
          <option value = "Sweet" >Sweet</option></select>
        </td>
        <!-- 重さ -->
        <td>
          <select name="body">
            <option value="<?php print $value['body'];?>"><?php print $value['body'];?></option>
            <option value = "Full" >Full</option>
            <option value = "Medium" >Medium</option>
            <option value = "Light" >Light</option></select>
        </td>
        <!-- 色 -->
        <td>
          <select name="color">
            <option value="<?php print $value['color'];?>"><?php print $value['color'];?></option>
            <option value = "white" >white</option>
            <option value = "yellow" >yellow</option>
            <option value = "orange" >orange</option>
            <option value = "black" >black</option></select>
        </td>
        <!-- 変更ボタン -->
        <td>
          <input type = "hidden" name="process_kind" value="update_status">
          <input type = "hidden" name="item_id" value="<?php print $value['item_id'];?>">
          <input type="submit" value= "変更">
          </form>
        </td>
      </tr>
      <?php }?>
  </table>

</div>
</main>
</body>
</html>
