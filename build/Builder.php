<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Builder
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
use
	h,
	Phar;

class Builder {
	/**
	 * @var string
	 */
	protected $target;
	/**
	 * @param string $target
	 */
	function __construct ($target) {
		$this->target = $target;
	}
	/**
	 * @return string
	 */
	function form () {
		return h::{'form[method=post]'}(
			h::nav(
				'Build: '.
				h::{'radio.build-mode[name=mode]'}(
					[
						'value'   => ['core', 'module', 'plugin', 'theme'],
						'in'      => ['Core', 'Module', 'Plugin', 'Theme'],
						'onclick' => 'change_mode(this.value, this);'
					]
				)
			).
			h::{'table tr| td'}(
				[
					'Modules',
					'Plugins',
					'Themes'
				],
				[
					h::{'select#modules[name=modules[]][size=20][multiple] option'}(
						array_map(
							function ($module) {
								return [
									$module,
									file_exists(DIR."/components/modules/$module/meta.json") ? [
										'title' => 'Version: '.file_get_json(DIR."/components/modules/$module/meta.json")['version']
									] : [
										'title' => 'No meta.json file found',
										'disabled'
									]
								];
							},
							get_files_list(DIR.'/components/modules', '/[^System)]/', 'd')
						)
					),
					h::{'select#plugins[name=plugins[]][size=20][multiple] option'}(
						array_map(
							function ($plugin) {
								return [
									$plugin,
									file_exists(DIR."/components/plugins/$plugin/meta.json") ? [
										'title' => 'Version: '.file_get_json(DIR."/components/plugins/$plugin/meta.json")['version']
									] : [
										'title' => 'No meta.json file found',
										'disabled'
									]
								];
							},
							get_files_list(DIR.'/components/plugins', false, 'd')
						)
					),
					h::{'select#themes[name=themes[]][size=20][multiple] option'}(
						array_map(
							function ($theme) {
								return [
									$theme,
									file_exists(DIR."/themes/$theme/meta.json") ? [
										'title' => 'Version: '.file_get_json(DIR."/themes/$theme/meta.json")['version']
									] : [
										'title' => 'No meta.json file found',
										'disabled'
									]
								];
							},
							get_files_list(DIR.'/themes', '/[^CleverStyle)]/', 'd')
						)
					)
				]
			).
			h::{'input[name=suffix]'}(
				[
					'placeholder' => 'Package file suffix'
				]
			).
			h::{'button.uk-button.license'}(
				'License',
				[
					'onclick' => "window.open('license.txt', 'license', 'location=no')"
				]
			).
			h::{'button.uk-button[type=submit]'}(
				'Build'
			)
		);
	}
	/**
	 * @return string
	 */
	function core () {
		time_limit_pause();
		$version = file_get_json(DIR.'/components/modules/System/meta.json')['version'];
		if (file_exists(DIR.'/build.phar')) {
			unlink(DIR.'/build.phar');
		}
		$phar   = new Phar(DIR.'/build.phar');
		$length = mb_strlen(DIR.'/');
		foreach (get_files_list(DIR.'/install', false, 'f', true, true) as $file) {
			$phar->addFile($file, mb_substr($file, $length));
		}
		unset($file);
		/**
		 * Files to be included into installation package
		 */
		$list = array_merge(
			get_files_list(DIR.'/components/modules/System', false, 'f', true, true, false, false, true),
			get_files_list(DIR.'/core', false, 'f', true, true, false, false, true),
			get_files_list(DIR.'/custom', false, 'f', true, true, false, false, true),
			get_files_list(DIR.'/includes', false, 'f', true, true, false, false, true),
			get_files_list(DIR.'/templates', false, 'f', true, true, false, false, true),
			get_files_list(DIR.'/themes/CleverStyle', false, 'f', true, true, false, false, true),
			[
				DIR.'/components/blocks/.gitkept',
				DIR.'/components/plugins/.gitkept',
				DIR.'/index.php',
				DIR.'/license.txt',
				DIR.'/Storage.php'
			]
		);
		/**
		 * If composer.json exists - include it into installation build
		 */
		if (file_exists(DIR.'/composer.json')) {
			$list[] = DIR.'/composer.json';
		}
		/**
		 * If composer.lock exists - include it into installation build
		 */
		if (file_exists(DIR.'/composer.lock')) {
			$list[] = DIR.'/composer.lock';
		}
		/**
		 * Add selected modules that should be built-in into package
		 */
		$components_list = [];
		if (@$_POST['modules']) {
			foreach ($_POST['modules'] as $i => $module) {
				if ($module != 'System' && is_dir(DIR."/components/modules/$module") && file_exists(DIR."/components/modules/$module/meta.json")) {
					@unlink(DIR."/components/modules/$module/fs.json");
					$list_ = get_files_list(DIR."/components/modules/$module", false, 'f', true, true, false, false, true);
					file_put_json(
						DIR."/components/modules/$module/fs.json",
						array_values(
							_mb_substr(
								$list_,
								mb_strlen(DIR."/components/modules/$module/")
							)
						)
					);
					$list_[]         = DIR."/components/modules/$module/fs.json";
					$components_list = array_merge(
						$components_list,
						$list_
					);
					unset($list_);
				} else {
					unset($_POST['modules'][$i]);
				}
			}
			unset($i, $module);
			$phar->addFromString('modules.json', _json_encode($_POST['modules']));
		}
		/**
		 * Add selected plugins that should be built-in into package
		 */
		if (@$_POST['plugins']) {
			foreach ($_POST['plugins'] as $plugin) {
				if (is_dir(DIR."/components/plugins/$plugin") && file_exists(DIR."/components/plugins/$plugin/meta.json")) {
					@unlink(DIR."/components/plugins/$plugin/fs.json");
					$list_ = get_files_list(DIR."/components/plugins/$plugin", false, 'f', true, true, false, false, true);
					file_put_json(
						DIR."/components/plugins/$plugin/fs.json",
						array_values(
							_mb_substr(
								$list_,
								mb_strlen(DIR."/components/plugins/$plugin/")
							)
						)
					);
					$list_[]         = DIR."/components/plugins/$plugin/fs.json";
					$components_list = array_merge(
						$components_list,
						$list_
					);
					unset($list_);
				}
			}
			unset($plugin);
		}
		/**
		 * Add selected themes that should be built-in into package
		 */
		if (@$_POST['themes']) {
			foreach ($_POST['themes'] as $theme) {
				if (is_dir(DIR."/themes/$theme") && file_exists(DIR."/themes/$theme/meta.json")) {
					@unlink(DIR."/themes/$theme/fs.json");
					$list_ = get_files_list(DIR."/themes/$theme", false, 'f', true, true, false, false, true);
					file_put_json(
						DIR."/themes/$theme/fs.json",
						array_values(
							_mb_substr(
								$list_,
								mb_strlen(DIR."/themes/$theme/")
							)
						)
					);
					$list_[]         = DIR."/themes/$theme/fs.json";
					$components_list = array_merge(
						$components_list,
						$list_
					);
					unset($list_);
				}
			}
			unset($theme);
		}
		/**
		 * Joining system and components files list
		 */
		$list = array_merge(
			$list,
			$components_list
		);
		/**
		 * Addition files content into package
		 */
		$list = array_map(
			function ($index, $file) use ($phar, $length) {
				$phar->addFromString("fs/$index", file_get_contents($file));
				return substr($file, $length);
			},
			array_keys($list),
			$list
		);
		/**
		 * Addition of separate files into package
		 */
		$list[] = 'readme.html';
		$phar->addFromString(
			'fs/'.(count($list) - 1),
			str_replace(
				[
					'$version$',
					'$image$'
				],
				[
					$version,
					h::img(
						[
							'src' => 'data:image/png;charset=utf-8;base64,'.base64_encode(file_get_contents(DIR.'/install/logo.png'))
						]
					)
				],
				file_get_contents(DIR.'/readme.html')
			)
		);
		$phar->addFromString(
			'languages.json',
			_json_encode(
				array_merge(
					_mb_substr(get_files_list(DIR.'/core/languages', '/^.*?\.php$/i', 'f'), 0, -4) ?: [],
					_mb_substr(get_files_list(DIR.'/core/languages', '/^.*?\.json$/i', 'f'), 0, -5) ?: []
				)
			)
		);
		$phar->addFromString(
			'db_engines.json',
			_json_encode(
				_mb_substr(get_files_list(DIR.'/core/engines/DB', '/^[^_].*?\.php$/i', 'f'), 0, -4)
			)
		);
		/**
		 * Fixing of system files list (without components files and core/fs.json file itself), it is needed for future system updating
		 */
		$list[] = 'core/fs.json';
		$phar->addFromString(
			'fs/'.(count($list) - 1),
			_json_encode(
				array_flip(array_diff(array_slice($list, 0, -1), _substr($components_list, $length)))
			)
		);
		unset($components_list, $length);
		/**
		 * Addition of files, that are needed only for installation
		 */
		$list[] = '.htaccess';
		$phar->addFromString(
			'fs/'.(count($list) - 1),
			'AddDefaultCharset utf-8
Options -Indexes -Multiviews +FollowSymLinks
IndexIgnore *.php *.pl *.cgi *.htaccess *.htpasswd

RewriteEngine On
RewriteBase /

<FilesMatch ".*/.*">
	Options -FollowSymLinks
</FilesMatch>
<FilesMatch "\.(css|js|gif|jpg|jpeg|png|ico|eot|ttc|ttf|svg|svgz|woff)$">
	RewriteEngine Off
</FilesMatch>
<Files license.txt>
	RewriteEngine Off
</Files>
#<Files Storage.php>
#	RewriteEngine Off
#</Files>

RewriteRule .* index.php
'
		);
		$list[] = 'config/main.php';
		$phar->addFromString(
			'fs/'.(count($list) - 1),
			file_get_contents(DIR.'/config/main.php')
		);
		$list[] = 'favicon.ico';
		$phar->addFromString(
			'fs/'.(count($list) - 1),
			file_get_contents(DIR.'/favicon.ico')
		);
		$list[] = '.gitignore';
		$phar->addFromString(
			'fs/'.(count($list) - 1),
			file_get_contents(DIR.'/.gitignore')
		);
		/**
		 * Flip array to have direct access to files by name during extracting and installation, and fixing of files list for installation
		 */
		$phar->addFromString(
			'fs.json',
			_json_encode(
				array_flip($list)
			)
		);
		unset($list);
		/**
		 * Addition of supplementary files, that are needed directly for installation process: installer with GUI interface, readme, license, some additional
		 * information about available languages, themes, current version of system
		 */
		$phar->addFromString(
			'install.php',
			str_replace('$version$', $version, file_get_contents(DIR.'/install.php'))
		);
		$phar->addFromString(
			'readme.html',
			str_replace(
				[
					'$version$',
					'$image$'
				],
				[
					$version,
					h::img(
						[
							'src' => 'data:image/png;charset=utf-8;base64,'.base64_encode(file_get_contents(DIR.'/install/logo.png'))
						]
					)
				],
				file_get_contents(DIR.'/readme.html')
			)
		);
		$phar->addFromString(
			'license.txt',
			file_get_contents(DIR.'/license.txt')
		);
		$themes = get_files_list(DIR.'/themes', false, 'd');
		asort($themes);
		$phar->addFromString(
			'themes.json',
			_json_encode($themes)
		);
		$phar->addFromString(
			'version',
			"\"$version\""
		);
		unset($themes, $theme);
		$phar->setStub(
			"<?php
		if (PHP_SAPI == 'cli') {
			Phar::mapPhar('cleverstyle_cms.phar');
			include 'phar://cleverstyle_cms.phar/install.php';
		} else {
			Phar::webPhar(null, 'install.php');
		}
		__HALT_COMPILER();"
		);
		unset($phar);
		$suffix = @$_POST['suffix'] ? "_$_POST[suffix]" : '';
		rename(DIR.'/build.phar', DIR."/CleverStyle_CMS_$version$suffix.phar.php");
		return "Done! CleverStyle CMS $version";
	}
	/**
	 * @param string $module
	 *
	 * @return string
	 */
	function module ($module) {
		$module = $module ?: $_POST['modules'][0];
		if ($module == 'System') {
			return "Can't build module, System module is a part of core, it is not necessary to build it as separate module";
		}
		return $this->generic_package_creation(DIR."/components/modules/$module", @$_POST['suffix']);
	}
	/**
	 * @param string $plugin
	 *
	 * @return string
	 */
	function plugin ($plugin) {
		$plugin = $plugin ?: $_POST['plugins'][0];
		return $this->generic_package_creation(DIR."/components/plugins/$plugin", @$_POST['suffix']);
	}
	/**
	 * @param string $theme
	 *
	 * @return string
	 */
	function theme ($theme) {
		$theme = $theme ?: $_POST['themes'][0];
		if ($theme == 'CleverStyle') {
			return "Can't build theme, CleverStyle theme is a part of core, it is not necessary to build it as separate theme";
		}
		return $this->generic_package_creation(DIR."/themes/$theme", @$_POST['suffix']);
	}
	protected function generic_package_creation ($source_dir, $suffix = null) {
		if (file_exists("$this->target/build.phar")) {
			unlink("$this->target/build.phar");
		}
		if (!file_exists("$source_dir/meta.json")) {
			$component = basename($source_dir);
			return "Can't build $component, meta information (meta.json) not found";
		}
		$meta   = file_get_json("$source_dir/meta.json");
		$phar   = new Phar("$this->target/build.phar");
		$list   = get_files_list($source_dir, false, 'f', true, true, false, false, true);
		$length = mb_strlen("$source_dir/");
		$list   = array_map(
			function ($index, $file) use ($phar, $length) {
				$phar->addFromString("fs/$index", file_get_contents($file));
				return mb_substr($file, $length);
			},
			array_keys($list),
			$list
		);
		unset($length);
		/**
		 * Flip array to have direct access to files by name during extraction
		 */
		$phar->addFromString(
			'fs.json',
			_json_encode(
				array_flip($list)
			)
		);
		unset($list);
		$phar->addFromString('meta.json', _json_encode($meta));
		//TODO remove in future versions
		$phar->addFromString('dir', $meta['package']);
		$readme = false;
		if (file_exists("$source_dir/readme.html")) {
			$phar->addFromString('readme.html', file_get_contents("$source_dir/readme.html"));
			$readme = 'readme.html';
		} elseif (file_exists("$source_dir/readme.txt")) {
			$phar->addFromString('readme.txt', file_get_contents("$source_dir/readme.txt"));
			$readme = 'readme.txt';
		}
		if ($readme) {
			$phar->setStub("<?php Phar::webPhar(null, '$readme'); __HALT_COMPILER();");
		} else {
			$phar->addFromString('index.html', isset($meta['description']) ? $meta['description'] : $meta['package']);
			$phar->setStub("<?php Phar::webPhar(null, 'index.html'); __HALT_COMPILER();");
		}
		unset($readme, $phar);
		$suffix = $suffix ? "_$suffix" : '';
		$type   = 'CleverStyle_CMS';
		$Type   = 'CleverStyle CMS';
		switch ($meta['category']) {
			case 'modules':
				$type = 'module';
				$Type = 'Module';
				break;
			case 'plugins':
				$type = 'plugins';
				$Type = 'Plugin';
				break;
			case 'themes':
				$type = 'theme';
				$Type = 'Theme';
				break;
		}
		rename("$this->target/build.phar", "$this->target/{$type}_$meta[package]_$meta[version]$suffix.phar.php");
		return "Done! $Type $meta[package] $meta[version]";
	}
}
