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
$dns = 'mysql:dbname='.$dbname.';host'.$host;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 新規追加の認識
  if(isset($_POST['process_kind']) === TRUE){
    $process_kind = $_POST['process_kind'];
  }
  if($process_kind ="insert_item"){
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
    // 商品名受け取り

























  }


}






?>
<!DOCTYPE html>
<head>
  <meta charset="utf-8">
  <title>BeerList商品管理ページ</title>
</head>
<body>
<h1> Beer List 商品管理ページ</h1>

<!-- フォームボタン部分 -->
<div class="button">
<form method = "post" enctype="multipart/form-data">
  <label>商品名:<input type ="text" name ="beer_name"></label><br>

  <label>値段:<input type ="text" name ="price"></label><br>

  <input type="file" name="new_img"><br>

  <label>ステータス: 公開<input type ="radio" name ="satus" value=1> 非公開<input type ="radio" name ="satus" value=0></label><br>

  <label>説明:<input type ="text" name ="detail"></label><br>

  <label>カテゴリー:
    <select name="category">
    <option value = "">選択してください</option>
    <option value = "ビール">ビール</option>
    <option value = "その他">その他</option>
  </select>
  </label><br><br>

  <label>個数:
    <input type ="text" name ="beer_stock">
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
  <input type="hidden" name="process_kind" value="insert_item">
  <input type="submit" name="submit" value="追加">

</form>
</div>
<!-- 以下一覧表示部分 -->
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
      <tr class="<?php? if($value['status'] === 0){ print "backGray";}?>">

        <!-- 画像 -->
        <td>
          <img src="<?php print $img_dir . $value['img']; ?>">
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
          if($value['status'] === 1){print "checked"};?>>公開
          <input type="radio" name="status" value = 0 <?php
          if($value['status'] === 0){print "checked"};?>>非公開
        </td>
        <!-- 説明文 -->
        <td>
          <input type="text" name="detail" value="<?php print $value['detail'];?>">
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
          <option value="<?php print $value['taste'];?>"><?print $value['taste'];?></option>
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

</body>
</html>
