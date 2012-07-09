<?php
global $Config, $Index, $L, $Page;
$a = &$Index;
$rc = $Config->routing['current'];
$test_dialog = true;
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
			$a->action = 'admin/'.MODULE.'/'.$rc[0].'/'.$rc[1];
			$Page->title($rc[2] == 'edit' ? $L->editing_a_storage($Config->storage[$rc[3]]['host'].'/'.$Config->storage[$rc[3]]['connection']) : $L->adding_a_storage);
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages'}(
					$rc[2] == 'edit' ? $L->editing_a_storage($Config->storage[$rc[3]]['host'].'/'.$Config->storage[$rc[3]]['connection']) : $L->adding_a_storage
				).
				h::{'table.cs-fullwidth-table.cs-center-all'}(
					h::tr(
						h::{'th.ui-widget-header.ui-corner-all'}([
							h::info('storageurl'),
							h::info('storagehost'),
							h::info('storageconnection'),
							h::info('storageuser'),
							h::info('storagepass')
						])
					).
					h::tr(
						h::{'td.ui-widget-content.ui-corner-all.cs-add-storage'}([
							h::{'input.cs-form-element'}([
								'name'		=> 'storage[url]',
								'value'		=> $rc[2] == 'edit' ? $storage['url'] : ''
							]),
							h::{'input.cs-form-element'}([
								'name'		=> 'storage[host]',
								'value'		=> $rc[2] == 'edit' ? $storage['host'] : ''
							]),
							h::{'select.cs-form-element'}(
								[
									'in'		=> _mb_substr(get_list(ENGINES.'/storage', '/^[^_].*?\.php$/i', 'f'), 0, -4)
								],
								[
									'name'		=> 'storage[connection]',
									'selected'	=> $rc[2] == 'edit' ? $storage['connection'] : '',
									'size'		=> 5
								]
							),
							h::{'input.cs-form-element'}([
								'name'		=> 'storage[user]',
								'value'		=> $rc[2] == 'edit' ? $storage['user'] : ''
							]),
							h::{'input.cs-form-element'}([
								'name'		=> 'storage[password]',
								'value'		=> $rc[2] == 'edit' ? $storage['password'] : ''
							]).
							h::{'input[type=hidden]'}([
								'name'		=> 'mode',
								'value'		=> $rc[2] == 'edit' ? 'edit' : 'add'
							]).
							(isset($rc[3]) ? h::{'input[type=hidden]'}([
								'name'	=> 'storage_id',
								'value'	=> $rc[3]
							]) : '')
						])
					)
				).
				h::button(
					$L->test_connection,
					[
						'onMouseDown'	=> 'storage_test(\''.$a->action.'/test\');'
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
				global $Page;
				$Page->warning($L->storage_used_by_modules.': '.implode(', ', $modules));
			} else {
				$a->action = 'admin/'.MODULE.'/'.$rc[0].'/'.$rc[1];
				$Page->title($L->deleting_a_storage($Config->storage[$rc[3]]['host'].'/'.$Config->storage[$rc[3]]['connection']));
				$a->content(
					h::{'p.ui-priority-primary.cs-state-messages'}(
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
					h::{'button[type=submit]'}($L->yes)
				);
			}
		break;
		case 'test':
			interface_off();
			$test_dialog		= false;
			$a->form			= false;
			$a->generate_auto	= false;
			global $Page, $Storage;
			if (isset($rc[3])) {
				$Page->Content = h::{'p.cs-test-result'}($Storage->test([$rc[3]]) ? $L->success : $L->fail);
			} else {
				$Page->Content = h::{'p.cs-test-result'}($Storage->test($_POST['storage']) ? $L->success : $L->fail);
			}
		break;
	}
} else {
	$storage_list = h::tr(
		h::{'th.ui-widget-header.ui-corner-all'}([
			$L->action,
			$L->storageurl,
			$L->storagehost,
			$L->storageconnection,
			$L->storageuser
		])
	);
	global $STORAGE_TYPE, $STORAGE_URL, $STORAGE_HOST, $STORAGE_USER;
	foreach ($Config->storage as $i => &$storage_data) {
		$storage_list .=	h::tr(
			h::td(
				($i ? 
				h::{'a.cs-button.cs-button-compact'}(
					h::icon('wrench'),
					[
						'href'			=> $a->action.'/edit/'.$i,
						'data-title'	=> $L->edit.' '.$L->storage
					]
				).
				h::{'a.cs-button.cs-button-compact'}(
					h::icon('trash'),
					[
						'href'			=> $a->action.'/delete/'.$i,
						'data-title'	=> $L->delete.' '.$L->storage
					]
				).
				h::{'a.cs-button.cs-button-compact'}(
					h::icon('signal-diag'),
					[
						'onMouseDown'	=> 'storage_test(\''.$a->action.'/test/'.$i.'\', true);',
						'data-title'	=> $L->test_connection
					]
				) : '-'),
				[
					'class'	=> 'ui-corner-all '.($i ? 'ui-widget-content' : 'ui-state-highlight')
				]
			).
			h::td(
				[
					$i	? $storage_data['url']			: $STORAGE_URL ?: url_by_source(STORAGE),
					$i	? $storage_data['host']			: $STORAGE_HOST,
					$i	? $storage_data['connection']	: $STORAGE_TYPE,
					$i	? $storage_data['user']			: $STORAGE_USER ?: '-'
				],
				[
					'class'	=> 'ui-corner-all '.($i ? 'ui-widget-content' : 'ui-state-highlight')
				]
			)
		);
	}
	unset($i, $storage_data);
	$a->content(
		h::{'table.cs-fullwidth-table.cs-center-all'}(
			$storage_list.
			h::tr(
				h::{'td.cs-left-all[colspan=4]'}(
					h::button(
						$L->add_storage,
						[
							'onMouseDown' => 'javasript: location.href= \'admin/'.MODULE.'/'.$rc[0].'/'.$rc[1].'/add\';'
						]
					).h::br()
				)
			)
		)
	);
	unset($storage_list);
}
$test_dialog && $a->content(
	h::{'div#test_storage.cs-dialog'}([
		'data-dialog'	=> '{"autoOpen":false,"height":"75","hide":"puff","modal":true,"show":"scale","width":"250"}',
		'title'			=> $L->test_connection
	])
);