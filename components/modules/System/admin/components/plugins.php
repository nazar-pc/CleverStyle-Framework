<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\System;
use			h,
			cs\Config,
			cs\Core,
			cs\Index,
			cs\Language,
			cs\Page,
			cs\User;
$Config			= Config::instance();
$L				= Language::instance();
$Page			= Page::instance();
$a				= Index::instance();
$rc				= $Config->route;
$plugins		= get_files_list(PLUGINS, false, 'd');
$a->buttons		= false;
if (
	isset($rc[2]) &&
	!empty($rc[2]) &&
	(
		(
			isset($rc[3]) &&
			!empty($rc[3])
		) ||
		$rc[2] == 'remove'
	)
) {
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
				if ($_FILES['upload_module']['error'] != UPLOAD_ERR_OK) {
					break;
				}
				$tmp_file = TEMP.'/'.md5($_FILES['upload_plugin']['tmp_name'].MICROTIME).'.phar.php';
				move_uploaded_file($_FILES['upload_plugin']['tmp_name'], $tmp_file);
				$tmp_dir	= "phar://$tmp_file";
				$plugin		= file_get_contents("$tmp_dir/dir");
				if (!$plugin) {
					unlink($tmp_file);
					break;
				}
				$rc[3]		= $plugin;
				if (!file_exists("$tmp_dir/meta.json") || file_get_json("$tmp_dir/meta.json")['category'] != 'plugins') {
					$Page->warning($L->this_is_not_plugin_installer_file);
					unlink($tmp_file);
					break;
				}
				if (in_array($plugin, $Config->components['plugins'])) {
					$current_version		= file_get_json(PLUGINS."/$plugin/meta.json")['version'];
					$new_version			= file_get_json("$tmp_dir/meta.json")['version'];
					if (!version_compare($current_version, $new_version, '<')) {
						$Page->warning($L->update_plugin_impossible_older_version($plugin));
						unlink($tmp_file);
						break;
					}
					$check_dependencies		= check_dependencies($plugin, 'plugin', $tmp_dir);
					if (!$check_dependencies && $Config->core['simple_admin_mode']) {
						break;
					}
					$rc[2]					= 'update';
					$Page->title($L->updating_of_plugin($plugin));
					rename($tmp_file, $tmp_file = TEMP.'/'.User::instance()->get_session().'_plugin_update.phar.php');
					$a->content(
						h::{'h2.cs-center'}(
							$L->update_plugin(
								$plugin,
								$current_version,
								$new_version
							)
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
					$a->cancel_button_back	= true;
					$a->content(
						h::{'button.uk-button[type=submit]'}($L->{$check_dependencies ? $L->yes : 'force_update_not_recommended'})
					);
					return;
				}
				if (!file_exists(PLUGINS."/$plugin") && !mkdir(PLUGINS."/$plugin", 0770)) {
					$Page->warning($L->cant_unpack_plugin_no_write_permissions);
					unlink($tmp_file);
					break;
				}
				$fs				= file_get_json("$tmp_dir/fs.json");
				$extract		= array_product(
					array_map(
						function ($index, $file) use ($tmp_dir, $plugin) {
							if (
								!file_exists(dirname(PLUGINS."/$plugin/$file")) &&
								!mkdir(dirname(PLUGINS."/$plugin/$file"), 0770, true)
							) {
								return 0;
							}
							return (int)copy("$tmp_dir/fs/$index", PLUGINS."/$plugin/$file");
						},
						$fs,
						array_keys($fs)
					)
				);
				file_put_json(PLUGINS."/$plugin/fs.json", array_keys($fs));
				unset($tmp_dir);
				if (!$extract) {
					$Page->warning($L->plugin_files_unpacking_error);
					break;
				}
				unlink($tmp_file);
				$plugins[]		= $plugin;
				unset($tmp_file, $plugin);
			}
			if (!in_array($rc[3], $Config->components['plugins']) && in_array($rc[3], $plugins)) {
				$Page->title($L->enabling_of_plugin($rc[3]));
				$a->content(
					h::{'h2.cs-center'}(
						$L->enabling_of_plugin($rc[3])
					)
				);
				$check_dependencies		= check_dependencies($rc[3], 'plugin');
				if (!$check_dependencies && $Config->core['simple_admin_mode']) {
					break;
				}
				if (file_exists(PLUGINS."/$rc[3]/meta.json")) {
					$meta	= file_get_json(PLUGINS."/$rc[3]/meta.json");
					if (isset($meta['optional'])) {
						$Page->success(
							$L->for_complete_feature_set(
								implode(', ', (array)$meta['optional'])
							)
						);
					}
					unset($meta);
				}
				$a->cancel_button_back	= true;
				$a->content(
					h::{'button.uk-button[type=submit]'}(
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
				return;
			}
		break;
		case 'disable':
			if (in_array($rc[3], $Config->components['plugins'])) {
				$Page->title($L->disabling_of_plugin($rc[3]));
				$a->content(
					h::{'h2.cs-center'}(
						$L->disabling_of_plugin($rc[3])
					)
				);
				$check_dependencies		= check_backward_dependencies($rc[3], 'plugin');
				if (!$check_dependencies && $Config->core['simple_admin_mode']) {
					break;
				}
				$a->cancel_button_back	= true;
				$a->content(
					h::{'button.uk-button[type=submit]'}(
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
			return;
		break;
		case 'remove':
			$Page->title($L->complete_removal_of_plugin($_POST['remove_plugin']));
			$a->content(
				h::{'h2.cs-center'}(
					$L->completely_remove_plugin($_POST['remove_plugin'])
				)
			);
			$a->cancel_button_back	= true;
			$a->content(
				h::{'button.uk-button[type=submit]'}($L->yes).
				h::{'input[type=hidden]'}([
					'name'	=> 'mode',
					'value'	=> $rc[2]
				]).
				h::{'input[type=hidden]'}([
					'name'	=> 'plugin',
					'value'	=> $_POST['remove_plugin']
				])
			);
			return;
		break;
	}
}
unset($rc);
$a->buttons		= false;
$a->file_upload	= true;
$plugins_list	= [];
if (!empty($plugins)) {
	foreach ($plugins as $plugin) {
		$addition_state = $action = '';
		/**
		 * Information about plugin
		 */
		if (file_exists($file = PLUGINS."/$plugin/readme.txt") || file_exists($file = PLUGINS."/$plugin/readme.html")) {
			if (substr($file, -3) == 'txt') {
				$tag		= 'pre';
			} else {
				$tag = 'div';
			}
			$uniqid			= uniqid('module_info_');
			$Page->replace($uniqid, $tag == 'pre' ? prepare_attr_value(file_get_contents($file)) : file_get_contents($file));
			$addition_state .= h::{'div.uk-modal.cs-left'}(
				h::{"$tag.uk-modal-dialog-large"}($uniqid),
				[
					'id'			=> "{$plugin}_readme",
					'title'			=> "$plugin -> $L->information_about_plugin"
				]
			).
			h::{'icon.cs-pointer'}(
				'exclamation',
				[
					'data-title'	=> $L->information_about_plugin.h::br().$L->click_to_view_details,
					'onClick'		=> "$('#{$plugin}_readme').cs().modal('show');"
				]
			);
			unset($uniqid);
		}
		unset($tag, $file);
		/**
		 * License
		 */
		if (file_exists($file = PLUGINS."/$plugin/license.txt") || file_exists($file = PLUGINS."/$plugin/license.html")) {
			if (substr($file, -3) == 'txt') {
				$tag = 'pre';
			} else {
				$tag = 'div';
			}
			$addition_state .= h::{'div.uk-modal.cs-left'}(
				h::{"$tag.uk-modal-dialog-large"}($tag == 'pre' ? prepare_attr_value(file_get_contents($file)) : file_get_contents($file)),
				[
					'id'			=> "{$plugin}_license",
					'title'			=> "$plugin -> $L->license"
				]
			).
			h::{'icon.cs-pointer'}(
				'legal',
				[
					'data-title'	=> $L->license.h::br().$L->click_to_view_details,
					'onClick'		=> "$('#{$plugin}_license').cs().modal('show');"
				]
			);
		}
		unset($tag, $file);
		$state = in_array($plugin, $Config->components['plugins']);
		$action .= h::{'a.uk-button.cs-button-compact'}(
			h::icon($state ? 'minus' : 'check'),
			[
				'href'			=> $a->action.($state ? '/disable/' : '/enable/').$plugin,
				'data-title'	=> $state ? $L->disable : $L->enable
			]
		);
		$plugin_info	= false;
		if (file_exists(PLUGINS."/$plugin/meta.json")) {
			$plugin_meta	= file_get_json(PLUGINS."/$plugin/meta.json");
			$plugin_info	= $L->plugin_info(
				$plugin_meta['package'],
				$plugin_meta['version'],
				$plugin_meta['description'],
				$plugin_meta['author'],
				isset($plugin_meta['website']) ? $plugin_meta['website'] : $L->none,
				$plugin_meta['license'],
				isset($plugin_meta['provide']) ? implode(', ', (array)$plugin_meta['provide']) : $L->none,
				isset($plugin_meta['require']) ? implode(', ', (array)$plugin_meta['require']) : $L->none,
				isset($plugin_meta['conflict']) ? implode(', ', (array)$plugin_meta['conflict']) : $L->none,
				isset($plugin_meta['optional']) ? implode(', ', (array)$plugin_meta['optional']) : $L->none,
				isset($plugin_meta['multilingual']) && in_array('interface', $plugin_meta['multilingual']) ? $L->yes : $L->no,
				isset($plugin_meta['multilingual']) && in_array('content', $plugin_meta['multilingual']) ? $L->yes : $L->no,
				isset($plugin_meta['languages']) ? implode(', ', $plugin_meta['languages']) : $L->none
			);
		}
		unset($plugin_meta);
		$plugins_list[]	= [
			[
				h::span(
					$L->$plugin,
					[
						'data-title'	=> $plugin_info
					]
				),
				h::icon(
					$state ? 'check' : 'minus',
					[
						'data-title'	=> $state ? $L->enabled : $L->disabled
					]
				).
				$addition_state,
				$action
			],
			[
				'class'	=> $state ? 'uk-alert-success' : 'uk-alert-warning'
			]
		];
		unset($plugin_info);
	}
	unset($plugin, $state, $addition_state, $action);
}
$plugins_for_removal = array_values(array_filter(
	$plugins,
	function ($plugin) use ($Config) {
		return !in_array($plugin, $Config->components['plugins']);
	}
));
$a->content(
	h::{'cs-table[center][list][with-header] cs-table-row| cs-table-cell'}(
		[
			$L->plugin_name,
			$L->state,
			$L->action
		],
		$plugins_list ?: false
	).
	h::p(
		h::{'input[type=file][name=upload_plugin]'}([
			'style'	=> 'position: relative;'
		]).
		h::{'button.uk-button[type=submit]'}(
			h::icon('upload').$L->upload_and_install_update_plugin,
			[
				'formaction'	=>  "$a->action/enable/upload"
			]
		)
	).
	(
		$plugins_for_removal
			? h::p(
				h::{'select[name=remove_plugin]'}($plugins_for_removal).
				h::{'button.uk-button[type=submit]'}(
					h::icon('trash-o').$L->complete_plugin_removal,
					[
						'formaction'	=>  "$a->action/remove"
					]
				)
			)
			: ''
	)
);
