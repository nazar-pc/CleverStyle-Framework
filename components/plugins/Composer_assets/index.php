<?php
/**
 * @package   Composer assets
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\plugins\Composer_assets;
use
	cs\Event,
	Fxp\Composer\AssetPlugin\FxpAssetPlugin;
Event::instance()
	->on(
		'Composer/generate_package',
		function ($data) {
			if (isset($data['meta']['require_bower']) && $data['meta']['require_bower']) {
				foreach ((array)$data['meta']['require_bower'] as $package => $version) {
					$data['package']['require']["bower-asset/$package"] = $version;
				}
			}
			if (isset($data['meta']['require_npm']) && $data['meta']['require_npm']) {
				foreach ((array)$data['meta']['require_npm'] as $package => $version) {
					$data['package']['require']["npm-asset/$package"] = $version;
				}
			}
		}
	)
	->on(
		'Composer/Composer',
		function ($data) {
			/**
			 * @var \Composer\Composer $Composer
			 */
			$Composer = $data['Composer'];
			/**
			 * @var \Composer\Plugin\PluginManager $PluginManager
			 */
			$PluginManager = $Composer->getPluginManager();
			$PluginManager->addPlugin(new FxpAssetPlugin);
		}
	)
	->on(
		'System/Page/includes_dependencies_and_map',
		function ($data) {
			$composer_dir        = STORAGE.'/Composer';
			$composer_assets_dir = PUBLIC_STORAGE.'/Composer_assets';
			$composer_lock       = @file_get_json("$composer_dir/composer.lock");
			if (!$composer_lock) {
				return;
			}
			if (file_exists("$composer_assets_dir/$composer_lock[hash].json")) {
				list($dependencies, $includes_map) = file_get_json("$composer_assets_dir/$composer_lock[hash].json");
			} else {
				rmdir_recursive($composer_assets_dir);
				mkdir($composer_assets_dir, 0770);
				$dependencies    = [];
				$includes_map    = [];
				$ignore_packages = file_get_json(__DIR__.'/ignore_packages.json');
				foreach ($composer_lock['packages'] as $package) {
					$package_name = $package['name'];
					/**
					 * Ignore packages already bundled with system core
					 */
					if (in_array(
						strtolower(explode('/', $package_name)[1]),
						$ignore_packages,
						true
					)) {
						continue;
					}
					/**
					 * System package, for consistency with system's internals prefix should be removed in dependencies
					 */
					if (
						strpos($package_name, 'modules/') === 0 ||
						strpos($package_name, 'plugins/') === 0
					) {
						$package_name = explode('/', $package_name, 2)[1];
					}
					if (isset($package['require'])) {
						foreach (array_keys($package['require']) as $r) {
							$dependencies[$package_name][] = $r;
						}
						unset($r);
					}
					if (isset($package['provide'])) {
						foreach (array_keys($package['provide']) as $p) {
							$dependencies[$p][] = $package_name;
						}
						unset($p);
					}
					if (isset($package['replace'])) {
						foreach (array_keys($package['replace']) as $r) {
							$dependencies[$r][] = $package_name;
						}
						unset($r);
					}
					/**
					 * If current package is Bower or NPM package (we will analyse both configurations)
					 */
					if (strpos($package_name, '-asset/') !== false) {
						$package_dir = "$composer_dir/vendor/$package_name";
						$target_dir  = "$composer_assets_dir/$package_name";
						mkdir($target_dir, 0770, true);
						$Assets_processing = new Assets_processing($package_name, $package_dir, $target_dir, $includes_map);
						/**
						 * Bower is preferable for frontend, obviously
						 */
						if (file_exists("$package_dir/bower.json")) {
							$bower = file_get_json("$package_dir/bower.json");
							if (isset($bower['main'])) {
								$Assets_processing->add($bower['main']);
								continue;
							}
						}
						/**
						 * But sometimes NPM is also acceptable
						 */
						if (file_exists("$package_dir/package.json")) {
							$npm = file_get_json("$package_dir/package.json");
							/**
							 * CSS is not the best friend of NPM, but sometimes such keys might happen
							 */
							if (isset($npm['style']) && is_string($npm['style'])) {
								$Assets_processing->add($npm['style']);
							} elseif (isset($npm['less']) && is_string($npm['less'])) {
								$Assets_processing->add($npm['less']);
							}
							if (isset($npm['browser']) && is_string($npm['browser'])) {
								$Assets_processing->add($npm['browser']);
								continue;
							}
							if (isset($npm['main']) && is_string($npm['main'])) {
								$Assets_processing->add($npm['main']);
								continue;
							}
						}
					}
				}
				file_put_json("$composer_assets_dir/$composer_lock[hash].json", [$dependencies, $includes_map]);
			}
			$data['dependencies'] = array_merge_recursive($data['dependencies'], $dependencies);
			$data['includes_map'] = array_merge_recursive($data['includes_map'], $includes_map);
		}
	);
