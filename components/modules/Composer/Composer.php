<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer;
use
	cs\Config,
	cs\Event,
	cs\Singleton,
	Symfony\Component\Console\Input\ArrayInput;

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
class Composer {
	use
		Singleton;
	const MODE_ADD    = 1;
	const MODE_DELETE = 2;
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
	 * @param string $category       `modules` or `plugins`
	 * @param int    $mode           Composer::MODE_ADD or Composer::MODE_DELETE
	 *
	 * @return array Array with `code` and `description` elements, first represents status code returned by composer, second contains ANSI text returned by
	 *               composer
	 */
	function update ($component_name = null, $category = 'modules', $mode = self::MODE_ADD) {
		time_limit_pause();
		$storage     = STORAGE.'/Composer';
		$status_code = 0;
		$description = '';
		$this->prepare($storage);
		$composer_json = $this->generate_composer_json($component_name, $category, $mode);
		$Config        = Config::instance();
		$auth_json     = _json_decode($Config->module('Composer')->auth_json ?: '[]');
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
				$Application        = new Application(
					function ($Composer) use ($Event, &$Application) {
						$Event->fire(
							'Composer/Composer',
							[
								'Application' => $Application,
								'Composer'    => $Composer
							]
						);
					}
				);
				$verbosity          = !DEBUG && $Config->core['simple_admin_mode'] ? '-vv' : '-vvv';
				$input              = new ArrayInput(
					[
						'command'       => 'update',
						'--working-dir' => "$storage/tmp",
						'--no-dev'      => true,
						'--ansi'        => true,
						'--prefer-dist' => true,
						$verbosity      => true
					]
				);
				$output             = new Output;
				$output->set_stream(fopen("$storage/last_execution.log", 'w'));
				$Application->setAutoExit(false);
				$status_code = $Application->run($input, $output);
				$description = $output->fetch();
				if ($status_code == 0) {
					rmdir_recursive("$storage/vendor");
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
			rmdir_recursive("$storage/vendor");
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
			/** @noinspection MkdirRaceConditionInspection */
			@mkdir($storage, 0770);
		}
		rmdir_recursive("$storage/home");
		/** @noinspection MkdirRaceConditionInspection */
		@mkdir("$storage/home", 0770);
		rmdir_recursive("$storage/tmp");
		/** @noinspection MkdirRaceConditionInspection */
		@mkdir("$storage/tmp", 0770);
		putenv("COMPOSER_HOME=$storage/home");
		@ini_set('display_errors', 1);
		@ini_set('memory_limit', '512M');
		@unlink("$storage/last_execution.log");
	}
	/**
	 * @param string $component_name
	 * @param string $category `modules` or `plugins`
	 * @param int    $mode     `self::MODE_ADD` or `self::MODE_DELETE`
	 *
	 * @return array Resulting `composer.json` structure in form of array
	 */
	protected function generate_composer_json ($component_name, $category, $mode) {
		$composer = [
			'repositories' => [],
			'require'      => []
		];
		$Config   = Config::instance();
		foreach (array_keys($Config->components['modules']) as $module) {
			if (
				$module == $component_name &&
				$category == 'modules' &&
				$mode == self::MODE_DELETE
			) {
				continue;
			}
			if (
				file_exists(MODULES."/$module/meta.json") &&
				(
					!$Config->module($module)->uninstalled() ||
					(
						$component_name == $module &&
						$category == 'modules'
					)
				)
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
				$category == 'plugins' && $component_name ? [$component_name] : []
			) as $plugin
		) {
			if (
				$plugin == $component_name &&
				$category == 'plugins' &&
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
			'type'    => 'metapackage',
			'require' => isset($meta['require_composer']) ? $meta['require_composer'] : []
		];
		if ($meta['package'] == 'Composer') {
			$package['replace'] = file_get_json(__DIR__.'/packages_bundled_with_system.json');
		}
		Event::instance()->fire(
			'Composer/generate_package',
			[
				'package' => &$package,
				'meta'    => $meta
			]
		);
		if (!$package['require'] && !isset($package['replace'])) {
			return;
		}
		$composer['repositories'][] = [
			'type'    => 'package',
			'package' => $package
		];
		/**
		 * @alpha in order to ignore stability issue: https://github.com/composer/composer/issues/4889
		 */
		$composer['require'][$package_name] = "$meta[version]@alpha";
	}
	/**
	 * @param string $storage
	 */
	protected function cleanup ($storage) {
		rmdir_recursive("$storage/home");
		rmdir_recursive("$storage/tmp");
	}
}
