<?php
/**
 * @package    CleverStyle Framework
 * @subpackage Test
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
if (!extension_loaded('xdebug') || getenv('SKIP_COVERAGE')) {
	return;
}

require_once __DIR__.'/php-code-coverage.phar';

$filter = new \SebastianBergmann\CodeCoverage\Filter;
$filter->addDirectoryToWhitelist(__DIR__.'/../build');
$filter->addDirectoryToWhitelist(__DIR__.'/../core');
$filter->addDirectoryToWhitelist(__DIR__.'/../install');
$filter->addDirectoryToWhitelist(__DIR__.'/../modules/System');
$filter->addDirectoryToWhitelist(__DIR__.'/cscms.travis');
$filter->removeDirectoryFromWhitelist(__DIR__.'/../core/thirdparty');
$filter->removeDirectoryFromWhitelist(__DIR__.'/../modules/System/meta/update');
$filter->removeDirectoryFromWhitelist(__DIR__.'/cscms.travis/core/thirdparty');

$coverage_data_location = __DIR__.'/coverage_data.json';
$normal_prefix          = realpath(__DIR__.'/..');
$installed_prefix       = __DIR__.'/cscms.travis';

$coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage(null, $filter);
$coverage->start($_ENV['TEST_FILE']);

register_shutdown_function(
	function () use ($coverage, $coverage_data_location, $normal_prefix, $installed_prefix) {
		$coverage->stop();

		$data     = $coverage->getData(true);
		$new_data = [];
		foreach ($data as $file => $d) {
			if (strpos($file, $installed_prefix) === 0) {
				$new_file            = $normal_prefix.substr($file, strlen($installed_prefix));
				$new_data[$new_file] = $d;
			}
		}
		if ($new_data) {
			$coverage_new = new \SebastianBergmann\CodeCoverage\CodeCoverage(null, $coverage->filter());
			$coverage_new->setData($new_data);
			$coverage->merge($coverage_new);
			unset($coverage_new);
			$data = $coverage->getData(true);
		}

		file_put_contents(
			$coverage_data_location,
			json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n",
			FILE_APPEND
		);
	}
);
