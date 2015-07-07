--TEST--
Basic Encryption functionality
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
include __DIR__.'/../_SERVER.php';
$Encryption	= Encryption::instance();
$data		= [
	'test',
	'data',
	'to',
	'be',
	'encrypted'
];
$encrypted	= $Encryption->encrypt($data);
if ($encrypted === $data) {
	die('Encryption not working, probably, OpenSSL is not available');
}
$decrypted	= $Encryption->decrypt($encrypted);
if ($decrypted !== $data) {
	die('Decryption failed, different result returned');
}
?>
Done
--EXPECT--
Done
