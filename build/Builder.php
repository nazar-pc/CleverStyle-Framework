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
	 * @param string[]    $modules
	 * @param string[]    $plugins
	 * @param string[]    $themes
	 * @param null|string $suffix
	 *
	 * @return string
	 */
	function core ($modules = [], $plugins = [], $themes = [], $suffix = null) {
		$modules = $modules ?: @$_POST['modules'];
		$plugins = $plugins ?: @$_POST['plugins'];
		$themes  = $themes ?: @$_POST['themes'];
		$modules = $modules ?: [];
		$plugins = $plugins ?: [];
		$themes  = $themes ?: [];
		$suffix  = $suffix ?: @$_POST['suffix'];
		if (file_exists("$this->target/build.phar")) {
			unlink("$this->target/build.phar");
		}
		$phar   = new Phar("$this->target/build.phar");
		$length = mb_strlen(DIR.'/');
		foreach (get_files_list(DIR.'/install', false, 'f', true, true) as $file) {
			$phar->addFile($file, mb_substr($file, $length));
		}
		unset($file);
		/**
		 * Files to be included into installation package
		 */
		$core_files = $this->get_files(
			[
				DIR.'/components/modules/System',
				DIR.'/components/blocks/.gitkept',
				DIR.'/components/plugins/.gitkept',
				DIR.'/core',
				DIR.'/custom',
				DIR.'/includes',
				DIR.'/templates',
				DIR.'/themes/CleverStyle',
				DIR.'/composer.json',
				DIR.'/composer.lock',
				DIR.'/index.php',
				DIR.'/license.txt',
				DIR.'/Storage.php'
			]
		);
		/**
		 * Add modules that should be built-in into package
		 */
		$components_files = [];
		$modules          = array_filter(
			$modules,
			function ($module) use (&$components_files) {
				return $this->get_component_files(DIR."/components/modules/$module", $components_files);
			}
		);
		asort($modules);
		$phar->addFromString('modules.json', _json_encode($modules));
		/**
		 * Add plugins that should be built-in into package
		 */
		$plugins = array_filter(
			$plugins,
			function ($plugin) use (&$components_files) {
				return $this->get_component_files(DIR."/components/plugins/$plugin", $components_files);
			}
		);
		asort($plugins);
		$phar->addFromString('plugins.json', _json_encode($plugins));
		/**
		 * Add themes that should be built-in into package
		 */
		$themes   = array_filter(
			$themes,
			function ($theme) use (&$components_files) {
				return $this->get_component_files(DIR."/themes/$theme", $components_files);
			}
		);
		$themes[] = 'CleverStyle';
		asort($themes);
		$phar->addFromString('themes.json', _json_encode($themes));
		/**
		 * Joining system and components files
		 */
		$core_files = array_merge(
			$core_files,
			$components_files
		);
		/**
		 * Addition of files into package
		 */
		foreach ($core_files as $index => &$file) {
			$phar->addFile($file, "fs/$index");
			$file = substr($file, $length);
		}
		unset($index, $file);
		/**
		 * Addition of separate files into package
		 */
		$phar->addFromString('fs/'.count($core_files), $this->get_readme());
		$core_files[] = 'readme.html';
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
		 * Fixing of system files list (without components files), it is needed for future system updating
		 */
		$phar->addFromString(
			'fs/'.count($core_files),
			_json_encode(
				array_flip(
					array_diff(
						$core_files,
						_substr($components_files, $length)
					)
				)
			)
		);
		$core_files[] = 'core/fs.json';
		unset($components_files, $length);
		/**
		 * Addition of files, that are needed only for installation
		 */
		$phar->addFromString('fs/'.count($core_files), $this->get_htaccess());
		$core_files[] = '.htaccess';
		$phar->addFile(DIR.'/config/main.php', 'fs/'.count($core_files));
		$core_files[] = 'config/main.php';
		$phar->addFile(DIR.'/favicon.ico', 'fs/'.count($core_files));
		$core_files[] = 'favicon.ico';
		$phar->addFile(DIR.'/.gitignore', 'fs/'.count($core_files));
		$core_files[] = '.gitignore';
		/**
		 * Flip array to have direct access to files by name during extracting and installation, and fixing of files list for installation
		 */
		$phar->addFromString(
			'fs.json',
			_json_encode(
				array_flip($core_files)
			)
		);
		unset($core_files);
		/**
		 * Addition of supplementary files, that are needed directly for installation process: installer with GUI interface, readme, license, some additional
		 * information about available languages, themes, current version of system
		 */
		$phar->addFile(DIR.'/install.php', 'install.php');
		$phar->addFromString('readme.html', $this->get_readme());
		$phar->addFile(DIR.'/license.txt', 'license.txt');
		$phar->addFile(DIR.'/components/modules/System/meta.json', 'meta.json');
		$version = file_get_json(DIR.'/components/modules/System/meta.json')['version'];
		//TODO Remove in future versions
		$phar->addFromString(
			'version',
			"\"$version\""
		);
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
		$suffix = $suffix ? "_$suffix" : '';
		rename("$this->target/build.phar", DIR."/CleverStyle_CMS_$version$suffix.phar.php");
		return "Done! CleverStyle CMS $version";
	}
	/**
	 * Get array of files
	 *
	 * @param string[] $source Files and directories (absolute paths); If file does non exists - it will be skipped, if directory - all files will be returned
	 *                         instead
	 *
	 * @return array
	 */
	protected function get_files ($source) {
		$files = [];
		foreach ($source as $s) {
			if (is_file($s)) {
				$files[] = $s;
			} elseif (is_dir($s)) {
				/** @noinspection SlowArrayOperationsInLoopInspection */
				$files = array_merge(
					$files,
					get_files_list($s, false, 'f', true, true, false, false, true)
				);
			}
		}
		return $files;
	}
	/**
	 * @param string   $component_root
	 * @param string[] $files Array, where new files will be appended
	 *
	 * @return bool
	 */
	protected function get_component_files ($component_root, &$files) {
		/**
		 * Do not allow building System module and CleverStyle theme
		 */
		if (in_array(basename($component_root), ['System', 'CleverStyle'])) {
			return false;
		}
		/**
		 * Components without meta.json also not allowed
		 */
		if (!file_exists("$component_root/meta.json")) {
			return false;
		}
		@unlink("$component_root/fs.json");
		$files = array_merge(
			$files,
			get_files_list($component_root, false, 'f', true, true, false, false, true)
		);
		file_put_json(
			"$component_root/fs.json",
			array_values(
				_mb_substr(
					$files,
					mb_strlen("$component_root/")
				)
			)
		);
		$files[] = "$component_root/fs.json";
		return true;
	}
	/**
	 * @return string
	 */
	protected function get_readme () {
		return str_replace(
			[
				'$version$',
				'$image$'
			],
			[
				file_get_json(DIR.'/components/modules/System/meta.json')['version'],
				h::img(
					[
						'src' => 'data:image/png;charset=utf-8;base64,'.base64_encode(file_get_contents(DIR.'/install/logo.png'))
					]
				)
			],
			file_get_contents(DIR.'/readme.html')
		);
	}
	/**
	 * @return string
	 */
	protected function get_htaccess () {
		return 'AddDefaultCharset utf-8
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
';
	}
	/**
	 * @param string      $module
	 * @param null|string $suffix
	 *
	 * @return string
	 */
	function module ($module, $suffix = null) {
		$module = $module ?: $_POST['modules'][0];
		$suffix = $suffix ?: $_POST['suffix'];
		if ($module == 'System') {
			return "Can't build module, System module is a part of core, it is not necessary to build it as separate module";
		}
		return $this->generic_package_creation(DIR."/components/modules/$module", $suffix);
	}
	/**
	 * @param string      $plugin
	 * @param null|string $suffix
	 *
	 * @return string
	 */
	function plugin ($plugin, $suffix = null) {
		$plugin = $plugin ?: $_POST['plugins'][0];
		$suffix = $suffix ?: $_POST['suffix'];
		return $this->generic_package_creation(DIR."/components/plugins/$plugin", $suffix);
	}
	/**
	 * @param string      $theme
	 * @param null|string $suffix
	 *
	 * @return string
	 */
	function theme ($theme, $suffix = null) {
		$theme  = $theme ?: $_POST['themes'][0];
		$suffix = $suffix ?: $_POST['suffix'];
		if ($theme == 'CleverStyle') {
			return "Can't build theme, CleverStyle theme is a part of core, it is not necessary to build it as separate theme";
		}
		return $this->generic_package_creation(DIR."/themes/$theme", $suffix);
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
		$files  = get_files_list($source_dir, false, 'f', true, true, false, false, true);
		$length = strlen("$source_dir/");
		foreach ($files as $index => &$file) {
			$phar->addFile($file, "fs/$index");
			$file = substr($file, $length);
		}
		unset($index, $file, $length);
		/**
		 * Flip array to have direct access to files by name during extraction
		 */
		$phar->addFromString(
			'fs.json',
			_json_encode(
				array_flip($files)
			)
		);
		unset($files);
		$phar->addFile("$source_dir/meta.json", 'meta.json');
		//TODO remove in future versions
		$phar->addFromString('dir', $meta['package']);
		$readme = false;
		if (file_exists("$source_dir/readme.html")) {
			$phar->addFile("$source_dir/readme.html", 'readme.html');
			$readme = 'readme.html';
		} elseif (file_exists("$source_dir/readme.txt")) {
			$phar->addFile("$source_dir/readme.txt", 'readme.txt');
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
		$type   = '';
		$Type   = '';
		switch ($meta['category']) {
			case 'modules':
				$type = 'module_';
				$Type = 'Module';
				break;
			case 'plugins':
				$type = 'plugins_';
				$Type = 'Plugin';
				break;
			case 'themes':
				$type = 'theme_';
				$Type = 'Theme';
				break;
		}
		rename("$this->target/build.phar", "$this->target/$type$meta[package]_$meta[version]$suffix.phar.php");
		return "Done! $Type $meta[package] $meta[version]";
	}
}
