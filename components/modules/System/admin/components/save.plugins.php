<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Provides next triggers:<br>
 *  admin/System/components/plugins/enable<br>
 *  ['name'	=> <i>plugin_name</i>]<br>
 *  admin/System/components/plugins/disable<br>
 *  ['name'	=> <i>plugin_name</i>]
 */
global $Config, $Index, $Core, $Page, $L, $User;
$rc			= $Config->route;
$plugins	= get_files_list(PLUGINS, false, 'd');
if (isset($_POST['mode'], $_POST['plugin'])) {
	$plugin	= $_POST['plugin'];
	switch ($_POST['mode']) {
		case 'enable':
			if (!in_array($plugin, $Config->components['plugins']) && in_array($plugin, $plugins)) {
				$Config->components['plugins'][] = $plugin;
				$Index->save();
				$Core->run_trigger(
					'admin/System/components/plugins/enable',
					[
						'name' => $plugin
					]
				);
			}
		break;
		case 'disable':
			if (in_array($plugin, $Config->components['plugins'])) {
				unset($Config->components['plugins'][array_search($plugin, $Config->components['plugins'])]);
				$Index->save();
				$Core->run_trigger(
					'admin/System/components/plugins/disable',
					[
						'name' => $plugin
					]
				);
			}
		break;
		case 'update':
			/**
			 * Temporary disable plugin
			 */
			$active	= in_array($plugin, $Config->components['plugins']);
			if ($active) {
				unset($Config->components['plugins'][array_search($plugin, $Config->components['plugins'])]);
				$Config->save();
				$Core->run_trigger(
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
			$tmp_dir	= 'phar://'.TEMP.'/'.$User->get_session().'_plugin_update.phar.php';
			$fs			= _json_decode(file_get_contents("$tmp_dir/fs.json"));
			$extract	= array_product(
				array_map(
					function ($index, $file) use ($tmp_dir, $plugin_dir) {
						if (
							!file_exists(pathinfo("$plugin_dir/$file", PATHINFO_DIRNAME)) &&
							!mkdir(pathinfo("$plugin_dir/$file", PATHINFO_DIRNAME), 0700, true)
						) {
							return 0;
						}
						return (int)copy("$tmp_dir/fs/$index", "$plugin_dir/$file");
					},
					$fs,
					array_keys($fs)
				)
			);
			if (!$extract) {
				$Page->warning($L->plugin_files_unpacking_error);
				break;
			}
			unset($extract);
			$tmp_file	= TEMP.'/'.$User->get_session().'_plugin_update.phar.php';
			rename($tmp_file, $tmp_file = mb_substr($tmp_file, 0, -9));
			$api_request							= $Core->api_request(
				'System/admin/update_plugin',
				[
					'package'	=> str_replace(DIR, $Config->base_url(), $tmp_file)
				]
			);
			if ($api_request) {
				$success	= true;
				foreach ($api_request as $mirror => $result) {
					if ($result == 1) {
						$success	= false;
						$Page->warning($L->cant_unpack_plugin_on_mirror($mirror));
					}
				}
				if (!$success) {
					$Page->warning($L->plugin_files_unpacking_error);
					break;
				}
				unset($success, $mirror, $result);
			}
			unlink($tmp_file);
			unset($api_request, $tmp_file);
			file_put_contents($plugin_dir.'/fs.json', _json_encode($fs = array_keys($fs)));
			/**
			 * Removing of old unnecessary files and directories
			 */
			foreach (array_diff(_json_encode($plugin_dir.'/fs_old.json'), $fs) as $file) {
				$file	= "$plugin_dir/$file";
				if (file_exists($file) && is_writable($file)) {
					unlink($file);
					if (!get_files_list($dir = pathinfo($file, PATHINFO_DIRNAME))) {
						rmdir($dir);
					}
				}
			}
			unset($fs, $file, $dir);
			/**
			 * Updating of plugin
			 */
			if (file_exists($plugin_dir.'/versions.json')) {
				$old_version	= _json_decode($plugin_dir.'/meta_old.json')['version'];
				$versions		= [];
				foreach (_json_decode($plugin_dir.'/versions.json') as $version) {
					if (version_compare($old_version, $version, '<')) {
						/**
						 * PHP update script
						 */
						_include($plugin_dir.'/meta/update/'.$version.'.php', true, false);
					}
				}
			}
			unlink($plugin_dir.'/fs_old.json');
			unlink($plugin_dir.'/meta_old.json');
			/**
			 * Restore previous plugin state
			 */
			if ($active) {
				$Config->components['plugins'][]	= $plugin;
				$Config->save();
				$Core->run_trigger(
					'admin/System/components/plugins/enable',
					[
						'name' => $plugin
					]
				);
			}
			$Index->save();
		break;
	}
}