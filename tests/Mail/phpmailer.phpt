--FILE--
<?php
include __DIR__.'/../bootstrap.php';
require DIR.'/core/thirdparty/PHPMailer.php';
if (method_exists('PHPMailer', '__construct')) {
	die('__construct() method should be removed from PHPMailer class');
}
if (!file_exists(DIR.'/core/thirdparty/SMTP.php')) {
	die('File with SMTP class for PHPMailer should be named "SMTP.php"');
}
?>
Done
--EXPECT--
Done
