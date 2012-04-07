<?php
global $Config, $Index, $L;
$a = &$Index;
$rc = &$Config->routing['current'];
$test_dialog = true;
if (isset($rc[2])) {
	$a->apply = false;
	$a->cancel_back = true;
	switch ($rc[2]) {
		case 'add':
		case 'edit':
			if (isset($rc[3])) {
				if ($rc[2] == 'edit') {
					$storage = &$Config->storage[$rc[3]];
				}
				$a->action = 'admin/'.MODULE.'/'.$rc[0].'/'.$rc[1];
				$a->content(
					h::{'table.admin_table.center_all'}(
						h::tr(
							h::{'th.ui-widget-header.ui-corner-all'}(
								array(
									h::info('storageurl'),
									h::info('storagehost'),
									h::info('storageconnection'),
									h::info('storageuser'),
									h::info('storagepass')
								)
							)
						).
						h::tr(
							h::{'td.ui-widget-content.ui-corner-all.storage_add'}(
								array(
									h::{'input.form_element'}(
										array(
											'name'		=> 'storage[url]',
											'value'		=> $rc[2] == 'edit' ? $storage['url'] : ''
										)
									),
									h::{'input.form_element'}(
										array(
											'name'		=> 'storage[host]',
											'value'		=> $rc[2] == 'edit' ? $storage['host'] : ''
										)
									),
									h::{'select.form_element'}(
										array(
											'in'		=> _mb_substr(get_list(ENGINES, '/^storage\..*?\.php$/i', 'f'), 8, -4)
										),
										array(
											'name'		=> 'storage[connection]',
											'selected'	=> $rc[2] == 'edit' ? $storage['connection'] : '',
											'size'		=> 5
										)
									),
									h::{'input.form_element'}(
										array(
											'name'		=> 'storage[user]',
											'value'		=> $rc[2] == 'edit' ? $storage['user'] : ''
										)
									),
									h::{'input.form_element'}(
										array(
											'name'		=> 'storage[password]',
											'value'		=> $rc[2] == 'edit' ? $storage['password'] : ''
										)
									).
									h::{'input[type=hidden]'}(
										array(
											'name'		=> 'mode',
											'value'		=> $rc[2] == 'edit' ? 'edit' : 'add'
										)
									).
									(isset($rc[3]) ? h::{'input[type=hidden]'}(array('name' => 'storage_id', 'value' => $rc[3])) : '')
								)
							)
						)
					).
					h::button(
						$L->test_connection,
						array(
							'onMouseDown'	=> 'storage_test(\''.$a->action.'/test\');'
						)
					)
				);
			}
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
				$a->content(
					h::{'p.center_all'}(
						$L->sure_to_delete.' '.$L->storage.' '.
							h::b($Config->storage[$rc[3]]['host'].'/'.$Config->storage[$rc[3]]['connection']).'?'.
							h::{'input[type=hidden]'}(array('name'	=> 'mode',		'value'		=> 'delete')).
							h::{'input[type=hidden]'}(array('name'	=> 'storage',	'value'		=> $rc[3]))
					).
					h::{'button[type=submit]'}($L->yes)
				);
			}
		break;
		case 'test':
			interface_off();
			$test_dialog = false;
			$a->form = false;
			global $Page, $Storage;
			if (isset($rc[3])) {
				$Page->Content = h::{'p.test_result'}($Storage->test(array($rc[3])) ? $L->success : $L->fail);
			} else {
				$Page->Content = h::{'p.test_result'}($Storage->test($_POST['storage']) ? $L->success : $L->fail);
			}
		break;
	}
} else {
	$storage_list = h::tr(
		h::{'th.ui-widget-header.ui-corner-all'}(
			array(
				$L->action,
				$L->storageurl,
				$L->storagehost,
				$L->storageconnection,
				$L->storageuser
			)
		)
	);
	foreach ($Config->storage as $i => &$storage_data) {
		$storage_list .=	h::tr(
			h::td(
				($i ? 
				h::a(
					h::{'button.compact'}(
						h::icon('wrench'),
						array(
							'data-title'	=> $L->edit.' '.$L->storage
						)
					),
					array(
						'href'		=> $a->action.'/edit/'.$i
					)
				).
				h::a(
					h::{'button.compact'}(
						h::icon('trash'),
						array(
							'data-title'	=> $L->delete.' '.$L->storage
						)
					),
					array(
						'href'		=> $a->action.'/delete/'.$i
					)
				).
				h::a(
					h::{'button.compact'}(
						h::icon('signal-diag'),
						array(
							'data-title'	=> $L->test_connection
						)
					),
					array(
						'onMouseDown'	=> 'storage_test(\''.$a->action.'/test/'.$i.'\', true);'
					)
				) : '-'),
				array(
					'class'	=> 'ui-corner-all storages_config_buttons '.($i ? 'ui-widget-content' : 'ui-state-highlight')
				)
			).
			h::td(
				array(
					$i	? $storage_data['url']			: url_by_source(STORAGE),
					$i	? $storage_data['host']			: 'localhost',
					$i	? $storage_data['connection']	: 'Local',
					$i	? $storage_data['user']			: '-'
				),
				array(
					'class'	=> 'ui-corner-all '.($i ? 'ui-widget-content' : 'ui-state-highlight')
				)
			)
		);
	}
	unset($i, $storage_data);
	$a->content(
		h::{'table.admin_table.center_all'}(
			$storage_list.
			h::tr(
				h::{'td.left_all[colspan=4]'}(
					h::button(
						$L->add_storage,
						array(
							'onMouseDown' => 'javasript: location.href= \'admin/'.MODULE.'/'.$rc[0].'/'.$rc[1].'/add\';'
						)
					).h::br()
				)
			)
		)
	);
	unset($storage_list);
}
$test_dialog && $a->content(
	h::{'div#test_storage.dialog'}(
		array(
			'data-dialog'	=> '{"autoOpen":false,"height":"75","hide":"puff","modal":true,"show":"scale","width":"250"}',
			'title'			=> $L->test_connection
		)
	)
);