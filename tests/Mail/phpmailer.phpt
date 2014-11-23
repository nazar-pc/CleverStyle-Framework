--TEST--
Test for correctly modified PHPMailer and SMTP classes
--FILE--
<?php
include __DIR__.'/../custom_loader.php';
require DIR.'/core/thirdparty/PHPMailer.php';
if (method_exists('PHPMailer', '__construct')) {
	die('__construct() method should be removed from PHPMailer class');
}
if (!file_exists(DIR.'/core/thirdparty/SMTP.php')) {
	die('File with SMTP class for PHPMailer should be named "SMTP.php"');
}
echo 'Done';
?>
--EXPECT--
Done
