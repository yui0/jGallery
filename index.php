<?php
mb_language('ja');
mb_internal_encoding('UTF-8');

require 'config.php';
require 'import_csv_to_sqlite.php';

function img_rsz($path, $file)
{
	list($width, $height) = getimagesize($path.$file); // サイズ取得
	if ($width > $height) {
		// 横長写真
		$newwidth = 100; // 横幅指定
		$newheight = abs($newwidth * $height / $width); // 縦幅を計算
	} else {
		// 縦長写真
		$newheight = 75; // 縦幅指定
		$newwidth = abs($newheight * $width / $height); // 横幅を計算
	}
	$image_p = imagecreatetruecolor($newwidth, $newheight); // 空の画像を作成
	$image = imagecreatefromjpeg($path.$file); // 元の画像をコピー
	imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height); // サイズ変更
	imagejpeg($image_p, $path.'_'.$file); // 画像の出力
}

$i = 0;
foreach (glob($path."*") as $value) { // ディレクトリ内を見る
	$file = str_replace($path, '', $value); // ファイル名のみ取得
	if (substr($file, 0, 1) != '_' && (substr($file, -4) == '.jpg' || substr($file, -4) == '.JPG')) {
		$files[$i] = $file; // 元画像ファイル名のみ配列に格納
		if (file_exists($path.'_'.$file) == false) {
			img_rsz($path, $file); // サムネイルが存在しなければ生成
		}
		$i++;
	}
}

$images = "";
$explanation = array('', ' [問い合わせ中]', ' [済]');

switch ($database) {
case 0:
	$records = file($path.'list.txt');
	break;
case 1:
	$file = new SplFileObject($path.'list.csv');
	$file->setFlags(SplFileObject::READ_CSV);
	foreach ($file as $line) {
		$records[] = $line;
	}
	break;
case 2:
	if (file_exists($path.'list.db') == false) {
		$pdo = new PDO('sqlite:'.$path.'list.db');
		import_csv_to_sqlite($pdo, $path.'list.csv', array("table" => "list", "fields" => array("id", "comments", "flag")));
	} else {
		$pdo = new PDO('sqlite:'.$path.'list.db');
	}
	$sql = "select * from list";
	$statement = $pdo->query($sql);

	foreach ($statement as $row) {
		$s = $row["comments"].$explanation[$row["flag"]];
		$file = $files[($row["id"]-1)];
		$images .= "\t<a href=\"".$path.$file."\" data-caption=\"№".$row["id"]." ".$s."\"><img src=\"".$path."_".$file."\"></a>\n";
	}
}
if ($database <2) {
	$i = 0;
	foreach ($files as $file) {
		$s = $records[$i][1].$explanation[$records[$i][2]];
		$i++;
		$images .= "\t<a href=\"".$path.$file."\" data-caption=\"№".$i." ".$s."\"><img src=\"".$path."_".$file."\"></a>\n";
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
	<link rel="apple-touch-icon-precomposed" href="img/apple-touch-icon.png">
	<link rel="shortcut icon" href="img/favicon.ico" type="image/vnd.microsoft.icon">
	<link rel="icon" href="img/favicon.ico" type="image/vnd.microsoft.icon">

	<title><?= $title ?></title>

	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/honoka.css">
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

	<!--[if lt IE 9]>
		<script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

	<!-- jQuery -->
	<script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
	<script src="js/bootstrap.min.js"></script>

	<!-- Fotorama -->
	<link href="fotorama.css" rel="stylesheet">
	<script src="fotorama.js"></script>
	<!-- Just don’t want to repeat this prefix in every img[src] -->
	<!-- <base href="<?= $base_url ?>"> -->
</head>

<body>
<header>
  <div class="navbar navbar-default navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <a href="/" class="navbar-brand"><?= $title ?></a>
      </div>
    </div>
  </div>
</header>

<section class="section section-inverse japanese-font">
<div class="container">
<div class="row">
	<!-- Fotorama -->
	<div class="fotorama" align="center"
		data-width="700" data-ratio="4/3" data-max-width="100%"
		data-arrows="false" data-click="true" data-swipe="true"
		data-keyboard='{"space": true, "home": true, "end": true, "up": true, "down": true, "left": true, "right": true}'
		data-nav="thumbs" data-thumbheight="48">
<?= $images ?>
	</div>
</div>

<br>
<div class="row text-center">
	<button class="btn btn-primary" data-toggle="modal" data-target="#myModal"><?= $button ?></button><?= $admin_button ?>
</div>
<br>
<div class="row">
	<div id="thanks"></div>
</div>

<div class="modal fade in" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title"><?= $dialog ?></h4>
      </div>
      <div class="modal-body">
        <form class="contact" name="contact">
            <!--<label class="label" for="name">Your Name</label><br>
            <input type="text" name="name" class="input-xlarge"><br>-->
            <label class="label" for="email"><i class="glyphicon glyphicon-envelope"></i> メールアドレス ※必須</label><br>
            <input type="email" name="email" class="input-xlarge" dataCheck="mandatory mail"><br><br>
            <label class="label" for="message"><i class="glyphicon glyphicon glyphicon-pencil"></i> お問い合わせ内容 ※必須</label><br>
            <textarea name="message" id="message" class="input-xlarge" rows="10" dataCheck="mandatory"></textarea>
        </form>
      </div>
      <div class="modal-footer">
        <input class="btn btn-success" type="submit" value="<?= $send ?>" id="submit">
        <a href="#" class="btn" data-dismiss="modal">Close</a>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
jQuery(function($){
//	$('.modal').modal('show');
	$("input#submit").click(function(){
		$.ajax({
			type: "POST",
			url: "mail.php", // process to mail
			data: $('.contact').serialize(),
			success: function(msg){
				$("#thanks").html(msg) // hide button and show thank you
				$("#myModal").modal('hide'); // hide popup  

				if (msg.indexOf("warning") == -1) {
					$.ajax({
						url: "process.php",
						data: 'id='+index+'&f=1'
					});
				}
			},
			error: function(){
				alert("送信に失敗しました。");
			}
		});
	});

	var index = 1;
	$('.fotorama').on('fotorama:show', function(e, fotorama, direct){
		index = fotorama.activeIndex+1;
		//$('#number').text((fotorama.activeIndex + 1) + '/' + fotorama.size);
		$('#message').val('No.' + (fotorama.activeIndex + 1) + '<?= $about ?>');
	})
	.fotorama();

	$('#check1').on('click', function(e){
		$.ajax({
			type: "GET",
			url: "process.php",
			data: 'id='+index+'&f=2',
			success: function(msg){
				//alert(msg+'id='+index+'&f=2');
				location.reload();
			},
			error: function(){
				alert("失敗しました。");
			}
		});
	});
	$('#check2').on('click', function(e){
		$.ajax({
			type: "GET",
			url: "process.php",
			data: 'id='+index+'&f=0',
			success: function(){
				location.reload();
			},
			error: function(){
				alert("失敗しました。");
			}
		});
	});
});
</script>
</div><!-- /.container -->
</section>

<footer class="small">
  <div class="container">
    <div class="row">
      <div class="col-xs-12 text-center copyright">
        <?= $copyright ?>
      </div>
    </div>
  </div>
</footer>
</body>
</html>
