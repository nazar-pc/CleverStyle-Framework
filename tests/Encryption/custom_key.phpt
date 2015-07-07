--TEST--
Encryption functionality with custom key
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
$key		= md5(microtime(true));
$encrypted	= $Encryption->encrypt($data, $key);
if ($encrypted === $data) {
	die('Encryption not working, probably, OpenSSL is not available');
}
$decrypted	= $Encryption->decrypt($encrypted, $key);
if ($decrypted !== $data) {
	die('Decryption failed, different result returned');
}
?>
Done
--EXPECT--
Done
