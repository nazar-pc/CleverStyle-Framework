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

require_once __DIR__.'/php-code-coverage+PR-457+phar.phar';

$filter = new \SebastianBergmann\CodeCoverage\Filter;
$filter->addDirectoryToWhitelist(__DIR__.'/../build');
$filter->addDirectoryToWhitelist(__DIR__.'/../core');
$filter->addDirectoryToWhitelist(__DIR__.'/../install');
$filter->addDirectoryToWhitelist(__DIR__.'/../modules/System');
$filter->addDirectoryToWhitelist(__DIR__.'/cscms.travis');
// Following 3 files are hacked explicitly because php-code-coverage doesn't want to work correctly with Phar as with directory
$filter->addFileToWhitelist('phar://'.__DIR__.'/cscms.travis/distributive.phar.php/cli.php');
$filter->addFileToWhitelist('phar://'.__DIR__.'/cscms.travis/distributive.phar.php/Installer.php');
$filter->addFileToWhitelist('phar://'.__DIR__.'/cscms.travis/distributive.phar.php/web.php');
$filter->removeDirectoryFromWhitelist(__DIR__.'/../core/thirdparty');
$filter->removeDirectoryFromWhitelist(__DIR__.'/../modules/System/meta/update');
$filter->removeDirectoryFromWhitelist(__DIR__.'/cscms.travis/core/thirdparty');

$coverage_data_location = __DIR__.'/coverage_data.json';

$coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage(null, $filter);
$coverage->start($_ENV['TEST_FILE']);

register_shutdown_function(
	function () use ($coverage, $coverage_data_location) {
		$coverage->stop();

		if (file_exists($coverage_data_location)) {
			$coverage_existing = new \SebastianBergmann\CodeCoverage\CodeCoverage(null, $coverage->filter());
			$coverage_existing->setData(json_decode(file_get_contents($coverage_data_location), true));
			$coverage->merge($coverage_existing);
			unset($coverage_existing);
		}

		$data = $coverage->getData(true);

		$normal_prefix    = realpath(__DIR__.'/..');
		$installed_prefix = __DIR__.'/cscms.travis';
		$installer_prefix = 'phar://'.__DIR__.'/cscms.travis/distributive.phar.php';
		$new_data         = [];
		foreach ($data as $file => $d) {
			if (strpos($file, $installed_prefix) === 0) {
				$new_file = $normal_prefix.substr($file, strlen($installed_prefix));
			} elseif (strpos($file, $installer_prefix) === 0) {
				$new_file = $normal_prefix.'/install'.substr($file, strlen($installer_prefix));
			} else {
				continue;
			}
			$new_data[$new_file] = $d;
		}
		unset($data);
		if ($new_data) {
			$coverage_new = new \SebastianBergmann\CodeCoverage\CodeCoverage(null, $coverage->filter());
			$coverage_new->setData($new_data);
			$coverage->merge($coverage_new);
			unset($coverage_new);
		}

		file_put_contents($coverage_data_location, json_encode($coverage->getData(true), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	}
);
