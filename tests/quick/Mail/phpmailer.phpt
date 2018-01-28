--FILE--
<?php
include __DIR__.'/../../unit.php';
if (
	!class_exists('\PHPMailer\PHPMailer\PHPMailer') ||
	!class_exists('\PHPMailer\PHPMailer\Exception') ||
	!class_exists('\PHPMailer\PHPMailer\SMTP')
) {
	die("PHPMailer's `src` directory contents should be in `thirdparty/PHPMailer/PHPMailer`");
}
?>
--EXPECT--
