<?php
/**
 * @package   Composer assets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer_assets;
use
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Request,
	cs\Response,
	Fxp\Composer\AssetPlugin\FxpAssetPlugin;

Event::instance()
	->on(
		'admin/System/modules/uninstall/after',
		function ($data) {
			if ($data['name'] === 'Composer_assets') {
				rmdir_recursive(PUBLIC_STORAGE.'/Composer_assets');
			}
		}
	)
	->on(
		'System/Request/routing_replace/before',
		function ($data) {
			if (!Config::instance()->module('Composer_assets')->enabled()) {
				return;
			}
			if (
				Request::instance()->method == 'GET' &&
				(
					strpos($data['rc'], 'bower_components/') === 0 ||
					strpos($data['rc'], 'node_modules/') === 0
				)
			) {
				$composer_lock   = @file_get_json(STORAGE.'/Composer/composer.lock');
				$target_location = str_replace(
					['bower_components', 'node_modules'],
					['storage/Composer/vendor/bower-asset', 'storage/Composer/vendor/npm-asset'],
					$data['rc']
				);
				Response::instance()->redirect("/$target_location?$composer_lock[hash]", 301);
				throw new ExitException;
			}
		}
	)
	->on(
		'Composer/generate_package',
		function ($data) {
			if (!Config::instance()->module('Composer_assets')->enabled()) {
				return;
			}
			if (isset($data['meta']['require_bower']) && $data['meta']['require_bower']) {
				foreach ((array)$data['meta']['require_bower'] as $package => $version) {
					$data['package']['require']["bower-asset/$package"] = isset($version['version']) ? $version['version'] : $version;
				}
			}
			if (isset($data['meta']['require_npm']) && $data['meta']['require_npm']) {
				foreach ((array)$data['meta']['require_npm'] as $package => $version) {
					$data['package']['require']["npm-asset/$package"] = isset($version['version']) ? $version['version'] : $version;
				}
			}
			if ($data['meta']['package'] === 'Composer_assets') {
				$data['package']['replace'] = file_get_json(__DIR__.'/packages_bundled_with_system.json');
			}
		}
	)
	->on(
		'Composer/Composer',
		function ($data) {
			if (!Config::instance()->module('Composer_assets')->enabled()) {
				return;
			}
			/**
			 * @var \cs\modules\Composer\Application $Application
			 */
			$Application = $data['Application'];
			/**
			 * @var \Composer\Composer $Composer
			 */
			$Composer = $data['Composer'];
			/**
			 * Fail silently instead of breaking everything
			 */
			if (class_exists('Fxp\\Composer\\AssetPlugin\\FxpAssetPlugin')) {
				(new FxpAssetPlugin)->activate($Composer, $Application->getIO());
			} else {
				/**
				 * @var \Composer\IO\IOInterface $io
				 */
				$io = $Application->getIO();
				$io->writeError(
					"<error>Fxp\\Composer\\AssetPlugin\\FxpAssetPlugin class not available, Composer assets plugin can't work, update Composer once again after finish</error>",
					true
				);
			}
		}
	)
	->on(
		'System/Page/assets_dependencies_and_map',
		function ($data) {
			if (!Config::instance()->module('Composer_assets')->enabled()) {
				return;
			}
			$composer_dir        = STORAGE.'/Composer';
			$composer_assets_dir = PUBLIC_STORAGE.'/Composer_assets';
			$composer_lock       = @file_get_json("$composer_dir/composer.lock");
			if (!$composer_lock) {
				return;
			}
			rmdir_recursive($composer_assets_dir);
			/** @noinspection MkdirRaceConditionInspection */
			@mkdir($composer_assets_dir, 0770);
			$dependencies = [];
			$assets_map = [];
			foreach ($composer_lock['packages'] as $package) {
				$package_name = $package['name'];
				if (preg_match('#^modules/#', $package_name)) {
					$package_name = explode('/', $package_name, 2)[1];
				} elseif (strpos($package_name, '-asset/') !== false) {
					$package_name = str_replace('-asset/', '-asset-', $package_name);
				} else {
					// Ignore other Composer packages here
					continue;
				}
				$package += [
					'require' => [],
					'provide' => [],
					'replace' => []
				];
				foreach (array_keys($package['require']) as $r) {
					if (strpos($r, '-asset/') !== false) {
						$dependencies[$package_name][] = str_replace('-asset/', '-asset-', $r);
					}
				}
				foreach (array_keys($package['provide']) as $p) {
					$dependencies[$p][] = $package_name;
				}
				foreach (array_keys($package['replace']) as $r) {
					$dependencies[$r][] = $package_name;
				}
				/**
				 * If current package is Bower or NPM package (we will analyse both configurations)
				 */
				if (strpos($package['name'], '-asset/') !== false) {
					$assets_map[$package_name] = Assets_processing::run($package['name'], "$composer_dir/vendor/$package[name]", $composer_assets_dir);
				}
			}
			$data['dependencies'] = array_merge_recursive($data['dependencies'], $dependencies);
			$data['assets_map'] = array_merge_recursive($data['assets_map'], $assets_map);
		}
	)
	->on(
		'Composer/updated',
		function () {
			if (!Config::instance()->module('Composer_assets')->enabled()) {
				return;
			}
			// Allow public access to assets
			$htaccess_contents = /** @lang ApacheConfig */
				<<<HTACCESS
Allow From All
<ifModule mod_expires.c>
	ExpiresActive On
	ExpiresDefault "access plus 1 month"
</ifModule>
<ifModule mod_headers.c>
	Header set Cache-Control "max-age=2592000, public"
	Header always append X-Frame-Options DENY
	Header set Content-Type application/octet-stream
</ifModule>

HTACCESS;
			if (is_dir(STORAGE.'/Composer/vendor/bower-asset')) {
				file_put_contents(STORAGE.'/Composer/vendor/bower-asset/.htaccess', $htaccess_contents);
			}
			if (is_dir(STORAGE.'/Composer/vendor/npm-asset')) {
				file_put_contents(STORAGE.'/Composer/vendor/npm-asset/.htaccess', $htaccess_contents);
			}
			if (clean_public_cache()) {
				Event::instance()->fire('admin/System/general/optimization/clean_public_cache');
			}
		}
	)
	->on(
		'System/Page/requirejs',
		function ($data) {
			if (!Config::instance()->module('Composer_assets')->enabled()) {
				return;
			}
			$data['directories_to_browse'][] = STORAGE.'/Composer/vendor/bower-asset';
			$data['directories_to_browse'][] = STORAGE.'/Composer/vendor/npm-asset';
		}
	);
