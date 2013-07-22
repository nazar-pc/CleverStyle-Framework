<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Tester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
$tests_total	= $tests_success + $tests_failed;
echo	"\e[1mCleverStyle CMS Tester\e[21m\n".
		"----------------------\n".
		"Test results $tests_success/$tests_total ".round($tests_total / $tests_success * 100, 2)."%\n".
		implode(
			"\n",
			array_map(
				function ($suite) {
					$tests_total	= $suite['success'] + $suite['failed'];
					return	"\t$suite[title] $suite[success]/$tests_total ".round($tests_total / $suite['success'] * 100, 2)."%\n".
							implode(
								"\n",
								array_map(
									function ($test) {
										return $test['result'] ? "\t\t$test[title]" : "\t\t\e[91m$test[title]\e[39m".($test['result_text'] ? "\n\t\t| $test[result_text]" : '');
									},
									$suite['tests']
								)
							);
				},
				$test_suites
			)
		).
		"\n\n".
		"Copyright (c) 2011-2013, Nazar Mokrynskyi\n";