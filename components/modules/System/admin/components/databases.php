<?php
global $Config, $Index, $L, $Page, $DB_HOST, $DB_TYPE, $DB_PREFIX, $DB_NAME, $DB_CHARSET;
$a				= &$Index;
$rc				= &$Config->routing['current'];
$test_dialog	= false;
if (isset($rc[2])) {
	$a->apply_button		= false;
	$a->cancel_button_back	= true;
	switch ($rc[2]) {
		case 'edit':
			if (!isset($rc[3])) {
				break;
			}
		case 'add':
			$test_dialog = true;
			if ($rc[2] == 'edit') {
				if (isset($rc[4])) {
					$database = &$Config->db[$rc[3]]['mirrors'][$rc[4]];
				} else {
					$database = &$Config->db[$rc[3]];
				}
				$mirror		= isset($rc[4]);
				$cdb		= $Config->db[$rc[3]];
				if ($mirror) {
					$cdbm	= $Config->db[$rc[3]]['mirrors'][$rc[4]];
					$name	= $L->mirror.' '.($rc[3] ? $L->db.' '.$cdb['name'] : $L->core_db).', '.$cdbm['name'].' ('.$cdbm['host'].'/'.$cdbm['type'].')?';
					unset($cdbm);
				} else {
					$name	= $L->db.' '.$cdb['name'].' ('.$cdb['host'].'/'.$cdb['type'].')?';
				}
				unset($mirror, $cdb);
			} elseif ($rc[2] == 'add') {
				$dbs = array(-1, 0);
				$dbsname = array($L->separate_db, $L->core_db);
				foreach ($Config->db as $i => $db) {
					if ($i) {
						$dbs[] = $i;
						$dbsname[] = $db['name'];
					}
				}
				unset($i, $db);
			}
			$a->action = 'admin/'.MODULE.'/'.$rc[0].'/'.$rc[1];
			/**
			 * @var array $dbsname
			 * @var array $dbs
			 * @var array $database
			 * @var string $name
			 */
			$Page->title($rc[2] == 'edit' ? $L->editing_a_database($name) : $L->adding_a_database);
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages'}(
					$rc[2] == 'edit' ? $L->editing_a_database($name) : $L->adding_a_database
				).
				h::{'table.cs-fullwidth-table.cs-center-all'}(
					h::{'tr th.ui-widget-header.ui-corner-all'}([
						$rc[2] == 'add' ? h::info('db_mirror') : false,
						h::info('db_host'),
						h::info('db_type'),
						h::info('db_prefix'),
						h::info('db_name'),
						h::info('db_user'),
						h::info('db_password'),
						h::info('db_charset')
					]).
					h::{'tr td.ui-widget-content.ui-corner-all.cs-add-db'}([
						($rc[2] == 'add' ?
							h::{'select.cs-form-element'}(
								[
									'in'		=> $dbsname,
									'value'		=> $dbs
								],
								[
									'name'		=> 'db[mirror]',
									'selected'	=> isset($rc[3]) ? $rc[3] : -1,
									'size'		=> 5
								]
							)
							: false),
						h::{'input.cs-form-element'}([
							'name'		=> 'db[host]',
							'value'		=> $rc[2] == 'edit' ? $database['host'] : $DB_HOST
						]),
						h::{'select.cs-form-element'}(
							[
								'in'		=> _mb_substr(get_list(ENGINES.DS.'database', '/^[^_].*?\.php$/i', 'f'), 0, -4)
							],
							[
								'name'		=> 'db[type]',
								'selected'	=> $rc[2] == 'edit' ? $database['type'] : $DB_TYPE,
								'size'		=> 5
							]
						),
						h::{'input.cs-form-element'}([
							'name'		=> 'db[prefix]',
							'value'		=> $rc[2] == 'edit' ? $database['prefix'] : $DB_PREFIX
						]),
						h::{'input.cs-form-element'}([
							'name'		=> 'db[name]',
							'value'		=> $rc[2] == 'edit' ? $database['name'] : ''
						]),
						h::{'input.cs-form-element'}([
							'name'		=> 'db[user]',
							'value'		=> $rc[2] == 'edit' ? $database['user'] : ''
						]),
						h::{'input.cs-form-element'}([
							'name'		=> 'db[password]',
							'value'		=> $rc[2] == 'edit' ? $database['password'] : ''
						]),
						h::{'input.cs-form-element'}([
							'name'		=> 'db[charset]',
							'value'		=> $rc[2] == 'edit' ? $database['charset'] : $DB_CHARSET
						]).
						h::{'input[type=hidden]'}([
							'name'		=> 'mode',
							'value'		=> $rc[2] == 'edit' ? 'edit' : 'add'
						]).
						(isset($rc[3]) ? h::{'input[type=hidden]'}([
							'name'		=> 'database',
							'value'		=> $rc[3]
						]) : '').
						(isset($rc[4]) ? h::{'input[type=hidden]'}([
							'name'		=> 'mirror',
							'value'		=> $rc[4]
						]) : '')
					])
				).
				h::button(
					$L->test_connection,
					[
						'onMouseDown'	=> 'db_test(\''.$a->action.'/test\');'
					]
				)
			);
		break;
		case 'delete':
			$a->buttons = false;
			$content = [];
			if (!isset($rc[4])) {
				foreach ($Config->components['modules'] as $module => &$mdata) {
					if (isset($mdata['db']) && is_array($mdata['db'])) {
						foreach ($mdata['db'] as $db_name) {
							if ($db_name == $rc[3]) {
								$content[] = h::b($module);
								break;
							}
						}
					}
				}
				unset($module, $mdata, $db_name);
			}
			if (!empty($content)) {
				global $Page;
				$Page->warning($L->db_used_by_modules.': '.implode(', ', $content));
			} else {
				$a->action	= 'admin/'.MODULE.'/'.$rc[0].'/'.$rc[1];
				$mirror		= isset($rc[4]);
				$cdb		= $Config->db[$rc[3]];
				if ($mirror) {
					$cdbm	= $Config->db[$rc[3]]['mirrors'][$rc[4]];
					$name	= $L->mirror.' '.($rc[3] ? $L->db.' '.$cdb['name'] : $L->core_db).', '.$cdbm['name'].' ('.$cdbm['host'].'/'.$cdbm['type'].')?';
					unset($cdbm);
				} else {
					$name	= $L->db.' '.$cdb['name'].' ('.$cdb['host'].'/'.$cdb['type'].')?';
				}
				unset($mirror, $cdb);
				$Page->title($L->deleting_a_database($name));
				$a->content(
					h::{'p.ui-priority-primary.cs-state-messages'}(
						$L->sure_to_delete.' '.$name.
							h::{'input[type=hidden]'}([
								'name'	=> 'mode',
								'value'	=> 'delete'
							]).
							h::{'input[type=hidden]'}([
								'name'	=> 'database',
								'value'	=> $rc[3]
							]).
							(isset($rc[4]) ?
								h::{'input[type=hidden]'}([
									'name'	=> 'mirror',
									'value'	=> $rc[4]
								])
							: '')
					).
					h::{'button[type=submit]'}($L->yes)
				);
			}
		break;
		case 'test':
			interface_off();
			$a->form			= false;
			$a->generate_auto	= false;
			global $Page, $db;
			if (isset($rc[4])) {
				$Page->content(
					h::{'p.cs-test-result'}(
						$db->test([$rc[3], $rc[4]]) ? $L->success : $L->fail
					)
				);
			} elseif (isset($rc[3])) {
				$Page->content(
					h::{'p.cs-test-result'}(
						$db->test([$rc[3]]) ? $L->success : $L->fail
					)
				);
			} else {
				$Page->content(
					h::{'p.cs-test-result'}(
						$db->test($_POST['db']) ? $L->success : $L->fail
					)
				);
			}
	}
} else {
	$test_dialog = true;
	$db_list = h::{'tr th.ui-widget-header.ui-corner-all'}([
		$L->action,
		$L->db_host,
		$L->db_type,
		$L->db_prefix,
		$L->db_name,
		$L->db_user,
		$L->db_charset
	]);
	foreach ($Config->db as $i => &$db_data) {
		$db_list .=	h::tr(
			h::td(
				h::{'a.cs-button.cs-button-compact'}(
					h::icon('plus'),
					[
						'href'			=> $a->action.'/add/'.$i,
						'data-title'	=> $L->add.' '.$L->mirror.' '.$L->of_db
					]
				).($i ? 
				h::{'a.cs-button.cs-button-compact'}(
					h::icon('wrench'),
					[
						'href'			=> $a->action.'/edit/'.$i,
						'data-title'	=> $L->edit.' '.$L->db
					]
				).
				h::{'a.cs-button.cs-button-compact'}(
					h::icon('trash'),
					[
						'href'			=> $a->action.'/delete/'.$i,
						'data-title'	=> $L->delete.' '.$L->db
					]
				) : '').
				h::{'a.cs-button.cs-button-compact'}(
					h::icon('signal-diag'),
					[
						'onMouseDown'	=> 'db_test(\''.$a->action.'/test/'.$i.'\', true);',
						'data-title'	=> $L->test_connection
					]
				),
				[
					'class'	=> 'ui-corner-all cs-db-config-buttons '.($i ? 'ui-widget-content' : 'ui-state-highlight')
				]
			).
			h::td(
				[
					$i	? $db_data['host']		: $DB_HOST,
					$i	? $db_data['type']		: $DB_TYPE,
					$i	? $db_data['prefix']	: $DB_PREFIX,
					$i	? $db_data['name']		: $DB_NAME,
					$i	? $db_data['user']		: '*****',
					$i	? $db_data['charset']	: $DB_CHARSET
				],
				[
					'class'	=> 'ui-corner-all '.($i ? 'ui-widget-content' : 'ui-state-highlight')
				]
			)
		);
		foreach ($Config->db[$i]['mirrors'] as $m => &$mirror) {
			if (is_array($mirror) && !empty($mirror)) {
				$db_list .=	h::tr(
					h::{'td.ui-widget-content.ui-corner-all.cs-db-config-buttons-r'}(
						h::{'a.cs-button.cs-button-compact'}(
							h::icon('wrench'),
							[
								'href'			=> 'admin/'.MODULE.'/'.$rc[0].'/'.$rc[1].'/edit/'.$i.'/'.$m,
								'data-title'	=> $L->edit.' '.$L->mirror.' '.$L->of_db
							]
						).
						h::{'a.cs-button.cs-button-compact'}(
							h::icon('trash'),
							[
								'href'			=> 'admin/'.MODULE.'/'.$rc[0].'/'.$rc[1].'/delete/'.$i.'/'.$m,
								'data-title'	=> $L->delete.' '.$L->mirror.' '.$L->of_db
							]
						).
						h::{'a.cs-button.cs-button-compact'}(
							h::icon('signal-diag'),
							[
								'onMouseDown'	=> 'db_test(\''.$a->action.'/test/'.$i.'/'.$m.'\', true);',
								'data-title'	=> $L->test_connection
							]
						)
					).
					h::{'td.ui-widget-content.ui-corner-all'}([
						$mirror['host'],
						$mirror['type'],
						$mirror['prefix'],
						$mirror['name'],
						$mirror['user'],
						$mirror['charset']
					])
				);
			}
		}
		unset($m, $mirror);
	}
	unset($i, $db_data);
	$a->content(
		h::{'table.cs-fullwidth-table'}(
			$db_list.
			h::{'tr td.cs-left-all[colspan=7]'}(
				h::button(
					$L->add_database,
					[
						'onMouseDown' => 'javasript: location.href= \'admin/'.MODULE.'/'.$rc[0].'/'.$rc[1].'/add\';'
					]
				).h::br()
			).
			h::tr(
				h::{'td.cs-right-all[colspan=4] info'}('db_balance').
				h::{'td.cs-left-all[colspan=3] input[type=radio]'}([
					'name'			=> 'core[db_balance]',
					'checked'		=> $Config->core['db_balance'],
					'value'			=> array(0, 1),
					'in'			=> array($L->off, $L->on)
				])
			).
			h::tr(
				h::{'td.cs-right-all[colspan=4] info'}('maindb_for_write').
				h::{'td.cs-left-all[colspan=3] input[type=radio]'}([
					'name'			=> 'core[maindb_for_write]',
					'checked'		=> $Config->core['maindb_for_write'],
					'value'			=> array(0, 1),
					'class'			=> array('cs-form-element'),
					'in'			=> array($L->off, $L->on)
				])
			)
		).
		h::{'input[type=hidden]'}([
			 'name'			=> 'mode',
			 'value'		=> 'config'
		])
	);
}
$test_dialog && $a->content(
	h::{'div#test_db.cs-dialog'}([
		'data-dialog'	=> '{"autoOpen":false,"height":"75","hide":"puff","modal":true,"show":"scale","width":"250"}',
		'title'			=> $L->test_connection
	])
);