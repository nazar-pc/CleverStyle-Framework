<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer;
use
	cs\Config,
	cs\Singleton,
	Composer\Console\Application,
	Symfony\Component\Console\Input\ArrayInput,
	Symfony\Component\Console\Output\BufferedOutput;
class Composer {
	use
		Singleton;
	const COMPONENT_MODULE = 1;
	const COMPONENT_PLUGIN = 2;
	const MODE_ADD         = 1;
	const MODE_DELETE      = 2;
	/**
	 * Force update even if nothing changed
	 *
	 * @var bool
	 */
	protected $force_update = false;
	protected function construct () {
		require_once 'phar://'.__DIR__.'/composer.phar/src/bootstrap.php';
	}
	/**
	 * Update composer even if nothing changed
	 *
	 * @return array
	 */
	function force_update () {
		$this->force_update = true;
		return $this->update();
	}
	/**
	 * Update composer
	 *
	 * @param string $component_name Is specified if called before component actually installed (to satisfy dependencies)
	 * @param int    $component_type Composer::COMPONENT_MODULE or Composer::COMPONENT_PLUGIN
	 * @param int    $mode           Composer::MODE_ADD or Composer::MODE_DELETE
	 *
	 * @return array Array with `code` and `description` elements, first represents status code returned by composer, second contains ANSI text returned by
	 *               composer
	 */
	function update ($component_name = null, $component_type = self::COMPONENT_MODULE, $mode = self::MODE_ADD) {
		time_limit_pause();
		$storage     = DIR.'/storage/Composer';
		$status_code = 0;
		$description = '';
		$this->prepare($storage);
		$composer = $this->generate_composer_json($component_name, $component_type, $mode);
		if ($composer['repositories']) {
			file_put_json("$storage/tmp/composer.json", $composer);
			if (
				$this->force_update ||
				!file_exists("$storage/composer.json") ||
				md5_file("$storage/tmp/composer.json") != md5_file("$storage/composer.json")
			) {
				$this->force_update = false;
				$application        = new Application;
				$input              = new ArrayInput([
					'command'       => 'update',
					'--working-dir' => "$storage/tmp",
					'--ansi',
					'--no-dev'
				]);
				$output             = new BufferedOutput;
				$application->setAutoExit(false);
				$status_code = $application->run($input, $output);
				$description = $output->fetch();
				file_put_contents("$storage/last_execution.log", $description);
				if ($status_code == 0) {
					$this->rmdir_recursive("$storage/vendor");
					@unlink("$storage/composer.json");
					@unlink("$storage/composer.lock");
					rename("$storage/tmp/vendor", "$storage/vendor");
					rename("$storage/tmp/composer.json", "$storage/composer.json");
					rename("$storage/tmp/composer.lock", "$storage/composer.lock");
				}
			}
		} else {
			$this->rmdir_recursive("$storage/vendor");
			@unlink("$storage/composer.json");
			@unlink("$storage/composer.lock");
		}
		$this->cleanup($storage);
		time_limit_pause(false);
		return [
			'code'        => $status_code,
			'description' => $description
		];
	}
	protected function prepare ($storage) {
		if (!is_dir($storage)) {
			@mkdir($storage, 0770);
		}
		$this->rmdir_recursive("$storage/home");
		@mkdir("$storage/home", 0770);
		$this->rmdir_recursive("$storage/tmp");
		@mkdir("$storage/tmp", 0770);
		putenv("COMPOSER_HOME=$storage/home");
		@ini_set('display_errors', 1);
		@ini_set('memory_limit', '512M');
		@unlink("$storage/last_execution.log");
	}
	protected function generate_composer_json ($component_name, $component_type, $mode) {
		$composer = [
			'repositories' => [],
			'require'      => []
		];
		$Config   = Config::instance();
		foreach ($Config->components['modules'] as $module => $module_data) {
			if (
				$module == $component_name &&
				$component_type == self::COMPONENT_MODULE &&
				$mode == self::MODE_DELETE
			) {
				continue;
			}
			if (
				(
					$module_data['active'] != -1 ||
					(
						$component_name == $module &&
						$component_type == self::COMPONENT_MODULE
					)
				) &&
				file_exists(MODULES."/$module/meta.json")
			) {
				$this->generate_package(
					$composer,
					file_get_json(MODULES."/$module/meta.json")
				);
			}
		}
		foreach (
			array_merge(
				$Config->components['plugins'],
				$component_type == self::COMPONENT_PLUGIN && $component_name ? [$component_name] : []
			) as $plugin
		) {
			if (
				$plugin == $component_name &&
				$component_type == self::COMPONENT_PLUGIN &&
				$mode == self::MODE_DELETE
			) {
				continue;
			}
			if (file_exists(PLUGINS."/$plugin/meta.json")) {
				$this->generate_package(
					$composer,
					file_get_json(PLUGINS."/$plugin/meta.json")
				);
			}
		}
		return $composer;
	}
	protected function generate_package (&$composer, $meta) {
		if (!isset($meta['version'], $meta['require_composer'])) {
			return;
		}
		$package                       = "$meta[category]/$meta[package]";
		$composer['repositories'][]    = [
			'type'    => 'package',
			'package' => [
				'name'    => $package,
				'version' => $meta['version'],
				'require' => $meta['require_composer'],
				'dist'    => [
					'url'  => __DIR__.'/empty.zip',
					'type' => 'zip'
				]
			]
		];
		$composer['require'][$package] = $meta['version'];
	}
	protected function cleanup ($storage) {
		$this->rmdir_recursive("$storage/home");
		$this->rmdir_recursive("$storage/tmp");
	}
	protected function rmdir_recursive ($dir) {
		if (!is_dir($dir)) {
			return;
		}
		get_files_list(
			$dir,
			false,
			'fd',
			true,
			true,
			false,
			false,
			true,
			function ($item) {
				if (is_dir($item)) {
					@rmdir($item);
				} else {
					@unlink($item);
				}
			}
		);
		@rmdir($dir);
	}
}
