<?php
/**
 * @package    CleverStyle Framework
 * @subpackage Test
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
if (!extension_loaded('xdebug')) {
	return;
}

require_once __DIR__.'/php-code-coverage.phar';

$filter = new \SebastianBergmann\CodeCoverage\Filter;
$filter->addDirectoryToWhitelist(__DIR__.'/../build');
$filter->addDirectoryToWhitelist(__DIR__.'/../components/modules/System');
$filter->addDirectoryToWhitelist(__DIR__.'/../core');
$filter->addDirectoryToWhitelist(__DIR__.'/cscms.travis');
$filter->removeDirectoryFromWhitelist(__DIR__.'/../core/thirdparty');
$filter->removeDirectoryFromWhitelist(__DIR__.'/cscms.travis/core/thirdparty');

$coverage_data_location = __DIR__.'/coverage_data.json';

$coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage(null, $filter);
if (file_exists($coverage_data_location)) {
	$coverage->setData(json_decode(file_get_contents($coverage_data_location), true));
}
$coverage->start($_ENV['TEST_FILE']);

register_shutdown_function(
	function () use ($coverage, $coverage_data_location) {
		$coverage->stop();
		file_put_contents($coverage_data_location, json_encode($coverage->getData(true), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	}
);
