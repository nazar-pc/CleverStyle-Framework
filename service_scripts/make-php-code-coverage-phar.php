<?php
if (!isset($argv[1])) {
	die("First argument MUST point to vendor directory after `composer require phpunit/php-code-coverage`, resulting `php-code-coverage.phar` will be created in current directory\n");
}
$phar = new Phar('php-code-coverage.phar');
$phar->startBuffering();
$phar->buildFromDirectory($argv[1]);
$phar->setStub(
	/** @lang PHP */
	<<<STUB
<?php
Phar::mapPhar('php-code-coverage.phar');
require_once 'phar://php-code-coverage.phar/autoload.php';
__HALT_COMPILER();
STUB
);
$phar->stopBuffering();
