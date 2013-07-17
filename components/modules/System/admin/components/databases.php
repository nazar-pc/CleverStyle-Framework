<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
$Config			= Config::instance();
$Core			= Core::instance();
$L				= Language::instance();
$Page			= Page::instance();
$a				= Index::instance();
$rc				= $Config->route;
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
			$a->action = 'admin/System/'.$rc[0].'/'.$rc[1];
			/**
			 * @var array $dbsname
			 * @var array $dbs
			 * @var array $database
			 * @var string $name
			 */
			$Page->title($rc[2] == 'edit' ? $L->editing_the_database($name) : $L->addition_of_db);
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
					$rc[2] == 'edit' ? $L->editing_the_database($name) : $L->addition_of_db
				).
				h::{'table.cs-fullwidth-table.cs-center-all tr'}(
					\cs\modules\System\form_rows_to_cols([
						array_map(
							function ($in) {
								return h::{'th.ui-widget-header.ui-corner-all info'}($in);
							},
							[
								$rc[2] == 'add' ? 'db_mirror' : false,
								'db_host',
								'db_type',
								'db_prefix',
								'db_name',
								'db_user',
								'db_password',
								'db_charset'
							]
						),
						array_map(
							function ($in) {
								return h::{'td.ui-widget-content.ui-corner-all'}($in);
							},
							[
								($rc[2] == 'add' ? h::select(
									[
										'in'		=> $dbsname,
										'value'		=> $dbs
									],
									[
										'name'		=> 'db[mirror]',
										'selected'	=> isset($rc[3]) ? $rc[3] : -1,
										'size'		=> 5
									]
								) : false),
								h::input([
									'name'		=> 'db[host]',
									'value'		=> $rc[2] == 'edit' ? $database['host'] : $Core->db_host
								]),
								h::select(
									[
										'in'		=> _mb_substr(get_files_list(ENGINES.'/DB', '/^[^_].*?\.php$/i', 'f'), 0, -4)
									],
									[
										'name'		=> 'db[type]',
										'selected'	=> $rc[2] == 'edit' ? $database['type'] : $Core->db_type,
										'size'		=> 5
									]
								),
								h::input([
									'name'		=> 'db[prefix]',
									'value'		=> $rc[2] == 'edit' ? $database['prefix'] : $Core->db_prefix
								]),
								h::input([
									'name'		=> 'db[name]',
									'value'		=> $rc[2] == 'edit' ? $database['name'] : ''
								]),
								h::input([
									'name'		=> 'db[user]',
									'value'		=> $rc[2] == 'edit' ? $database['user'] : ''
								]),
								h::input([
									'name'		=> 'db[password]',
									'value'		=> $rc[2] == 'edit' ? $database['password'] : ''
								]),
								h::input([
									'name'		=> 'db[charset]',
									'value'		=> $rc[2] == 'edit' ? $database['charset'] : $Core->db_charset
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
							]
						)
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
				$Page->warning($L->db_used_by_modules.': '.implode(', ', $content));
			} else {
				$a->action	= 'admin/System/'.$rc[0].'/'.$rc[1];
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
				$Page->title($L->deletion_of_database($name));
				$a->content(
					h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
						$L->sure_to_delete.' '.$name.
							h::{'input[type=hidden]'}([
								[[
									'name'	=> 'mode',
									'value'	=> $rc[2]
								]],
								[[
									'name'	=> 'database',
									'value'	=> $rc[3]
								]]
							]).
							(isset($rc[4]) ? h::{'input[type=hidden]'}([
								'name'	=> 'mirror',
								'value'	=> $rc[4]
							]) : '')
					).
					h::{'button[type=submit]'}($L->yes)
				);
			}
		break;
		case 'test':
			interface_off();
			$a->form			= false;
			$a->generate_auto	= false;
			$db					= DB::instance();
			if (isset($rc[4])) {
				$Page->content(
					h::{'p.cs-test-result'}(
						$db->test([$rc[3], $rc[4]]) ? $L->success : $L->failed
					)
				);
			} elseif (isset($rc[3])) {
				$Page->content(
					h::{'p.cs-test-result'}(
						$db->test([$rc[3]]) ? $L->success : $L->failed
					)
				);
			} else {
				$Page->content(
					h::{'p.cs-test-result'}(
						$db->test($_POST['db']) ? $L->success : $L->failed
					)
				);
			}
	}
} else {
	$test_dialog	= true;
	$db_list		= [h::{'th.ui-widget-header.ui-corner-all'}(
		$L->action,
		$L->db_host,
		$L->db_type,
		$L->db_prefix,
		$L->db_name,
		$L->db_user,
		$L->db_charset
	)];
	$databases		= $Config->db;
	if (!empty($databases)) {
		foreach ($databases as $i => &$db_data) {
			$db_list[]	= h::{'td.ui-corner-all'}(
				[
					[
						h::{'a.cs-button-compact'}(
							[
								h::icon('plus'),
								[
									'href'			=> $a->action.'/add/'.$i,
									'data-title'	=> $L->add.' '.$L->mirror.' '.$L->of_db
								]
							],
							$i ? [
								h::icon('wrench'),
								[
									'href'			=> $a->action.'/edit/'.$i,
									'data-title'	=> $L->edit.' '.$L->db
								]
							] : false,
							$i ? [
								h::icon('trash'),
								[
									'href'			=> $a->action.'/delete/'.$i,
									'data-title'	=> $L->delete.' '.$L->db
								]
							] : false,
							[
								h::icon('signal-diag'),
								[
									'onMouseDown'	=> 'db_test(\''.$a->action.'/test/'.$i.'\', true);',
									'data-title'	=> $L->test_connection
								]
							]
						),
						[
							'class'	=> 'cs-left-all'
						]
					],
					$i	? $db_data['host']		: $Core->db_host,
					$i	? $db_data['type']		: $Core->db_type,
					$i	? $db_data['prefix']	: $Core->db_prefix,
					$i	? $db_data['name']		: $Core->db_name,
					$i	? $db_data['user']		: '*****',
					$i	? $db_data['charset']	: $Core->db_charset
				],
				[
					'class'	=> $i ? 'ui-widget-content' : 'ui-state-highlight'
				]
			);
			foreach ($Config->db[$i]['mirrors'] as $m => &$mirror) {
				if (is_array($mirror) && !empty($mirror)) {
					$db_list[]	= h::{'td.ui-widget-content.ui-corner-all'}(
						[
							h::{'a.cs-button-compact'}(
								[
									h::icon('wrench'),
									[
										'href'			=> 'admin/System/'.$rc[0].'/'.$rc[1].'/edit/'.$i.'/'.$m,
										'data-title'	=> $L->edit.' '.$L->mirror.' '.$L->of_db
									]
								],
								[
									h::icon('trash'),
									[
										'href'			=> 'admin/System/'.$rc[0].'/'.$rc[1].'/delete/'.$i.'/'.$m,
										'data-title'	=> $L->delete.' '.$L->mirror.' '.$L->of_db
									]
								],
								[
									h::icon('signal-diag'),
									[
										'onMouseDown'	=> 'db_test(\''.$a->action.'/test/'.$i.'/'.$m.'\', true);',
										'data-title'	=> $L->test_connection
									]
								]
							),
							[
								'class'	=> 'cs-right-all'
							]
						],
						$mirror['host'],
						$mirror['type'],
						$mirror['prefix'],
						$mirror['name'],
						$mirror['user'],
						$mirror['charset']
					);
				}
			}
			unset($m, $mirror);
		}
		unset($i, $db_data);
	}
	unset($databases);
	$a->content(
		h::{'table.cs-fullwidth-table tr'}(
			$db_list,
			h::{'td.cs-left-all[colspan=7] a.cs-button'}(
				$L->add_database,
				[
					'href' => 'admin/System/'.$rc[0].'/'.$rc[1].'/add'
				]
			),
			h::{'td.cs-right-all[colspan=4] info'}('db_balance').
			h::{'td.cs-left-all[colspan=3] input[type=radio]'}([
				'name'			=> 'core[db_balance]',
				'checked'		=> $Config->core['db_balance'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on]
			]),
			h::{'td.cs-right-all[colspan=4] info'}('maindb_for_write').
			h::{'td.cs-left-all[colspan=3] input[type=radio]'}([
				'name'			=> 'core[maindb_for_write]',
				'checked'		=> $Config->core['maindb_for_write'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on]
			])
		).
		h::{'input[type=hidden]'}([
			 'name'			=> 'mode',
			 'value'		=> 'config'
		])
	);
}
$test_dialog && $a->content(
	h::{'div#test_db'}([
		'data-dialog'	=> '{"autoOpen":false,"height":"75","hide":"puff","modal":true,"show":"scale","width":"250"}',
		'title'			=> $L->test_connection
	])
);