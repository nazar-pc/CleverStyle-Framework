<?php
/**
 * @package    CleverStyle Framework
 * @subpackage Test
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
require_once __DIR__.'/php-code-coverage.phar';

$filter = new \SebastianBergmann\CodeCoverage\Filter;
$filter->addDirectoryToWhitelist(__DIR__.'/../build');
$filter->addDirectoryToWhitelist(__DIR__.'/../components/modules/System');
$filter->addDirectoryToWhitelist(__DIR__.'/../core');
$filter->removeDirectoryFromWhitelist(__DIR__.'/../core/thirdparty');

$coverage_data_location = __DIR__.'/coverage_data.json';

$coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage(null, $filter);
$coverage->setAddUncoveredFilesFromWhitelist(true);
$data             = json_decode(file_get_contents($coverage_data_location), true);
$normal_prefix    = realpath(__DIR__.'/..');
$installed_prefix = __DIR__.'/cscms.travis';
foreach ($data as $file => $d) {
	if (strpos($file, $installed_prefix) === 0) {
		$new_file = $normal_prefix.substr($file, strlen($installed_prefix));
		if (isset($data[$new_file])) {
			$lines = array_unique(
				array_merge(
					array_keys($data[$new_file]),
					array_keys($d)
				)
			);
			foreach ($data[$new_file] as $line => $calls) {
				/** @noinspection SlowArrayOperationsInLoopInspection */
				$data[$new_file][$line] = array_merge($data[$new_file][$line] ?: [], @$d[$line] ?: []) ?: $data[$new_file][$line];
			}
		} else {
			$data[$new_file] = $d;
		}
		unset($data[$file]);
	}
}
$coverage->setData($data);

$report_location = __DIR__.'/code_coverage_report';
exec("rm -rf ".escapeshellarg($report_location));
$html_report = new \SebastianBergmann\CodeCoverage\Report\Html\Facade;
@$html_report->process($coverage, $report_location);

$clover_report = new \SebastianBergmann\CodeCoverage\Report\Clover;
@$clover_report->process($coverage, "$report_location/clover.xml");

$text_report = new \SebastianBergmann\CodeCoverage\Report\Text(50, 90, false, true);
echo @$text_report->process($coverage);

unlink($coverage_data_location);
