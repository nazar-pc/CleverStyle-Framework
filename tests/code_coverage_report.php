<?php
/**
 * @package    CleverStyle Framework
 * @subpackage Test
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016-2017, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
require_once __DIR__.'/php-code-coverage.phar';

$filter = new \SebastianBergmann\CodeCoverage\Filter;
$filter->addDirectoryToWhitelist(__DIR__.'/../build');
$filter->addDirectoryToWhitelist(__DIR__.'/../core');
$filter->addDirectoryToWhitelist(__DIR__.'/../install');
$filter->addDirectoryToWhitelist(__DIR__.'/../modules/System');
$filter->removeDirectoryFromWhitelist(__DIR__.'/../core/thirdparty');
$filter->removeDirectoryFromWhitelist(__DIR__.'/../modules/System/meta/update');

$coverage_data_location = __DIR__.'/coverage_data.json';

$coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage(null, $filter);
$coverage->setAddUncoveredFilesFromWhitelist(true);
$handler = fopen($coverage_data_location, 'rb');
while ($data = fgets($handler)) {
	$c = new \SebastianBergmann\CodeCoverage\CodeCoverage(null, $filter);
	$c->setData(json_decode($data, true) ?: []);
	$coverage->merge($c);
	unset($c);
}
fclose($handler);
unset($data, $handler);

$report_location = __DIR__.'/code_coverage_report';
exec("rm -rf ".escapeshellarg($report_location));
$html_report = new \SebastianBergmann\CodeCoverage\Report\Html\Facade;
@$html_report->process($coverage, $report_location);

$clover_report = new \SebastianBergmann\CodeCoverage\Report\Clover;
@$clover_report->process($coverage, "$report_location/clover.xml");

$text_report = new \SebastianBergmann\CodeCoverage\Report\Text(50, 90, false, true);
echo @$text_report->process($coverage);

unlink($coverage_data_location);
