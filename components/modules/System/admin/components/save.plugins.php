<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Provides next triggers:<br>
 *  admin/System/components/plugins/enable<br>
 *  ['name'	=> <i>plugin_name</i>]<br>
 *  admin/System/components/plugins/disable<br>
 *  ['name'	=> <i>plugin_name</i>]
 */
namespace	cs;
$Cache		= Cache::instance();
$Config		= Config::instance();
$Index		= Index::instance();
$L			= Language::instance();
$Page		= Page::instance();
$plugins	= get_files_list(PLUGINS, false, 'd');
if (isset($_POST['mode'], $_POST['plugin'])) {
	$plugin	= $_POST['plugin'];
	switch ($_POST['mode']) {
		case 'enable':
			if (!in_array($plugin, $Config->components['plugins']) && in_array($plugin, $plugins)) {
				$Config->components['plugins'][] = $plugin;
				$Index->save();
				clean_pcache();
				Trigger::instance()->run(
					'admin/System/components/plugins/enable',
					[
						'name' => $plugin
					]
				);
				unset($Cache->functionality);
			}
			clean_classes_cache();
		break;
		case 'disable':
			if (in_array($plugin, $Config->components['plugins'])) {
				unset($Config->components['plugins'][array_search($plugin, $Config->components['plugins'])]);
				$Index->save();
				clean_pcache();
				Trigger::instance()->run(
					'admin/System/components/plugins/disable',
					[
						'name' => $plugin
					]
				);
				unset($Cache->functionality);
			}
			clean_classes_cache();
		break;
		case 'update':
			/**
			 * Temporary disable plugin
			 */
			$active	= in_array($plugin, $Config->components['plugins']);
			if ($active) {
				unset($Config->components['plugins'][array_search($plugin, $Config->components['plugins'])]);
				$Config->save();
				Trigger::instance()->run(
					'admin/System/components/plugins/disable',
					[
						'name' => $plugin
					]
				);
			}
			$plugin_dir				= PLUGINS."/$plugin";
			/**
			 * Backing up some necessary information about current version
			 */
			copy("$plugin_dir/fs.json",		"$plugin_dir/fs_old.json");
			copy("$plugin_dir/meta.json",	"$plugin_dir/meta_old.json");
			/**
			 * Extracting new versions of files
			 */
			$tmp_file	= TEMP.'/'.User::instance()->get_session().'_plugin_update.phar';
			$tmp_dir	= "phar://$tmp_file";
			$fs			= file_get_json("$tmp_dir/fs.json");
			$extract	= array_product(
				array_map(
					function ($index, $file) use ($tmp_dir, $plugin_dir) {
						if (
							!file_exists(dirname("$plugin_dir/$file")) &&
							!mkdir(dirname("$plugin_dir/$file"), 0770, true)
						) {
							return 0;
						}
						return (int)copy("$tmp_dir/fs/$index", "$plugin_dir/$file");
					},
					$fs,
					array_keys($fs)
				)
			);
			unlink($tmp_file);
			unset($tmp_file, $tmp_dir);
			if (!$extract) {
				$Page->warning($L->plugin_files_unpacking_error);
				unlink("$plugin_dir/fs_old.json");
				unlink("$plugin_dir/meta_old.json");
				break;
			}
			unset($extract);
			file_put_json("$plugin_dir/fs.json", $fs = array_keys($fs));
			/**
			 * Removing of old unnecessary files and directories
			 */
			foreach (array_diff(file_get_json("$plugin_dir/fs_old.json"), $fs) as $file) {
				$file	= "$plugin_dir/$file";
				if (file_exists($file) && is_writable($file)) {
					unlink($file);
					if (!get_files_list($dir = dirname($file))) {
						rmdir($dir);
					}
				}
			}
			unset($fs, $file, $dir);
			/**
			 * Updating of plugin
			 */
			if (file_exists("$plugin_dir/versions.json")) {
				$old_version	= file_get_json("$plugin_dir/meta_old.json")['version'];
				foreach (file_get_json("$plugin_dir/versions.json") as $version) {
					if (version_compare($old_version, $version, '<')) {
						/**
						 * PHP update script
						 */
						_include("$plugin_dir/meta/update/$version.php", true, false);
					}
				}
				unset($old_version);
			}
			unlink("$plugin_dir/fs_old.json");
			unlink("$plugin_dir/meta_old.json");
			/**
			 * Restore previous plugin state
			 */
			if ($active) {
				$Config->components['plugins'][]	= $plugin;
				$Config->save();
				clean_pcache();
				Trigger::instance()->run(
					'admin/System/components/plugins/enable',
					[
						'name' => $plugin
					]
				);
			}
			$Index->save();
			unset($Cache->functionality);
			clean_classes_cache();
		break;
		case 'remove':
			if (in_array($plugin, $Config->components['plugins'])) {
				break;
			}
			$ok			= true;
			get_files_list(
				PLUGINS."/$plugin",
				false,
				'fd',
				true,
				true,
				false,
				false,
				true,
				function ($item) use (&$ok) {
					if (is_writable($item)) {
						is_dir($item) ? @rmdir($item) : @unlink($item);
					} else {
						$ok = false;
					}
				}
			);
			if ($ok && @rmdir(PLUGINS."/$plugin")) {
				$Index->save();
			} else {
				$Index->save(false);
			}
		break;
	}
}
