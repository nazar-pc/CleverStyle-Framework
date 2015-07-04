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
	cs\Event,
	cs\Singleton,
	Symfony\Component\Console\Input\ArrayInput,
	Symfony\Component\Console\Output\BufferedOutput;
/**
 * Provides next events:
 *  Composer/generate_package
 *  [
 *   'package' => &$package, //Composer package generated, might be modified
 *   'meta'    => $meta      //Parsed `meta.json` for component, package is generated for
 *  ]
 *
 *  Composer/generate_composer_json
 *  [
 *   'composer_json' => &$composer_json //`composer.json` structure that will be used for dependencies installation, might be modified
 *   'auth_json'     => &$auth_json     //`auth.json` structure that will be used for auth credentials during dependencies installation, might be modified
 *  ]
 *
 *  Composer/Composer
 *  [
 *   'Composer' => $Composer //Instance of `\Composer\Composer`, so that it is possible, for instance, to inject some plugins manually
 *  ]
 *
 *  Composer/updated
 *  [
 *   'composer_json' => $composer_json, //`composer.json` structure that was used for dependencies installation
 *   'composer_lock' => $composer_lock  //`composer.lock` structure that was generated during dependencies installation
 *   'composer_root' => $composer_root  //Path to directory where dependencies were installed, and where `composer.json` and `composer.lock` are located
 *  ]
 */
/**
 * @method static Composer instance($check = false)
 */
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
		$storage     = STORAGE.'/Composer';
		$status_code = 0;
		$description = '';
		$this->prepare($storage);
		$composer_json = $this->generate_composer_json($component_name, $component_type, $mode);
		$auth_json     = _json_decode(Config::instance()->module('Composer')->auth_json ?: '[]');
		$Event         = Event::instance();
		$Event->fire(
			'Composer/generate_composer_json',
			[
				'composer_json' => &$composer_json,
				'auth_json'     => &$auth_json
			]
		);
		if ($composer_json['repositories']) {
			$this->file_put_json("$storage/tmp/composer.json", $composer_json);
			$this->file_put_json("$storage/tmp/auth.json", $auth_json);
			if (
				$this->force_update ||
				!file_exists("$storage/composer.json") ||
				md5_file("$storage/tmp/composer.json") != md5_file("$storage/composer.json")
			) {
				$this->force_update = false;
				$application        = new Application(
					function ($Composer) use ($Event) {
						$Event->fire(
							'Composer/Composer',
							[
								'Composer' => $Composer
							]
						);
					}
				);
				$input              = new ArrayInput(
					[
						'command'       => 'update',
						'--working-dir' => "$storage/tmp",
						'--no-dev'      => true,
						'--ansi'        => true,
						'--prefer-dist' => true,
						'-vvv'          => true
					]
				);
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
					$Event->fire(
						'Composer/updated',
						[
							'composer_json' => file_get_json("$storage/composer.json"),
							'composer_lock' => file_get_json("$storage/composer.lock"),
							'composer_root' => $storage
						]
					);
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
	/**
	 * @param string $filename
	 * @param array  $data
	 *
	 * @return int
	 */
	protected function file_put_json ($filename, $data) {
		return file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	}
	/**
	 * @param string $storage
	 */
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
	/**
	 * @param string $component_name
	 * @param int    $component_type `self::COMPONENT_MODULE` or `self::COMPONENT_PLUGIN`
	 * @param int    $mode           `self::MODE_ADD` or `self::MODE_DELETE`
	 *
	 * @return array Resulting `composer.json` structure in form of array
	 */
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
	/**
	 * @param array $composer
	 * @param array $meta
	 */
	protected function generate_package (&$composer, $meta) {
		if (!isset($meta['version'])) {
			return;
		}
		$package_name = "$meta[category]/$meta[package]";
		$package      = [
			'name'    => $package_name,
			'version' => $meta['version'],
			'require' => isset($meta['require_composer']) ? $meta['require_composer'] : [],
			'dist'    => [
				'url'  => __DIR__.'/empty.zip',
				'type' => 'zip'
			]
		];
		Event::instance()->fire(
			'Composer/generate_package',
			[
				'package' => &$package,
				'meta'    => $meta
			]
		);
		if (!$package['require']) {
			return;
		}
		$composer['repositories'][]         = [
			'type'    => 'package',
			'package' => $package
		];
		$composer['require'][$package_name] = $meta['version'];
	}
	/**
	 * @param string $storage
	 */
	protected function cleanup ($storage) {
		$this->rmdir_recursive("$storage/home");
		$this->rmdir_recursive("$storage/tmp");
	}
	/**
	 * @param string $dir
	 */
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
