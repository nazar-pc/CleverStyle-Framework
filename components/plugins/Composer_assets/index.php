<?php
/**
 * @package   Composer assets
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\plugins\Composer_assets;
use
	cs\Event,
	cs\ExitException,
	Fxp\Composer\AssetPlugin\FxpAssetPlugin;

/**
 * @var \cs\_SERVER $_SERVER
 */
if (
	$_SERVER->request_method == 'GET' &&
	(
		strpos($_SERVER->request_uri, '/bower_components') === 0 ||
		strpos($_SERVER->request_uri, '/node_modules') === 0
	)
) {
	$composer_lock   = @file_get_json(STORAGE.'/Composer/composer.lock');
	$target_location = str_replace(
		['/bower_components', '/node_modules'],
		['/storage/Composer/vendor/bower-asset', '/storage/Composer/vendor/npm-asset'],
		$_SERVER->request_uri
	);
	_header("Location: $target_location?$composer_lock[hash]", true, 301);
	throw new ExitException(301);
}

Event::instance()
	->on(
		'Composer/generate_package',
		function ($data) {
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
			/**
			 * @var \cs\modules\Composer\Application $Application
			 */
			$Application = $data['Application'];
			/**
			 * @var \Composer\Composer $Composer
			 */
			$Composer = $data['Composer'];
			(new FxpAssetPlugin)->activate($Composer, $Application->getIO());
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
			rmdir_recursive($composer_assets_dir);
			@mkdir($composer_assets_dir, 0770);
			file_put_contents(
				"$composer_assets_dir/require.js",
				'requirejs.config({paths : '._json_encode(Assets_processing::get_requirejs_paths()).'});'
			);
			$dependencies = [];
			$includes_map = [];
			foreach ($composer_lock['packages'] as $package) {
				$package_name = $package['name'];
				if (strpos($package_name, '/') !== false) {
					$package_name = explode('/', $package_name, 2)[1];
				}
				if (isset($package['require'])) {
					foreach (array_keys($package['require']) as $r) {
						if (strpos($r, '-asset/') !== false) {
							$r = explode('/', $r, 2)[1];
						}
						$dependencies[$package_name][] = $r;
					}
				}
				if (isset($package['provide'])) {
					foreach (array_keys($package['provide']) as $p) {
						$dependencies[$p][] = $package_name;
					}
				}
				if (isset($package['replace'])) {
					foreach (array_keys($package['replace']) as $r) {
						$dependencies[$r][] = $package_name;
					}
				}
				/**
				 * If current package is Bower or NPM package (we will analyse both configurations)
				 */
				if (strpos($package['name'], '-asset/') !== false) {
					$package_dir = "$composer_dir/vendor/$package[name]";
					$target_dir  = "$composer_assets_dir/$package_name";
					Assets_processing::run($package_name, $package_dir, $target_dir, $includes_map);
				}
			}
			$data['dependencies'] = array_merge_recursive($data['dependencies'], $dependencies);
			$data['includes_map'] = array_merge_recursive($data['includes_map'], $includes_map);
			// Hack: the whole thing is used to insert after last element from `/includes/js`, but before those of other components
			$offset = count(
				array_filter(
					$data['includes_map']['']['js'],
					function ($file) {
						return strpos($file, DIR.'/includes') === 0;
					}
				)
			);
			array_splice($data['includes_map']['']['js'], $offset, 0, "$composer_assets_dir/require.js");
		}
	)
	->on(
		'Composer/updated',
		function () {
			// Allow public access to assets
			$htaccess_contents = 'Allow From All
<ifModule mod_headers.c>
	Header always append X-Frame-Options DENY
	Header set Content-Type application/octet-stream
</ifModule>
<ifModule mod_expires.c>
	ExpiresActive On
	ExpiresDefault "access plus 1 month"
</ifModule>
<ifModule mod_headers.c>
	Header set Cache-Control "max-age=2592000, public"
</ifModule>
';
			if (is_dir(STORAGE.'/Composer/vendor/bower-asset')) {
				file_put_contents(STORAGE.'/Composer/vendor/bower-asset/.htaccess', $htaccess_contents);
			}
			if (is_dir(STORAGE.'/Composer/vendor/npm-asset')) {
				file_put_contents(STORAGE.'/Composer/vendor/npm-asset/.htaccess', $htaccess_contents);
			}
			if (clean_pcache()) {
				Event::instance()->fire('admin/System/general/optimization/clean_pcache');
			}
		}
	);
