<?php
/**
 * @package		CSTester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	nazarpc;
/**
 * CSTester class for testing of PHP applications
 */
class CSTester {
	protected	$tests_directory,
				$test_file,
				$cli,
				$port				= 8001,
				$title;
	/**
	 * @param string $tests_directory	Absolute path to tests directory
	 */
	function __construct ($tests_directory) {
		$this->tests_directory	= $tests_directory;
		$this->cli				= PHP_SAPI == 'cli';
		@ini_set('error_log', "$tests_directory/error.log");
		$this->title			= json_decode(file_get_contents("$this->tests_directory/tests.json"), true)['title'];
		/**
		 * Detect file, where tester was called
		 */
		$debug_backtrace		= debug_backtrace();
		$this->test_file		= array_pop($debug_backtrace)['file'];
		unset($debug_backtrace);
		if ($_SERVER['DOCUMENT_ROOT']) {
			$this->test_file	= str_replace(rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/', '', $this->test_file);
		} else {
			$this->test_file	= explode('/', $this->test_file);
			$this->test_file	= array_pop($this->test_file);
		}
	}
	/**
	 * Set port number (for CLI mode only)
	 *
	 * @param int $port Default - 8001
	 */
	function set_port ($port) {
		if ($this->cli) {
			$this->port	= (int)$port;
		}
	}
	/**
	 * Run testing
	 */
	function run () {
		if (!isset($_GET['suite'], $_GET['test'], $_GET['key'])) {
			$this->run_suites();
		} else {
			$this->run_test($_GET['suite'], $_GET['test'], $_GET['key']);
		}
	}
	/**
	 * Run testing of particular test
	 *
	 * @param string	$suite
	 * @param string	$test
	 * @param string	$key
	 */
	protected function run_test ($suite, $test, $key) {
		$tests	= $this->tests_directory;
		if (@file_get_contents("$tests/key" != $key)) {
			exit('Wrong key');
		}
		define('TESTS', $tests);
		define('TEMP', "$tests/temp");
		/**
		 * Include general prepare file for all suites
		 */
		if (file_exists("$tests/prepare.php")) {
			require_once "$tests/prepare.php";
		}
		/**
		 * Include general prepare file for current suite
		 */
		if (file_exists("$tests/suites/$suite/prepare.php")) {
			require_once "$tests/suites/$suite/prepare.php";
		}
		exit((string)require "$tests/suites/$suite/$test.php");
	}
	/**
	 * Run testing of all suites
	 */
	protected function run_suites () {
		/**
		 * Base necessary directories
		 */
		$tests	= $this->tests_directory;
		$suites	= "$tests/suites";
		$temp	= "$tests/temp";
		/**
		 * Set time limit to 1 hour
		 */
		set_time_limit(60 * 60);
		@ini_set('max_input_time', 60 * 60);
		/**
		 * Generate random key (for security reasons)
		 */
		file_put_contents(
			"$tests/key",
			$key = hash('sha512', microtime(true).uniqid('test', true))
		);
		/**
		 * If executed from command line
		 */
		if ($this->cli) {
			/**
			 * Start embedded php web-server in current directory
			 */
			$web_server_pid = exec("php -S localhost:$this->port >/dev/null 2>/dev/null & echo $!");
			/**
			 * Wait 500 ms for web server starting
			 */
			usleep(500000);
			$base_url	= "http://localhost:$this->port";
		} else {
			$base_url	= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http')."://$_SERVER[HTTP_HOST]";
		}
		$total_success	= 0;
		$total_failed	= 0;
		$suites_list	= [];
		$suites_dir		= opendir($suites);
		while ($suite = readdir($suites_dir)) {
			if (
				!in_array($suite, ['.', '..']) &&
				is_dir("$suites/$suite")
			) {
				$suites_list[]	= $suite;
			}
		}
		closedir($suites_dir);
		unset($suites_dir, $suite);
		natcasesort($suites_list);
		$suites_count	= count($suites_list);
		/**
		 * Display tester header
		 */
		$this->display_header();
		/**
		 * Look through all suites, and run all tests of each suite
		 */
		foreach ($suites_list as &$suite) {
			/**
			 * Check for suite file
			 */
			if (!file_exists("$suites/$suite/suite.json")) {
				continue;
			}
			$suite_data			= json_decode(file_get_contents("$suites/$suite/suite.json"), true);
			$local_success		= 0;
			$local_failed		= 0;
			$suite				= urlencode($suite);
			$results			= [
				'title'	=> $suite_data['title'],
				'tests'	=> []
			];
			/**
			 * Run tests of current suite
			 */
			foreach ($suite_data['tests'] as $test => $title) {
				/**
				 * Create temp directory if not exists
				 */
				if (!is_dir($temp)) {
					mkdir($temp);
				}
				/**
				 * Clear temp directory before every test
				 */
				$this->clear_temp();
				/**
				 * Run single test
				 */
				$test				= urlencode($test);
				$result				= file_get_contents("$base_url/$this->test_file?suite=$suite&test=$test&key=$key");
				if ($result === '0') {
					++$total_success;
					++$local_success;
				} else {
					++$total_failed;
					++$local_failed;
				}
				/**
				 * Test results
				 */
				$results['tests'][]	= [
					'title'			=> $title,
					'result'		=> $result === '0',
					'result_text'	=> $result
				];
			}
			unset($test, $title, $result);
			$results['success']	= $local_success;
			$results['failed']	= $local_failed;
			$suite				= $results;
			/**
			 * Display testing progress
			 */
			$this->display_testing_progress($suites_count);
		}
		unset($suite, $suite_data, $local_success, $local_failed);
		/**
		 * Clear temp directory after all tests
		 */
		$this->clear_temp();
		/**
		 * Stop embedded php web server
		 */
		if (isset($web_server_pid)) {
			exec("kill $web_server_pid");
		}
		unset($web_server_pid);
		/**
		 * Delete key
		 */
		@unlink("$tests/key");
		/**
		 * Display results
		 */
		$this->display_results($suites_list, $total_success, $total_failed);
		/**
		 * Exit with status code
		 */
		exit($total_failed);
	}
	/**
	 * Display header of tester
	 */
	protected function display_header () {
		$title	= $this->title;
		/**
		 * HTML presentation
		 */
		if (!$this->cli) {
			@ini_set('output_buffering', 'off');
			@ini_set('zlib.output_compression', 'off');
			ob_implicit_flush(true);
			header('Content-Type: text/html; charset=utf-8');
			echo	"<!doctype html>\n".
					"<title>$title: Test in progress 0%...</title>\n".
					"<meta charset=\"utf-8\">\n".
					"<style>html, body {font-size: 0; height: 100%; margin: 0; padding: 0; width: 100%;} body > p {background: #B9B9B9; display: inline-block; height: 10%; margin: 0; padding: 0; width: 10%;}</style>\n".
					"<script>function update_title_percents(percent) {document.getElementsByTagName('title')[0].innerHTML = \"$title: Test in progress \" + percent + '%...'}</script>".
					str_repeat(' ', 1024 * 64);
		/**
		 * CLI presentation
		 */
		} else {
			echo	"\e[1m$title\e[21m\n".
					str_repeat('-', strlen($title))."\n\n";
			echo	"\e[1ATest in progress 0%...\n";
		}
	}
	/**
	 * Display progress of testing
	 *
	 * @param int	$total_count
	 */
	protected function display_testing_progress ($total_count) {
		static	$current_percent	= 0,
				$completed_suites	= 0;
		++$completed_suites;
		$new_percent		= round($completed_suites / $total_count * 100);
		if ($new_percent == $current_percent) {
			return;
		}
		/**
		 * HTML presentation
		 */
		if (!$this->cli) {
			echo	str_repeat("<p>&nbsp;</p>", $new_percent - $current_percent).
					"<script>update_title_percents($new_percent)</script>".
					str_repeat(' ', 1024 * 64);
		/**
		 * CLI presentation
		 */
		} else {
			echo "\e[1ATest in progress $new_percent%...\n".str_pad(str_repeat('#', round($new_percent / 2)), 50, ' ')."\r";
		}
		$current_percent	= $new_percent;
	}
	/**
	 * Display results of testing
	 *
	 * @param array[]	$suites_list
	 * @param int		$total_success
	 * @param int		$total_failed
	 */
	protected function display_results ($suites_list, $total_success, $total_failed) {
		$title	= $this->title;
		$total	= $total_success + $total_failed;
		/**
		 * HTML presentation
		 */
		if (!$this->cli) {
			$css		= explode('*/', file_get_contents(__DIR__.'/includes/style.css'), 2)[1];
			$img		= base64_encode(file_get_contents(__DIR__.'/includes/logo.png'));
			$content	=	"<!doctype html>\n".
							"<title>Test results $total_success/$total ".round($total_success / $total * 100, 2)."%</title>\n".
							"<meta charset=\"utf-8\">\n".
							"<style type=\"text/css\">$css</style>\n".
							"<header><img src=\"data:image/png;base64,$img\" alt=\"\"><h1>$title</h1></header>\n".
							"<section><h2>Test results $total_success/$total ".round($total_success / $total * 100, 2)."%</h2>\n";
			foreach ($suites_list as $suite) {
				$content		.= "<article>\n";
				$tests_total	= $suite['success'] + $suite['failed'];
				$content		.= "<h3>$suite[title] $suite[success]/$tests_total ".round($suite['success'] / $tests_total * 100, 2)."%</h3>\n";
				foreach ($suite['tests'] as $test) {
					if ($test['result']) {
						$content	.= "<p class=\"success\">$test[title]</p>";
					} else {
						$content	.= "<p class=\"failed\">$test[title]</p>";
					}
					if (!$test['result'] && $test['result_text']) {
						$content	.= "<p class=\"more\">$test[result_text]</p>";
					}
				}
				$content		.= "</article>\n";
			}
			$content	.=	"</section>".
							"<footer>Powered by CleverStyle Tester<br>Copyright (c) 2013, Nazar Mokrynskyi</footer>";
			$content	= json_encode($content, JSON_UNESCAPED_UNICODE);
			echo "<script>document.documentElement.innerHTML = $content;</script>";
		/**
		 * CLI presentation
		 */
		} else {
			echo	"\e[1ATest results $total_success/$total ".round($total_success / $total * 100, 2)."%   \n".
					str_repeat(' ', 50)."\r";
			foreach ($suites_list as $suite) {
				$tests_total	= $suite['success'] + $suite['failed'];
				echo "\t$suite[title] $suite[success]/$tests_total ".round($suite['success'] / $tests_total * 100, 2)."%\n";
				foreach ($suite['tests'] as $test) {
					if ($test['result']) {
						echo "\t\t$test[title]\n";
					} else {
						echo "\t\t\e[91m$test[title]\e[39m".($test['result_text'] ? "\n\t\t| $test[result_text]" : '')."\n";
					}
				}
			}
			echo	"\nPowered by CleverStyle Tester\nCopyright (c) 2013, Nazar Mokrynskyi\n";
		}
	}
	/**
	 * Clear temp directory
	 */
	protected function clear_temp ($dir = null) {
		if ($dir === null) {
			$dir = "$this->tests_directory/temp";
		}
		$dir_handle		= opendir($dir);
		while ($item = readdir($dir_handle)) {
			if (
				!in_array($item, ['.', '..'])
			) {
				$item	= "$dir/$item";
				if (is_dir($item)) {
					$this->clear_temp($item);
					@rmdir($item);
				} else {
					@unlink($item);
				}
			}
		}
		closedir($dir_handle);
	}
}