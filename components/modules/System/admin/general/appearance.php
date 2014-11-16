<?php
/**
 * @package        CleverStyle CMS
 * @subpackage     System module
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\System;

use
	h,
	cs\Config,
	cs\Index,
	cs\Language,
	cs\Page,
	cs\User;

$Config = Config::instance();
$Index  = Index::instance();
$L      = Language::instance();
$Page   = Page::instance();

if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'upload':
			if (!isset($_FILES['upload_theme']) || !$_FILES['upload_theme']['tmp_name']) {
				break;
			}
			switch ($_FILES['upload_theme']['error']) {
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$Page->warning($L->file_too_large);
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$Page->warning($L->temporary_folder_is_missing);
					break;
				case UPLOAD_ERR_CANT_WRITE:
					$Page->warning($L->cant_write_file_to_disk);
					break;
				case UPLOAD_ERR_PARTIAL:
				case UPLOAD_ERR_NO_FILE:
					break;
			}
			if ($_FILES['upload_theme']['error'] != UPLOAD_ERR_OK) {
				break;
			}
			move_uploaded_file(
				$_FILES['upload_theme']['tmp_name'],
				$tmp_file = TEMP.'/'.md5($_FILES['upload_theme']['tmp_name'].MICROTIME).'.phar.php'
			);
			$tmp_dir = "phar://$tmp_file";
			$theme   = file_get_contents("$tmp_dir/dir");
			if (!$theme) {
				unlink($tmp_file);
				break;
			}
			if (!file_exists("$tmp_dir/meta.json") || file_get_json("$tmp_dir/meta.json")['category'] != 'themes') {
				$Page->warning($L->this_is_not_theme_installer_file);
				unlink($tmp_file);
				break;
			}
			if (in_array($theme, $Config->core['themes'])) {
				$current_version = file_get_json(THEMES."/$theme/meta.json")['version'];
				$new_version     = file_get_json("$tmp_dir/meta.json")['version'];
				if (!version_compare($current_version, $new_version, '<')) {
					$Page->warning($L->update_theme_impossible_older_version($theme));
					unlink($tmp_file);
					break;
				}
				$Page->title($L->updating_of_theme($theme));
				rename($tmp_file, $tmp_file = TEMP.'/'.User::instance()->get_session().'_theme_update.phar.php');
				$Index->content(
					h::{'h2.cs-center'}(
						$L->update_theme(
							$theme,
							$current_version,
							$new_version
						)
					).
					h::{'input[type=hidden]'}([
						'name'  => 'update_theme',
						'value' => $theme
					])
				);
				$Index->buttons				= false;
				$Index->cancel_button_back	= true;
				$Index->content(
					h::{'button.uk-button[type=submit][name=action][value=update]'}($L->yes)
				);
				return;
			}
			if (!file_exists(THEMES."/$theme") && !mkdir(THEMES."/$theme", 0770)) {
				$Page->warning($L->cant_unpack_theme_no_write_permissions);
				unlink($tmp_file);
				break;
			}
			$fs      = file_get_json("$tmp_dir/fs.json");
			$extract = array_product(
				array_map(
					function ($index, $file) use ($tmp_dir, $theme) {
						if (
							!file_exists(dirname(THEMES."/$theme/$file")) &&
							!mkdir(dirname(THEMES."/$theme/$file"), 0770, true)
						) {
							return 0;
						}
						return (int)copy("$tmp_dir/fs/$index", THEMES."/$theme/$file");
					},
					$fs,
					array_keys($fs)
				)
			);
			file_put_json(THEMES."/$theme/fs.json", array_keys($fs));
			unlink($tmp_file);
			unset($tmp_file, $tmp_dir, $theme);
			unset($tmp_dir);
			if (!$extract) {
				$Page->warning($L->theme_files_unpacking_error);
				break;
			}
			$Index->save(true);
		break;
		case 'update':
			if (!isset($_POST['update_theme'])) {
				break;
			}
			$User      = User::instance();
			$theme_dir = THEMES."/$_POST[update_theme]";
			/**
			 * Backing up some necessary information about current version
			 */
			copy("$theme_dir/fs.json", "$theme_dir/fs_old.json");
			copy("$theme_dir/meta.json", "$theme_dir/meta_old.json");
			/**
			 * Extracting new versions of files
			 */
			$tmp_file = TEMP.'/'.$User->get_session().'_theme_update.phar.php';
			$tmp_dir  = "phar://$tmp_file";
			$fs       = file_get_json("$tmp_dir/fs.json");
			$extract  = array_product(
				array_map(
					function ($index, $file) use ($tmp_dir, $theme_dir) {
						if (
							!file_exists(dirname("$theme_dir/$file")) &&
							!mkdir(dirname("$theme_dir/$file"), 0770, true)
						) {
							return 0;
						}
						return (int)copy("$tmp_dir/fs/$index", "$theme_dir/$file");
					},
					$fs,
					array_keys($fs)
				)
			);
			unlink($tmp_file);
			unset($tmp_file, $tmp_dir);
			if (!$extract) {
				$Page->warning($L->theme_files_unpacking_error);
				unlink("$theme_dir/fs_old.json");
				unlink("$theme_dir/meta_old.json");
				break;
			}
			unset($extract);
			file_put_json("$theme_dir/fs.json", $fs = array_keys($fs));
			/**
			 * Removing of old unnecessary files and directories
			 */
			foreach (array_diff(file_get_json("$theme_dir/fs_old.json"), $fs) as $file) {
				$file = "$theme_dir/$file";
				if (file_exists($file) && is_writable($file)) {
					unlink($file);
					if (!get_files_list($dir = dirname($file))) {
						rmdir($dir);
					}
				}
			}
			unset($fs, $file, $dir);
			unlink("$theme_dir/fs_old.json");
			unlink("$theme_dir/meta_old.json");
			/**
			 * Clean themes cache
			 */
			$Index->save(true);
			clean_pcache();
		break;
		case 'remove':
			if (!isset($_POST['remove_theme'])) {
				break;
			}
			$Page->title($L->complete_removal_of_theme($_POST['remove_theme']));
			$Index->content(
				h::{'h2.cs-center'}(
					$L->completely_remove_theme($_POST['remove_theme'])
				)
			);
			$Index->buttons            = false;
			$Index->cancel_button_back = true;
			$Index->content(
				h::{'button.uk-button[type=submit][name=action][value=remove_confirmed]'}($L->yes).
				h::{'input[type=hidden]'}([
					'name'  => 'remove_theme_confirmed',
					'value' => $_POST['remove_theme']
				])
			);
			return;
		break;
		case 'remove_confirmed':
			$theme = $_POST['remove_theme_confirmed'];
			if ($theme == 'CleverStyle' || $theme == $Config->core['theme']) {
				break;
			}
			$ok = true;
			get_files_list(
				THEMES."/$theme",
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
			if ($ok && @rmdir(THEMES."/$theme")) {
				$Index->save();
			} else {
				$Index->save(false);
			}
		break;
	}
}

$Config->reload_themes();
$themes_for_removal = array_values(array_filter(
	get_files_list(THEMES, '/[^CleverStyle)]/', 'd'),
	function ($theme) use ($Config) {
		return $theme != $Config->core['theme'];
	}
));
$Index->file_upload	= true;
$Index->content(
	h::{'cs-table[right-left] cs-table-row| cs-table-cell'}([
		core_select($Config->core['themes'], 'theme', null, 'current_theme')
	]).
	h::p(
		h::{'input[type=file][name=upload_theme]'}().
		h::{'button.uk-button[type=submit][name=action][value=upload]'}(
			h::icon('upload').$L->upload_and_install_update_theme,
			[
				'formaction' => $Index->action
			]
		)
	).
	(
	$themes_for_removal
		? h::p(
		h::{'select[name=remove_theme]'}($themes_for_removal).
		h::{'button.uk-button[type=submit][name=action][value=remove]'}(
			h::icon('trash-o').$L->complete_theme_removal,
			[
				'formaction' => $Index->action
			]
		)
	)
		: ''
	)
);
