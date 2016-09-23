<?php
if (!isset($argv[1])) {
	die("First argument MUST point to vendor directory with standalone htmlpurifier (like htmlpurifier-x.x.x-standalone), resulting `htmlpurifier.tar.gz` will be created in current directory\n");
}
$phar = new Phar('htmlpurifier.phar');
$phar->startBuffering();
$phar->buildFromDirectory($argv[1]);
$phar->stopBuffering();
$phar->convertToData(Phar::TAR, Phar::GZ);
unset($phar);
Phar::unlinkArchive('htmlpurifier.phar');
