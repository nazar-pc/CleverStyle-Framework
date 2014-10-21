<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
$Config			= Config::instance();
$L				= Language::instance();
$Page			= Page::instance();
$a				= Index::instance();
$rc				= $Config->route;
$test_dialog	= true;
if (isset($rc[2])) {
	$a->apply_button		= false;
	$a->cancel_button_back	= true;
	switch ($rc[2]) {
		case 'add':
		case 'edit':
			if ($rc[2] == 'edit' && isset($rc[3])) {
				$storage = &$Config->storage[$rc[3]];
			}
			/**
			 * @var array $storage
			 */
			$a->action = "admin/System/$rc[0]/$rc[1]";
			$Page->title($rc[2] == 'edit' ? $L->editing_of_storage($Config->storage[$rc[3]]['host'].'/'.$Config->storage[$rc[3]]['connection']) : $L->adding_of_storage);
			$a->content(
				h::{'h2.cs-center'}(
					$rc[2] == 'edit' ? $L->editing_of_storage($Config->storage[$rc[3]]['host'].'/'.$Config->storage[$rc[3]]['connection']) : $L->adding_of_storage
				).
				h::{'cs-table[center][right-left] cs-table-row| cs-table-cell'}(
					[
						h::info('storage_url'),
						h::input([
							'name'		=> 'storage[url]',
							'value'		=> $rc[2] == 'edit' ? $storage['url'] : ''
						])
					],
					[
						h::info('storage_host'),
						h::input([
							'name'		=> 'storage[host]',
							'value'		=> $rc[2] == 'edit' ? $storage['host'] : ''
						])
					],
					[
						h::info('storage_connection'),
						h::select(
							[
								'in'		=> _mb_substr(get_files_list(ENGINES.'/Storage', '/^[^_].*?\.php$/i', 'f'), 0, -4)
							],
							[
								'name'		=> 'storage[connection]',
								'selected'	=> $rc[2] == 'edit' ? $storage['connection'] : '',
								'size'		=> 5
							]
						)
					],
					[
						h::info('storage_user'),
						h::input([
							'name'		=> 'storage[user]',
							'value'		=> $rc[2] == 'edit' ? $storage['user'] : ''
						])
					],
					[
						h::info('storage_pass'),
						h::input([
							'name'		=> 'storage[password]',
							'value'		=> $rc[2] == 'edit' ? $storage['password'] : ''
						])
					]
				).
				h::{'input[type=hidden]'}([
					'name'		=> 'mode',
					'value'		=> $rc[2] == 'edit' ? 'edit' : 'add'
				]).
				(
					isset($rc[3])
						? h::{'input[type=hidden]'}([
							'name'	=> 'storage_id',
							'value'	=> $rc[3]
						])
						: ''
				).
				h::{'button.uk-button'}(
					$L->test_connection,
					[
						'onMouseDown'	=> "cs.storage_test();"
					]
				)
			);
		break;
		case 'delete':
			$a->buttons = false;
			$modules = [];
			foreach ($Config->components['modules'] as $module => &$mdata) {
				if (isset($mdata['storage']) && is_array($mdata['storage'])) {
					foreach ($mdata['storage'] as $storage_name) {
						if ($storage_name == $rc[3]) {
							$modules[] = h::b($module);
							break;
						}
					}
				}
			}
			unset($module, $mdata, $storage_name);
			if (!empty($modules)) {
				$Page->warning($L->storage_used_by_modules.': '.implode(', ', $modules));
			} else {
				$a->action = "admin/System/$rc[0]/$rc[1]";
				$Page->title($L->deletion_of_storage($Config->storage[$rc[3]]['host'].'/'.$Config->storage[$rc[3]]['connection']));
				$a->content(
					h::{'h2.cs-center'}(
						$L->sure_to_delete.' '.$L->storage.' '.
							$Config->storage[$rc[3]]['host'].'/'.$Config->storage[$rc[3]]['connection'].'?'.
							h::{'input[type=hidden]'}([
								'name'	=> 'mode',
								'value'	=> 'delete'
							]).
							h::{'input[type=hidden]'}([
								'name'	=> 'storage',
								'value'	=> $rc[3]
							])
					).
					h::{'button.uk-button[type=submit]'}($L->yes)
				);
			}
	}
} else {
	$storages_list = [];
	$Core			= Core::instance();
	$storages		= $Config->storage;
	if (!empty($storages)) {
		foreach ($storages as $i => &$storage_data) {
			$storages_list[] =	[
				[
					($i ?
					h::{'a.uk-button.cs-button-compact'}(
						h::icon('pencil'),
						[
							'href'			=> "$a->action/edit/$i",
							'data-title'	=> "$L->edit $L->storage"
						]
					).
					h::{'a.uk-button.cs-button-compact'}(
						h::icon('trash-o'),
						[
							'href'			=> "$a->action/delete/$i",
							'data-title'	=> "$L->delete $L->storage"
						]
					).
					h::{'a.uk-button.cs-button-compact'}(
						h::icon('signal'),
						[
							'onMouseDown'	=> "cs.storage_test($i);",
							'data-title'	=> $L->test_connection
						]
					) : '-'),
					[
						'class'	=> $i ? '' : 'text-primary'
					]
				],
				[
					[
						$i	? $storage_data['url']			: $Core->storage_url ?: url_by_source(STORAGE),
						$i	? $storage_data['host']			: $Core->storage_host,
						$i	? $storage_data['connection']	: $Core->storage_type,
						$i	? $storage_data['user']			: $Core->storage_user ?: '-'
					],
					[
						'class'	=> $i ? '' : 'text-primary'
					]
				]
			];
		}
		unset($i, $storage_data);
	}
	unset($storages);
	$a->content(
		h::{'cs-table[center][list][with-header] cs-table-row| cs-table-cell'}(
			[
				$L->action,
				$L->storage_url,
				$L->storage_host,
				$L->storage_connection,
				$L->storage_user
			],
			$storages_list
		).
		h::{'p a.uk-button'}(
			$L->add_storage,
			[
				'href' => "admin/System/$rc[0]/$rc[1]/add"
			]
		)
	);
	unset($storages_list);
}
$test_dialog && $a->content(
	h::{'div#cs-storage-test.uk-modal div'}(
		h::h3($L->test_connection).
		h::div()
	)
);
