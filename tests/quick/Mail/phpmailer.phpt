--FILE--
<?php
include __DIR__.'/../../unit.php';
if (method_exists('PHPMailer', '__construct')) {
	die('__construct() method should be removed from PHPMailer class');
}
if (!class_exists('SMTP')) {
	die('File with SMTP class for PHPMailer should be named "SMTP.php"');
}
?>
Done
--EXPECT--
Done
