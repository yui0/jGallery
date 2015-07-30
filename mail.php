<?php
mb_language('ja');
mb_internal_encoding('UTF-8');

require 'config.php';

// grab named inputs from html then post to #thanks
if (!empty($_POST['email'])) {
	//$name = strip_tags($_POST['name']);
	$email = strip_tags($_POST['email']);
	$message = strip_tags($_POST['message']);
	echo "<span class=\"alert alert-success\">送信に成功しました。送信した内容は次の通りです:</span><br><br>";
	//echo "<stong>Name:</strong> ".$name."<br>";   
	echo "<stong>Email:</strong> ".$email."<br>"; 
	echo "<stong>Message:</strong> ".$message."<br>";
	 
	// generate email and send!
	$to = $myemail;
//	$subject = "連絡です。";
//	$email_subject = "Contact form submission: $name";
//	$email_body = "You have received a new message. ".
//	" Here are the details:\n Name: $name \n ".
//	"Email: $email\n Message:\n $message";
	$headers = "From: $myemail\n";
	$headers .= "Reply-To: $email";
	mail($to, mb_encode_mimeheader($subject), mb_convert_encoding($message, "ISO-2022-JP", "auto"), $headers);
} else {
	echo "<span class=\"alert alert-warning\">メールアドレスを記入してください。</span><br>";
}
?>
