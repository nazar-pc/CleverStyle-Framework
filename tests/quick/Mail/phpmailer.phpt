--FILE--
<?php
include __DIR__.'/../../unit.php';
if (!class_exists('PHPMailer')) {
	die('File with PHPMailer class should be named "PHPMailer.php"');
}
if (!class_exists('SMTP')) {
	die('File with SMTP class for PHPMailer should be named "SMTP.php"');
}
?>
Done
--EXPECT--
Done
