<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\System;
use			\h;
global $Config, $Index, $L;
$a				= $Index;
$rc				= $Config->routing['current'];
$plugins		= get_files_list(PLUGINS, false, 'd');
$show_plugins	= true;
$a->buttons		= false;
if (isset($rc[2], $rc[3]) && !empty($rc[2]) && !empty($rc[3])) {
	global $Page;
	switch ($rc[2]) {
		case 'enable':
			if ($rc[3] == 'upload' && isset($_FILES['upload_plugin']) && $_FILES['upload_plugin']['tmp_name']) {
				switch ($_FILES['upload_plugin']['error']) {
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
				move_uploaded_file(
					$_FILES['upload_plugin']['tmp_name'],
					$tmp_file = TEMP.'/'.md5($_FILES['upload_plugin']['tmp_name'].MICROTIME).'.phar.php'
				);
				$tmp_dir								= 'phar://'.$tmp_file;
				if (!($rc[3]	= file_get_contents($tmp_dir.'/dir'))) {
					unlink($tmp_file);
					break;
				}
				if (_json_decode(file_get_contents($tmp_dir.'/meta.json'))['category'] != 'plugins') {
					$Page->warning($L->this_is_not_plugin_installer_file);
					unlink($tmp_file);
					break;
				}
				if (in_array($rc[3], $Config->components['plugins'])) {
					$Page->warning($L->cant_unpack_plugin_it_already_exists);
					unlink($tmp_file);
					break;
				}
				if (!file_exists(PLUGINS.'/'.$rc[3]) && !mkdir(PLUGINS.'/'.$rc[3], 0700)) {
					$Page->warning($L->cant_unpack_plugin_no_write_permissions);
					unlink($tmp_file);
					break;
				}
				$plugins[]								= $rc[3];
				$fs										= _json_decode(file_get_contents($tmp_dir.'/fs.json'));
				$extract								= array_product(
					array_map(
						function ($index, $file) use ($tmp_dir, $rc) {
							if (
								!file_exists(pathinfo(PLUGINS.'/'.$rc[3].'/'.$file, PATHINFO_DIRNAME)) &&
								!mkdir(pathinfo(PLUGINS.'/'.$rc[3].'/'.$file, PATHINFO_DIRNAME), 0700, true)
							) {
								return 0;
							}
							return (int)copy($tmp_dir.'/fs/'.$index, PLUGINS.'/'.$rc[3].'/'.$file);
						},
						$fs,
						array_keys($fs)
					)
				);
				file_put_contents(PLUGINS.'/'.$rc[3].'/fs.json', _json_encode(array_keys($fs)));
				unlink($tmp_file);
				unset($tmp_dir, $tmp_file);
				if (!$extract) {
					$Page->warning($L->plugin_files_unpacking_error);
					break;
				}
			}
			if (!in_array($rc[3], $Config->components['plugins']) && in_array($rc[3], $plugins)) {
				$Page->title($L->enabling_of_plugin($rc[3]));
				$a->content(
					h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
						$L->enabling_of_plugin($rc[3])
					)
				);
				$show_plugins			= false;
				$check_dependencies		= check_dependencies($rc[3], 'plugin');
				if (!$check_dependencies && $Config->core['simple_admin_mode']) {
					break;
				}
				$a->cancel_button_back	= true;
				$a->content(
					h::{'button[type=submit]'}(
						$L->{$check_dependencies ? 'enable' : 'force_enable_not_recommended'}
					).
					h::{'input[type=hidden]'}([
						'name'	=> 'mode',
						'value'	=> $rc[2]
					]).
					h::{'input[type=hidden]'}([
						'name'	=> 'plugin',
						'value'	=> $rc[3]
					])

				);
			}
		break;
		case 'disable':
			if (in_array($rc[3], $Config->components['plugins'])) {
				$Page->title($L->disabling_of_plugin($rc[3]));
				$a->content(
					h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
						$L->disabling_of_plugin($rc[3])
					)
				);
				$show_plugins			= false;
				$check_dependencies		= check_backward_dependencies($rc[3], 'plugin');
				if (!$check_dependencies && $Config->core['simple_admin_mode']) {
					break;
				}
				$a->cancel_button_back	= true;
				$a->content(
					h::{'button[type=submit]'}(
						$L->{$check_dependencies ? 'disable' : 'force_disable_not_recommended'}
					).
					h::{'input[type=hidden]'}([
						'name'	=> 'mode',
						'value'	=> $rc[2]
					]).
					h::{'input[type=hidden]'}([
						'name'	=> 'plugin',
						'value'	=> $rc[3]
					])

				);
			}
		break;
	}
}
unset($rc);
if (!$show_plugins) {
	return;
}
$a->buttons		= false;
$a->file_upload	= true;
$plugins_list	= [h::{'th.ui-widget-header.ui-corner-all'}(
	$L->plugin_name,
	$L->state,
	$L->action
)];
if (!empty($plugins)) {
	foreach ($plugins as $plugin) {
		$addition_state = $action = '';
		/**
		 * Information about plugin
		 */
		if (file_exists($file = PLUGINS.'/'.$plugin.'/readme.txt') || file_exists($file = PLUGINS.'/'.$plugin.'/readme.html')) {
			if (substr($file, -3) == 'txt') {
				$tag = 'pre';
			} else {
				$tag = 'div';
			}
			$addition_state .= h::$tag(
				$tag == 'pre' ? filter(file_get_contents($file)) : file_get_contents($file),
				[
					'id'			=> $plugin.'_readme',
					'class'			=> 'cs-dialog',
					'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
					'title'			=> $plugin.' -> '.$L->information_about_plugin
				]
			).
			h::{'icon.cs-pointer'}(
				'notice',
				[
					'data-title'	=> $L->information_about_plugin.h::br().$L->click_to_view_details,
					'onClick'		=> "$('#".$plugin."_readme').dialog('open');"
				]
			);
		}
		unset($tag, $file);
		/**
		 * License
		 */
		if (file_exists($file = PLUGINS.'/'.$plugin.'/license.txt') || file_exists($file = PLUGINS.'/'.$plugin.'/license.html')) {
			if (substr($file, -3) == 'txt') {
				$tag = 'pre';
			} else {
				$tag = 'div';
			}
			$addition_state .= h::$tag(
				$tag == 'pre' ? filter(file_get_contents($file)) : file_get_contents($file),
				[
					'id'			=> $plugin.'_license',
					'class'			=> 'cs-dialog',
					'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
					'title'			=> $plugin.' -> '.$L->license
				]
			).
			h::{'icon.cs-pointer'}(
				'note',
				[
					'data-title'	=> $L->license.h::br().$L->click_to_view_details,
					'onClick'		=> "$('#".$plugin."_license').dialog('open');"
				]
			);
		}
		unset($tag, $file);
		$state = in_array($plugin, $Config->components['plugins']);
		$action .= h::{'a.cs-button-compact'}(
			h::icon($state ? 'minusthick' : 'check'),
			[
				'href'			=> $a->action.($state ? '/disable/' : '/enable/').$plugin,
				'data-title'	=> $state ? $L->disable : $L->enable
			]
		);
		$plugin_info	= false;
		if (file_exists(PLUGINS.'/'.$plugin.'/meta.json')) {
			$plugin_meta	= _json_decode(file_get_contents(PLUGINS.'/'.$plugin.'/meta.json'));
			$plugin_info	= $L->plugin_info(
				$plugin_meta['package'],
				$plugin_meta['version'],
				$plugin_meta['description'],
				$plugin_meta['author'],
				isset($plugin_meta['website']) ? $plugin_meta['website'] : $L->none,
				$plugin_meta['license'],
				isset($plugin_meta['provide']) ? implode(', ', $plugin_meta['provide']) : $L->none,
				isset($plugin_meta['require']) ? implode(', ', $plugin_meta['require']) : $L->none,
				isset($plugin_meta['conflict']) ? implode(', ', $plugin_meta['conflict']) : $L->none,
				isset($plugin_meta['multilingual']) && in_array('interface', $plugin_meta['multilingual']) ? $L->yes : $L->no,
				isset($plugin_meta['multilingual']) && in_array('content', $plugin_meta['multilingual']) ? $L->yes : $L->no,
				isset($plugin_meta['languages']) ? implode(', ', $plugin_meta['languages']) : $L->none
			);
		}
		unset($plugin_meta);
		$plugins_list[]	= h::{'td.ui-widget-content.ui-corner-all'}(
			[
				$plugin,
				[
					'data-title'	=> $plugin_info
				]
			],
			h::icon(
				$state ? 'check' : 'minusthick',
				[
					'data-title'	=> $state ? $L->enabled : $L->disabled
				]
			).
			$addition_state,
			$action
		);
		unset($plugin_info);
	}
	unset($plugins, $plugin, $state, $addition_state, $action);
}
$a->content(
	h::{'table.cs-fullwidth-table.cs-center-all tr'}(
		$plugins_list,
		h::{'td.cs-left-all[colspan=3]'}(
			h::{'input[type=file][name=upload_plugin]'}([
				'style'	=> 'position: relative;'
			]).
			h::{'button[type=submit]'}(
				$L->upload_and_install_plugin,
				[
				'formaction'	=>  $a->action.'/enable/upload'
				]
			)
		)
	)
);
