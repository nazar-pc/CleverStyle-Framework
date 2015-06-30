<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\admin\Controller;
use
	cs\Config,
	cs\Core,
	cs\DB,
	cs\Event,
	cs\Index,
	cs\Language,
	cs\Page,
	cs\Route,
	cs\Session,
	cs\Text,
	h;

trait components {
	static function components_blocks ($route_ids, $route_path) {
		$Config = Config::instance();
		$L      = Language::instance();
		$Page   = Page::instance();
		$a      = Index::instance();
		$action = isset($route_path[2]) ? $route_path[2] : null;
		$id     = isset($route_ids[0]) ? $route_ids[0] : 0;
		$form   = true;
		if ($action == 'add' || isset($id, $Config->components['blocks'][$id])) {
			switch ($action) {
				case 'enable':
					$Config->components['blocks'][$id]['active'] = 1;
					$a->save();
					break;
				case 'disable':
					$Config->components['blocks'][$id]['active'] = 0;
					$a->save();
					break;
				case 'delete':
					$form                  = false;
					$a->buttons            = false;
					$a->cancel_button_back = true;
					$Page->title($L->deletion_of_block(static::get_block_title($id)));
					$a->content(
						h::{'h2.cs-center'}(
							$L->sure_to_delete_block(static::get_block_title($id)).
							h::{'input[type=hidden][name=mode][value=delete]'}().
							h::{'input[type=hidden][name=block[id]]'}(
								[
									'value' => $id
								]
							)
						).
						h::{'button.uk-button[type=submit]'}($L->yes)
					);
					break;
				case 'add':
					$form                  = false;
					$a->cancel_button_back = true;
					$a->form_attributes[]  = 'formnovalidate';
					$Page->title($L->adding_a_block);
					$a->content(
						h::{'h2.cs-center'}(
							$L->adding_a_block
						).
						h::{'cs-system-admin-components-blocks-form.uk-display-block.uk-margin-bottom script[type=application/json]'}(
							json_encode(
								[
									'types'      => array_merge(['html', 'raw_html'], _mb_substr(get_files_list(BLOCKS, '/^block\..*?\.php$/i', 'f'), 6, -4)),
									'templates'  => _mb_substr(get_files_list(TEMPLATES.'/blocks', '/^block\..*?\.(php|html)$/i', 'f'), 6),
									'block_data' => [
										'start'  => date('Y-m-d\TH:i', TIME),
										'expire' => [
											'date'  => date('Y-m-d\TH:i', TIME),
											'state' => false
										]
									]
								],
								JSON_UNESCAPED_UNICODE
							)
						).
						h::{'input[type=hidden][name=mode][value=add]'}()
					);
					break;
				case 'edit':
					$form                  = false;
					$a->cancel_button_back = true;
					$a->form_attributes[]  = 'formnovalidate';
					$block                 = &$Config->components['blocks'][$id];
					$Page->title($L->editing_a_block(static::get_block_title($id)));
					$a->content(
						h::{'h2.cs-center'}(
							$L->editing_a_block(static::get_block_title($id))
						).
						h::{'cs-system-admin-components-blocks-form.uk-display-block.uk-margin-bottom script[type=application/json]'}(
							json_encode(
								[
									'types'      => array_merge(['html', 'raw_html'], _mb_substr(get_files_list(BLOCKS, '/^block\..*?\.php$/i', 'f'), 6, -4)),
									'templates'  => _mb_substr(get_files_list(TEMPLATES.'/blocks', '/^block\..*?\.(php|html)$/i', 'f'), 6),
									'block_data' => [
										'title'    => static::get_block_title($id),
										'type'     => $block['type'],
										'active'   => $block['active'],
										'template' => $block['template'],
										'start'    => date('Y-m-d\TH:i', $block['start'] ?: TIME),
										'expire'   => [
											'date'  => date('Y-m-d\TH:i', $block['expire'] ?: TIME),
											'state' => $block['expire'] != 0
										],
										'content'  => static::get_block_content($id)
									]
								],
								JSON_UNESCAPED_UNICODE
							)
						).
						h::{'input[type=hidden][name=mode][value=edit]'}().
						h::{'input[type=hidden][name=block[id]]'}(
							[
								'value' => $id
							]
						)
					);
					break;
			}
		}
		if ($form) {
			$a->apply_button = true;
			$a->custom_buttons .= h::{'button.uk-button.cs-reload-button'}(
				$L->reset
			);
			$blocks_array = [
				'top'      => '',
				'left'     => '',
				'floating' => '',
				'right'    => '',
				'bottom'   => ''
			];
			if (!empty($Config->components['blocks'])) {
				foreach ($Config->components['blocks'] as $id => $block) {
					$blocks_array[$block['position']] .= h::li(
						h::{'div.cs-blocks-items-title'}("#$block[index] ".static::get_block_title($id)).
						h::a(
							[
								h::{'div icon'}('pencil'),
								[
									'href'       => "$a->action/edit/$id",
									'data-title' => $L->edit
								]
							],
							[
								h::{'div.cs-blocks-permissions icon'}('key'),
								[
									'data-title' => $L->edit_permissions
								]
							],
							[
								h::{'div icon'}($block['active'] ? 'minus' : 'check'),
								[
									'href'       => "$a->action/".($block['active'] ? 'disable' : 'enable')."/$id",
									'data-title' => $L->{$block['active'] ? 'disable' : 'enable'}
								]
							],
							[
								h::{'div icon'}('trash-o'),
								[
									'href'       => "$a->action/delete/$id",
									'data-title' => $L->delete
								]
							]
						),
						[
							'data-id'          => $id,
							'data-index'       => $block['index'],
							'data-block-title' => h::prepare_attr_value(static::get_block_title($id)),
							'class'            => $block['active'] ? 'uk-button-success' : 'uk-button-default'
						]
					);
					unset($block_data);
				}
				unset($id, $block);
			}
			foreach ($blocks_array as $position => &$content) {
				$content = h::{'cs-table-cell.cs-blocks-items-groups ul.cs-blocks-items'}(
					h::{'li.uk-button-primary'}(
						$L->{"{$position}_blocks"},
						[
							'onClick' => "cs.blocks_toggle('$position');"
						]
					).
					$content,
					[
						'data-mode' => 'open',
						'id'        => "cs-{$position}-blocks-items"
					]
				);
			}
			unset($position, $content);
			$a->content(
				h::{'cs-table cs-table-row'}(
					[
						h::cs_table_cell().$blocks_array['top'].h::cs_table_cell(),
						"$blocks_array[left]$blocks_array[floating]$blocks_array[right]",
						h::cs_table_cell().$blocks_array['bottom'].h::cs_table_cell()
					]
				).
				h::{'p.cs-left a.uk-button'}(
					"$L->add $L->block",
					[
						'href' => "$a->action/add"
					]
				).
				h::{'input#cs-blocks-position[type=hidden][name=position]'}()
			);
		}
	}
	static private function get_block_title ($id) {
		$Config = Config::instance();
		return Text::instance()->process($Config->module('System')->db('texts'), $Config->components['blocks'][$id]['title']);
	}
	static private function get_block_content ($id) {
		$Config = Config::instance();
		return Text::instance()->process($Config->module('System')->db('texts'), $Config->components['blocks'][$id]['content']);
	}
	static function components_databases ($route_ids, $route_path) {
		$Config       = Config::instance();
		$Core         = Core::instance();
		$L            = Language::instance();
		$Page         = Page::instance();
		$a            = Index::instance();
		$action       = isset($route_path[2]) ? $route_path[2] : null;
		$db_id        = isset($route_ids[0]) ? $route_ids[0] : false;
		$db_mirror_id = isset($route_ids[1]) ? $route_ids[1] : false;
		if ($action) {
			$a->cancel_button_back = true;
			switch ($action) {
				case 'edit':
					if ($db_id === false) {
						break;
					}
				case 'add':
					if ($action == 'edit') {
						if ($db_mirror_id !== false) {
							$parent_db = $Config->db[$db_id];
							$database  = &$Config->db[$db_id]['mirrors'][$db_mirror_id];
							$name      =
								"$L->mirror ".
								($db_id !== false ? "$L->db $parent_db[name]" : $L->core_db).
								", $database[name] ($database[host]/$database[type])?";
							unset($parent_db);
						} else {
							$database = &$Config->db[$db_id];
							$name     = "$L->db $database[name] ($database[host]/$database[type])?";
						}
					} elseif ($action == 'add') {
						$dbs     = [-1, 0];
						$dbsname = [$L->separate_db, $L->core_db];
						foreach ($Config->db as $i => $db) {
							if ($i) {
								$dbs[]     = $i;
								$dbsname[] = $db['name'];
							}
						}
						unset($i, $db);
					}
					/**
					 * @var array  $dbsname
					 * @var array  $dbs
					 * @var array  $database
					 * @var string $name
					 */
					$Page->title($action == 'edit' ? $L->editing_the_database($name) : $L->addition_of_db);
					$a->content(
						h::{'h2.cs-center'}(
							$action == 'edit' ? $L->editing_the_database($name) : $L->addition_of_db
						).
						static::vertical_table(
							[
								h::info($action == 'add' ? 'db_mirror' : false),
								$action == 'add'
									? h::select(
									[
										'in'    => $dbsname,
										'value' => $dbs
									],
									[
										'name'     => 'db[mirror]',
										'selected' => $db_id !== false ? $db_id : -1,
										'size'     => 5
									]
								)
									: false
							],
							[
								h::info('db_host'),
								h::input(
									[
										'name'  => 'db[host]',
										'value' => $action == 'edit' ? $database['host'] : $Core->db_host
									]
								)
							],
							[
								h::info('db_type'),
								h::select(
									[
										'in' => _mb_substr(get_files_list(ENGINES.'/DB', '/^[^_].*?\.php$/i', 'f'), 0, -4)
									],
									[
										'name'     => 'db[type]',
										'selected' => $action == 'edit' ? $database['type'] : $Core->db_type,
										'size'     => 5
									]
								)
							],
							[
								h::info('db_prefix'),
								h::input(
									[
										'name'  => 'db[prefix]',
										'value' => $action == 'edit' ? $database['prefix'] : $Core->db_prefix
									]
								)
							],
							[
								h::info('db_name'),
								h::input(
									[
										'name'  => 'db[name]',
										'value' => $action == 'edit' ? $database['name'] : ''
									]
								)
							],
							[
								h::info('db_user'),
								h::input(
									[
										'name'  => 'db[user]',
										'value' => $action == 'edit' ? $database['user'] : ''
									]
								)
							],
							[
								h::info('db_password'),
								h::input(
									[
										'name'  => 'db[password]',
										'value' => $action == 'edit' ? $database['password'] : ''
									]
								)
							],
							[
								h::info('db_charset'),
								h::input(
									[
										'name'  => 'db[charset]',
										'value' => $action == 'edit' ? $database['charset'] : $Core->db_charset
									]
								).
								h::{'input[type=hidden]'}(
									[
										'name'  => 'mode',
										'value' => $action == 'edit' ? 'edit' : 'add'
									]
								)
							]
						).
						(
						$db_id !== false
							? h::{'input[type=hidden]'}(
							[
								'name'  => 'database',
								'value' => $db_id
							]
						)
							: ''
						).
						(
						$db_mirror_id !== false
							? h::{'input[type=hidden]'}(
							[
								'name'  => 'mirror',
								'value' => $db_mirror_id
							]
						)
							: ''
						).
						h::{'button.uk-button'}(
							$L->test_connection,
							[
								'onMouseDown' => "cs.db_test();"
							]
						)
					);
					break;
				case 'delete':
					$a->buttons = false;
					$content    = [];
					if ($db_mirror_id === false) {
						foreach ($Config->components['modules'] as $module => &$mdata) {
							if (isset($mdata['db']) && is_array($mdata['db'])) {
								foreach ($mdata['db'] as $db_name) {
									if ($db_name == $db_id) {
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
						$parent_db = $Config->db[$db_id];
						if ($db_mirror_id !== false) {
							$current_db_mirror = $Config->db[$db_id]['mirrors'][$db_mirror_id];
							$name              =
								"$L->mirror ".
								($db_id !== false ? "$L->db $parent_db[name]" : $L->core_db).
								", $current_db_mirror[name] ($current_db_mirror[host]/$current_db_mirror[type])?";
							unset($current_db_mirror);
						} else {
							$name = "$L->db $parent_db[name] ($parent_db[host]/$parent_db[type])?";
						}
						unset($parent_db);
						$Page->title($L->deletion_of_database($name));
						$a->content(
							h::{'h2.cs-center'}(
								$L->sure_to_delete.' '.$name.
								h::{'input[type=hidden]'}(
									[
										[
											[
												'name'  => 'mode',
												'value' => $action
											]
										],
										[
											[
												'name'  => 'database',
												'value' => $db_id
											]
										]
									]
								).
								($db_mirror_id !== false ? h::{'input[type=hidden]'}(
									[
										'name'  => 'mirror',
										'value' => $db_mirror_id
									]
								) : '')
							).
							h::{'button.uk-button[type=submit]'}($L->yes)
						);
					}
			}
		} else {
			$a->apply_button = true;
			$db_list         = [];
			$databases       = $Config->db;
			if (!empty($databases)) {
				foreach ($databases as $i => &$db_data) {
					$db_list[] = [
						[
							[
								h::{'a.uk-button.cs-button-compact'}(
									[
										h::icon('plus'),
										[
											'href'       => "$a->action/add/$i",
											'data-title' => "$L->add $L->mirror $L->of_db"
										]
									],
									$i ? [
										h::icon('pencil'),
										[
											'href'       => "$a->action/edit/$i",
											'data-title' => "$L->edit $L->db"
										]
									] : false,
									$i ? [
										h::icon('trash-o'),
										[
											'href'       => "$a->action/delete/$i",
											'data-title' => $L->delete.' '.$L->db
										]
									] : false,
									[
										h::icon('signal'),
										[
											'onMouseDown' => "cs.db_test($i);",
											'data-title'  => $L->test_connection
										]
									]
								),
								[
									'class' => 'cs-left-all'
								]
							],
							$i ? $db_data['host'] : $Core->db_host,
							$i ? $db_data['type'] : $Core->db_type,
							$i ? $db_data['prefix'] : $Core->db_prefix,
							$i ? $db_data['name'] : $Core->db_name,
							$i ? $db_data['user'] : '*****',
							$i ? $db_data['charset'] : $Core->db_charset
						],
						[
							'class' => $i ? '' : 'text-primary'
						]
					];
					foreach ($Config->db[$i]['mirrors'] as $m => &$mirror) {
						if (is_array($mirror) && !empty($mirror)) {
							$db_list[] = [
								[
									h::{'a.uk-button.cs-button-compact'}(
										[
											h::icon('pencil'),
											[
												'href'       => "$a->action/edit/$i/$m",
												'data-title' => "$L->edit $L->mirror $L->of_db"
											]
										],
										[
											h::icon('trash-o'),
											[
												'href'       => "$a->action/delete/$i/$m",
												'data-title' => "$L->delete $L->mirror $L->of_db"
											]
										],
										[
											h::icon('signal'),
											[
												'onMouseDown' => "cs.db_test($i, $m);",
												'data-title'  => $L->test_connection
											]
										]
									),
									[
										'class' => 'cs-right-all'
									]
								],
								$mirror['host'],
								$mirror['type'],
								$mirror['prefix'],
								$mirror['name'],
								$mirror['user'],
								$mirror['charset']
							];
						}
					}
					unset($m, $mirror);
				}
				unset($i, $db_data);
			}
			unset($databases);
			$a->content(
				static::list_center_table(
					[
						$L->action,
						$L->db_host,
						$L->db_type,
						$L->db_prefix,
						$L->db_name,
						$L->db_user,
						$L->db_charset
					],
					$db_list
				).
				static::vertical_table(
					[
						[
							h::info('db_balance'),
							h::radio(
								[
									'name'    => 'core[db_balance]',
									'checked' => $Config->core['db_balance'],
									'value'   => [0, 1],
									'in'      => [$L->off, $L->on]
								]
							)
						],
						[
							h::info('db_mirror_mode'),
							h::radio(
								[
									'name'    => 'core[db_mirror_mode]',
									'checked' => $Config->core['db_mirror_mode'],
									'value'   => [DB::MIRROR_MODE_MASTER_MASTER, DB::MIRROR_MODE_MASTER_SLAVE],
									'in'      => [$L->master_master, $L->master_slave]
								]
							)
						]
					]
				).
				h::{'p a.uk-button'}(
					$L->add_database,
					[
						'href' => "$a->action/add"
					]
				).
				h::{'input[type=hidden]'}(
					[
						'name'  => 'mode',
						'value' => 'config'
					]
				)
			);
		}
	}
	/**
	 * Provides next events:
	 *  admin/System/components/modules/install/prepare
	 *  ['name' => module_name]
	 *
	 *  admin/System/components/modules/update/prepare
	 *  ['name' => module_name]
	 *
	 *  admin/System/components/modules/uninstall/prepare
	 *  ['name' => module_name]
	 *
	 *  admin/System/components/modules/update_system/prepare
	 *
	 *  admin/System/components/modules/default_module/prepare
	 *  ['name' => module_name]
	 *
	 *  admin/System/components/modules/db/prepare
	 *  ['name' => module_name]
	 *
	 *  admin/System/components/modules/storage/prepare
	 *  ['name' => module_name]
	 *
	 *  admin/System/components/modules/enable/prepare
	 *  ['name' => module_name]
	 *
	 *  admin/System/components/modules/disable/prepare
	 *  ['name' => module_name]
	 */
	static function components_modules () {
		$Config       = Config::instance();
		$L            = Language::instance();
		$Page         = Page::instance();
		$Session      = Session::instance();
		$a            = Index::instance();
		$rc           = Route::instance()->route;
		$a->buttons   = false;
		$show_modules = true;
		if (
			isset($rc[2]) &&
			!empty($rc[2]) &&
			(
				in_array($rc[2], ['update_system', 'remove']) ||
				(
					isset($rc[3], $Config->components['modules'][$rc[3]]) ||
					(
						isset($rc[3]) && $rc[2] == 'install' && $rc[3] == 'upload'
					)
				)
			)
		) {
			switch ($rc[2]) {
				case 'install':
					if ($rc[3] == 'upload') {
						$tmp_file = static::move_uploaded_file_to_tmp('upload_module');
						if (!$tmp_file) {
							break;
						}
						$tmp_dir = "phar://$tmp_file";
						$meta    = file_exists("$tmp_dir/meta.json") ? file_get_json("$tmp_dir/meta.json") : false;
						if (
							!$meta ||
							@$meta['category'] != 'modules' ||
							!@$meta['package']
						) {
							$Page->warning($L->this_is_not_module_installer_file);
							unlink($tmp_file);
							break;
						}
						$module_name = $meta['package'];
						$rc[3]       = $module_name;
						if (isset($Config->components['modules'][$module_name])) {
							$current_version = file_get_json(MODULES."/$module_name/meta.json")['version'];
							$new_version     = $meta['version'];
							if (!version_compare($current_version, $new_version, '<')) {
								$Page->warning($L->update_module_impossible_older_version($module_name));
								unlink($tmp_file);
								break;
							}
							if (!Event::instance()->fire(
								'admin/System/components/modules/update/prepare',
								[
									'name' => $module_name
								]
							)
							) {
								break;
							}
							$check_dependencies = static::check_dependencies($meta, true);
							if (!$check_dependencies && $Config->core['simple_admin_mode']) {
								break;
							}
							$rc[2]        = 'update';
							$show_modules = false;
							$Page->title($L->updating_of_module($module_name));
							rename($tmp_file, $tmp_file = TEMP.'/'.$Session->get_id().'_module_update.phar');
							$a->content(
								h::{'h2.cs-center'}(
									$L->update_module(
										$module_name,
										$current_version,
										$new_version
									)
								)
							);
							$a->cancel_button_back = true;
							$a->content(
								h::{'button.uk-button[type=submit]'}($L->{$check_dependencies ? $L->yes : 'force_update_not_recommended'})
							);
							break;
						}
						if (!file_exists(MODULES."/$module_name") && !mkdir(MODULES."/$module_name", 0770)) {
							$Page->warning($L->cant_unpack_module_no_write_permissions);
							unlink($tmp_file);
							break;
						}
						$extract = static::install_extract(MODULES."/$module_name", $tmp_file);
						unset($tmp_file, $tmp_dir);
						if (!$extract) {
							$Page->warning($L->module_files_unpacking_error);
							break;
						}
						$Config->components['modules'][$module_name] = [
							'active'  => -1,
							'db'      => [],
							'storage' => []
						];
						unset($meta, $module_name);
						ksort($Config->components['modules'], SORT_STRING | SORT_FLAG_CASE);
						$Config->save();
					} elseif ($rc[3] == 'upload') {
						break;
					}
					$show_modules = false;
					$Page->title($L->installation_of_module($rc[3]));
					$a->content(
						h::{'h2.cs-center'}(
							$L->installation_of_module($rc[3])
						)
					);
					if (!Event::instance()->fire(
						'admin/System/components/modules/install/prepare',
						[
							'name' => $rc[3]
						]
					)
					) {
						break;
					}
					$check_dependencies = static::check_dependencies(@file_get_json(MODULES."/$rc[3]/meta.json"));
					if (!$check_dependencies && $Config->core['simple_admin_mode']) {
						break;
					}
					if (file_exists(MODULES."/$rc[3]/meta.json")) {
						$meta = file_get_json(MODULES."/$rc[3]/meta.json");
						if (isset($meta['optional'])) {
							$Page->success(
								$L->for_complete_feature_set(
									implode(', ', (array)$meta['optional'])
								)
							);
						}
						if ($Config->core['simple_admin_mode']) {
							if (isset($meta['db'])) {
								foreach ($meta['db'] as $database) {
									$a->content(
										h::{'input[type=hidden]'}(
											[
												'name'  => "db[$database]",
												'value' => 0
											]
										)
									);
								}
								unset($database);
							}
							if (isset($meta['storage'])) {
								foreach ($meta['storage'] as $storage) {
									$a->content(
										h::{'input[type=hidden]'}(
											[
												'name'  => "storage[$storage]",
												'value' => 0
											]
										)
									);
								}
								unset($storage);
							}
						} else {
							goto module_db_settings;
							back_to_module_installation_1:
							goto module_storage_settings;
							back_to_module_installation_2:
						}
						unset($meta);
					}
					$a->cancel_button_back = true;
					$a->content(
						h::{'button.uk-button[type=submit]'}(
							$L->{$check_dependencies ? 'install' : 'force_install_not_recommended'}
						)
					);
					break;
				case 'uninstall':
					$show_modules = false;
					$Page->title($L->uninstallation_of_module($rc[3]));
					$a->content(
						h::{'h2.cs-center'}(
							$L->uninstallation_of_module($rc[3])
						)
					);
					if (!Event::instance()->fire(
						'admin/System/components/modules/uninstall/prepare',
						[
							'name' => $rc[3]
						]
					)
					) {
						break;
					}
					$check_dependencies = static::check_backward_dependencies(@file_get_json(MODULES."/$rc[3]/meta.json"));
					if (!$check_dependencies && $Config->core['simple_admin_mode']) {
						break;
					}
					$a->cancel_button_back = true;
					$a->content(
						h::{'button.uk-button[type=submit]'}(
							$L->{$check_dependencies ? 'uninstall' : 'force_uninstall_not_recommended'}
						)
					);
					break;
				case 'update_system':
					$tmp_file = static::move_uploaded_file_to_tmp('upload_system');
					if (!$tmp_file) {
						break;
					}
					$tmp_dir = "phar://$tmp_file";
					if (
						!file_exists("$tmp_dir/meta.json") ||
						!file_exists("$tmp_dir/modules.json") ||
						!file_exists("$tmp_dir/plugins.json") ||
						!file_exists("$tmp_dir/themes.json")
					) {
						$Page->warning($L->this_is_not_system_installer_file);
						unlink($tmp_file);
						break;
					}
					$meta            = file_get_json("$tmp_dir/meta.json");
					$current_version = file_get_json(MODULES.'/System/meta.json')['version'];
					if (!version_compare($current_version, $meta['version'], '<')) {
						$Page->warning($L->update_system_impossible_older_version);
						unlink($tmp_file);
						break;
					}
					if (isset($meta['update_from_version']) && version_compare($meta['update_from_version'], $current_version, '>')) {
						$Page->warning(
							$L->update_system_impossible_from_version_to($current_version, $meta['version'], $meta['update_from_version'])
						);
						unlink($tmp_file);
						break;
					}
					$rc[2]        = 'update_system';
					$show_modules = false;
					if (!Event::instance()->fire('admin/System/components/modules/update_system/prepare')) {
						break;
					}
					$Page->title($L->updating_of_system);
					rename($tmp_file, $tmp_file = TEMP.'/'.$Session->get_id().'_update_system.phar');
					$a->content(
						h::{'h2.cs-center'}(
							$L->update_system(
								$current_version,
								$meta['version']
							)
						).
						h::{'button.uk-button[type=submit]'}($L->yes)
					);
					unset($meta);
					$rc[3]                 = 'System';
					$a->cancel_button_back = true;
					break;
				case 'default_module':
					$show_modules = false;
					$Page->title($L->setting_default_module($rc[3]));
					$a->content(
						h::{'h2.cs-center'}(
							$L->setting_default_module($rc[3])
						)
					);
					if (!Event::instance()->fire(
						'admin/System/components/modules/default_module/prepare',
						[
							'name' => $rc[3]
						]
					)
					) {
						break;
					}
					$a->cancel_button_back = true;
					$a->content(
						h::{'button.uk-button[type=submit]'}($L->yes)
					);
					break;
				case 'db':
					$show_modules = false;
					if (count($Config->db) > 1) {
						$Page->warning($L->changing_settings_warning);
						$Page->title($L->db_settings_for_module($rc[3]));
						$a->content(
							h::{'h2.cs-center'}(
								$L->db_settings_for_module($rc[3])
							)
						);
						if (!Event::instance()->fire(
							'admin/System/components/modules/db/prepare',
							[
								'name' => $rc[3]
							]
						)
						) {
							break;
						}
						$a->buttons            = true;
						$a->cancel_button_back = true;
						module_db_settings:
						$Core = Core::instance();
						$dbs  = [0 => "$L->core_db ($Core->db_type)"];
						foreach ($Config->db as $i => &$db_data) {
							if ($i) {
								$dbs[$i] = "$db_data[name] ($db_data[host] / $db_data[type])";
							}
						}
						unset($i, $db_data);
						$db_list = [];
						foreach (file_get_json(MODULES."/$rc[3]/meta.json")['db'] as $database) {
							$db_list[] = [
								$database,
								h::select(
									[
										'in'    => array_values($dbs),
										'value' => array_keys($dbs)
									],
									[
										'name'     => "db[$database]",
										'selected' => isset($Config->components['modules'][$rc[3]]['db'][$database]) ?
											$Config->components['modules'][$rc[3]]['db'][$database] : 0,
										'size'     => 5
									]
								)
							];
						}
						unset($dbs, $database);
						if ($db_list) {
							$a->content(
								h::{'cs-table[right-left][with-header] cs-table-row| cs-table-cell'}(
									[
										h::info('appointment_of_db'),
										h::info('system_db')
									],
									$db_list
								)
							);
						}
						unset($db_list);
						if ($rc[2] == 'install') {
							goto back_to_module_installation_1;
						}
					}
					break;
				case 'storage':
					$show_modules = false;
					if (count($Config->storage) > 1) {
						$Page->warning($L->changing_settings_warning);
						$Page->title($L->storage_settings_for_module($rc[3]));
						$a->content(
							h::{'h2.cs-center'}(
								$L->storage_settings_for_module($rc[3])
							)
						);
						if (!Event::instance()->fire(
							'admin/System/components/modules/storage/prepare',
							[
								'name' => $rc[3]
							]
						)
						) {
							break;
						}
						$a->buttons            = true;
						$a->cancel_button_back = true;
						module_storage_settings:
						$storages = [0 => $L->core_storage];
						foreach ($Config->storage as $i => &$storage_data) {
							if ($i) {
								$storages[$i] = "$storage_data[host] ($storage_data[connection])";
							}
						}
						unset($i, $storage_data);
						$storage_list = [];
						foreach (file_get_json(MODULES."/$rc[3]/meta.json")['storage'] as $storage) {
							$storage_list[] = [
								$storage,
								h::select(
									[
										'in'    => array_values($storages),
										'value' => array_keys($storages)
									],
									[
										'name'     => "storage[$storage]",
										'selected' => isset($Config->components['modules'][$rc[3]]['storage'][$storage]) ?
											$Config->components['modules'][$rc[3]]['storage'][$storage] : 0,
										'size'     => 5
									]
								)
							];
						}
						unset($storages, $storage);
						if ($storage_list) {
							$a->content(
								h::{'cs-table[right-left][with-header] cs-table-row| cs-table-cell'}(
									[
										h::info('appointment_of_storage'),
										h::info('system_storage')
									],
									$storage_list
								)
							);
						}
						unset($storage_list);
						if ($rc[2] == 'install') {
							goto back_to_module_installation_2;
						}
					}
					break;
				case 'enable':
					$show_modules       = false;
					$check_dependencies = static::check_dependencies(@file_get_json(MODULES."/$rc[3]/meta.json"));
					if (!$check_dependencies && $Config->core['simple_admin_mode']) {
						break;
					}
					Event::instance()->fire(
						'admin/System/components/modules/enable/prepare',
						[
							'name' => $rc[3]
						]
					);
					$Page->title($L->enabling_of_module($rc[3]));
					$a->content(
						h::{'h2.cs-center'}(
							$L->enable_module($rc[3])
						)
					);
					$a->cancel_button_back = true;
					$a->content(
						h::{'button.uk-button[type=submit]'}($L->{$check_dependencies ? 'yes' : 'force_enable_not_recommended'})
					);
					break;
				case 'disable':
					$show_modules       = false;
					$check_dependencies = static::check_backward_dependencies(@file_get_json(MODULES."/$rc[3]/meta.json"));
					if (!$check_dependencies && $Config->core['simple_admin_mode']) {
						break;
					}
					Event::instance()->fire(
						'admin/System/components/modules/disable/prepare',
						[
							'name' => $rc[3]
						]
					);
					$Page->title($L->disabling_of_module($rc[3]));
					$a->content(
						h::{'h2.cs-center'}(
							$L->disable_module($rc[3])
						)
					);
					$a->cancel_button_back = true;
					$a->content(
						h::{'button.uk-button[type=submit]'}($L->{$check_dependencies ? 'yes' : 'force_disable_not_recommended'})
					);
					break;
				case 'remove':
					$show_modules = false;
					$Page->title($L->complete_removal_of_module($_POST['remove_module']));
					$a->content(
						h::{'h2.cs-center'}(
							$L->completely_remove_module($_POST['remove_module'])
						)
					);
					$a->cancel_button_back = true;
					$a->content(
						h::{'button.uk-button[type=submit]'}($L->yes)
					);
					$rc[3] = $_POST['remove_module'];
					break;
			}
			switch ($rc[2]) {
				case 'install':
				case 'uninstall':
				case 'update':
				case 'update_system':
				case 'default_module':
				case 'db':
				case 'storage':
				case 'enable':
				case 'disable':
				case 'remove':
					$a->content(
						h::{'input[type=hidden]'}(
							[
								'name'  => 'mode',
								'value' => $rc[2]
							]
						).
						h::{'input[type=hidden]'}(
							[
								'name'  => 'module',
								'value' => $rc[3]
							]
						)
					);
			}
		}
		unset($rc);
		if (!$show_modules) {
			return;
		}
		$a->file_upload = true;
		$modules_list   = [];
		foreach ($Config->components['modules'] as $module_name => &$module_data) {
			$module = [
				'active'                => $module_data['active'],
				'name'                  => $module_name,
				'is_default'            => $module_name == $Config->core['default_module'],
				'can_be_set_as_default' =>
					$module_data['active'] == 1 &&
					$module_name != $Config->core['default_module'] &&
					file_exists_with_extension(MODULES."/$module_name/index", ['php', 'html', 'json']),
				'db_settings'           => !$Config->core['simple_admin_mode'] && @$module_data['db'] && count($Config->db) > 1,
				'storage_settings'      => !$Config->core['simple_admin_mode'] && @$module_data['storage'] && count($Config->storage) > 1,
				'administration'        => file_exists_with_extension(MODULES."/$module_name/admin/index", ['php', 'json'])
			];
			/**
			 * Check if API available
			 */
			if (is_dir(MODULES."/$module_name/api")) {
				$module['api'] = [];
				$file          = file_exists_with_extension(MODULES."/$module_name/api/readme", ['txt', 'html']);
				if ($file) {
					$module['api'] = [
						'type'    => substr($file, -3) == 'txt' ? 'txt' : 'html',
						'content' => file_get_contents($file)
					];
				}
				unset($file);
			}
			/**
			 * Check if readme available
			 */
			$file = file_exists_with_extension(MODULES."/$module_name/readme", ['txt', 'html']);
			if ($file) {
				$module['readme'] = [
					'type'    => substr($file, -3) == 'txt' ? 'txt' : 'html',
					'content' => file_get_contents($file)
				];
			}
			unset($file);
			/**
			 * Check if license available
			 */
			$file = file_exists_with_extension(MODULES."/$module_name/license", ['txt', 'html']);
			if ($file) {
				$module['license'] = [
					'type'    => substr($file, -3) == 'txt' ? 'txt' : 'html',
					'content' => file_get_contents($file)
				];
			}
			unset($file);
			if (file_exists(MODULES."/$module_name/meta.json")) {
				$module['meta'] = file_get_json(MODULES."/$module_name/meta.json");
			}
			$modules_list[] = $module;
		}
		unset($module_name, $module_data, $module);
		$modules_for_removal = array_keys(
			array_filter(
				$Config->components['modules'],
				function ($module_data) {
					return $module_data['active'] == '-1';
				}
			)
		);
		$a->content(
			h::{'cs-system-admin-components-modules-list script[type=application/json]'}(
				json_encode($modules_list, JSON_UNESCAPED_UNICODE)
			).
			h::p(
				h::{'input[type=file][name=upload_module]'}().
				h::{'button.uk-button[type=submit]'}(
					h::icon('upload').$L->upload_and_install_update_module,
					[
						'formaction' => "$a->action/install/upload"
					]
				)
			).
			h::p(
				h::{'input[type=file][name=upload_system]'}().
				h::{'button.uk-button[type=submit]'}(
					h::icon('upload').$L->upload_and_update_system,
					[
						'formaction' => "$a->action/update_system"
					]
				)
			).
			($modules_for_removal ? h::p(
				h::{'select[name=remove_module]'}($modules_for_removal).
				h::{'button.uk-button[type=submit]'}(
					h::icon('trash-o').$L->complete_module_removal,
					[
						'formaction' => "$a->action/remove"
					]
				)
			) : '').
			h::{'button.uk-button[type=submit]'}(
				h::icon('refresh').$L->update_modules_list,
				[
					'data-title' => $L->update_modules_list_info,
					'name'       => 'update_modules_list'
				]
			)
		);
	}
	/**
	 * Provides next events:
	 *  admin/System/components/plugins/update/prepare
	 *  ['name' => plugin_name]
	 *
	 *  admin/System/components/plugins/enable/prepare
	 *  ['name' => plugin_name]
	 *
	 *  admin/System/components/plugins/disable/prepare
	 *  ['name' => plugin_name]
	 *
	 */
	static function components_plugins () {
		$Config     = Config::instance();
		$L          = Language::instance();
		$Page       = Page::instance();
		$a          = Index::instance();
		$rc         = Route::instance()->route;
		$plugins    = get_files_list(PLUGINS, false, 'd');
		$a->buttons = false;
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
					if ($rc[3] == 'upload') {
						$tmp_file = static::move_uploaded_file_to_tmp('upload_plugin');
						if (!$tmp_file) {
							break;
						}
						$tmp_dir = "phar://$tmp_file";
						$meta    = file_exists("$tmp_dir/meta.json") ? file_get_json("$tmp_dir/meta.json") : false;
						if (
							!$meta ||
							@$meta['category'] != 'plugins' ||
							!@$meta['package']
						) {
							$Page->warning($L->this_is_not_plugin_installer_file);
							unlink($tmp_file);
							break;
						}
						$plugin = $meta['package'];
						$rc[3]  = $plugin;
						if (in_array($plugin, $plugins)) {
							$current_version = file_get_json(PLUGINS."/$plugin/meta.json")['version'];
							$new_version     = $meta['version'];
							if (!version_compare($current_version, $new_version, '<')) {
								$Page->warning($L->update_plugin_impossible_older_version($plugin));
								unlink($tmp_file);
								break;
							}
							if (!Event::instance()->fire(
								'admin/System/components/plugins/update/prepare',
								[
									'name' => $plugin
								]
							)
							) {
								break;
							}
							$check_dependencies = static::check_dependencies($meta);
							if (!$check_dependencies && $Config->core['simple_admin_mode']) {
								break;
							}
							$rc[2] = 'update';
							$Page->title($L->updating_of_plugin($plugin));
							rename($tmp_file, $tmp_file = TEMP.'/'.Session::instance()->get_id().'_plugin_update.phar.php');
							$a->content(
								h::{'h2.cs-center'}(
									$L->update_plugin(
										$plugin,
										$current_version,
										$new_version
									)
								).
								h::{'input[type=hidden]'}(
									[
										'name'  => 'mode',
										'value' => $rc[2]
									]
								).
								h::{'input[type=hidden]'}(
									[
										'name'  => 'plugin',
										'value' => $rc[3]
									]
								)
							);
							$a->cancel_button_back = true;
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
						$extract = static::install_extract(PLUGINS."/$plugin", $tmp_file);
						unset($tmp_dir);
						if (!$extract) {
							$Page->warning($L->plugin_files_unpacking_error);
							break;
						}
						$plugins[] = $plugin;
						unset($tmp_file, $meta, $plugin);
					}
					/** @noinspection NotOptimalIfConditionsInspection */
					if (!in_array($rc[3], $Config->components['plugins']) && in_array($rc[3], $plugins)) {
						$Page->title($L->enabling_of_plugin($rc[3]));
						$a->content(
							h::{'h2.cs-center'}(
								$L->enabling_of_plugin($rc[3])
							)
						);
						if (!Event::instance()->fire(
							'admin/System/components/plugins/enable/prepare',
							[
								'name' => $rc[3]
							]
						)
						) {
							break;
						}
						$check_dependencies = static::check_dependencies(@file_get_json(PLUGINS."/$rc[3]/meta.json"));
						if (!$check_dependencies && $Config->core['simple_admin_mode']) {
							break;
						}
						if (file_exists(PLUGINS."/$rc[3]/meta.json")) {
							$meta = file_get_json(PLUGINS."/$rc[3]/meta.json");
							if (isset($meta['optional'])) {
								$Page->success(
									$L->for_complete_feature_set(
										implode(', ', (array)$meta['optional'])
									)
								);
							}
							unset($meta);
						}
						$a->cancel_button_back = true;
						$a->content(
							h::{'button.uk-button[type=submit]'}(
								$L->{$check_dependencies ? 'enable' : 'force_enable_not_recommended'}
							).
							h::{'input[type=hidden]'}(
								[
									'name'  => 'mode',
									'value' => $rc[2]
								]
							).
							h::{'input[type=hidden]'}(
								[
									'name'  => 'plugin',
									'value' => $rc[3]
								]
							)
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
						if (!Event::instance()->fire(
							'admin/System/components/plugins/disable/prepare',
							[
								'name' => $rc[3]
							]
						)
						) {
							break;
						}
						$check_dependencies = static::check_backward_dependencies(@file_get_json(PLUGINS."/$rc[3]/meta.json"));
						if (!$check_dependencies && $Config->core['simple_admin_mode']) {
							break;
						}
						$a->cancel_button_back = true;
						$a->content(
							h::{'button.uk-button[type=submit]'}(
								$L->{$check_dependencies ? 'disable' : 'force_disable_not_recommended'}
							).
							h::{'input[type=hidden]'}(
								[
									'name'  => 'mode',
									'value' => $rc[2]
								]
							).
							h::{'input[type=hidden]'}(
								[
									'name'  => 'plugin',
									'value' => $rc[3]
								]
							)
						);
					}
					return;
				case 'remove':
					$Page->title($L->complete_removal_of_plugin($_POST['remove_plugin']));
					$a->content(
						h::{'h2.cs-center'}(
							$L->completely_remove_plugin($_POST['remove_plugin'])
						)
					);
					$a->cancel_button_back = true;
					$a->content(
						h::{'button.uk-button[type=submit]'}($L->yes).
						h::{'input[type=hidden]'}(
							[
								'name'  => 'mode',
								'value' => $rc[2]
							]
						).
						h::{'input[type=hidden]'}(
							[
								'name'  => 'plugin',
								'value' => $_POST['remove_plugin']
							]
						)
					);
					return;
			}
		}
		unset($rc);
		$a->buttons     = false;
		$a->file_upload = true;
		$plugins_list   = [];
		foreach ($plugins as $plugin_name) {
			$plugin = [
				'active' => in_array($plugin_name, $Config->components['plugins']),
				'name'   => $plugin_name
			];
			/**
			 * Check if readme available
			 */
			$file = file_exists_with_extension(PLUGINS."/$plugin_name/readme", ['txt', 'html']);
			if ($file) {
				$plugin['readme'] = [
					'type'    => substr($file, -3) == 'txt' ? 'txt' : 'html',
					'content' => file_get_contents($file)
				];
			}
			unset($file);
			/**
			 * Check if license available
			 */
			$file = file_exists_with_extension(PLUGINS."/$plugin_name/license", ['txt', 'html']);
			if ($file) {
				$plugin['license'] = [
					'type'    => substr($file, -3) == 'txt' ? 'txt' : 'html',
					'content' => file_get_contents($file)
				];
			}
			unset($file);
			if (file_exists(PLUGINS."/$plugin_name/meta.json")) {
				$plugin['meta'] = file_get_json(PLUGINS."/$plugin_name/meta.json");
			}
			$plugins_list[] = $plugin;
		}
		unset($plugin_name, $plugin);
		$plugins_for_removal = array_values(
			array_filter(
				$plugins,
				function ($plugin) use ($Config) {
					return !in_array($plugin, $Config->components['plugins']);
				}
			)
		);
		$a->content(
			h::{'cs-system-admin-components-plugins-list script[type=application/json]'}(
				json_encode($plugins_list, JSON_UNESCAPED_UNICODE)
			).
			h::p(
				h::{'input[type=file][name=upload_plugin]'}(
					[
						'style' => 'position: relative;'
					]
				).
				h::{'button.uk-button[type=submit]'}(
					h::icon('upload').$L->upload_and_install_update_plugin,
					[
						'formaction' => "$a->action/enable/upload"
					]
				)
			).
			($plugins_for_removal ? h::p(
				h::{'select[name=remove_plugin]'}($plugins_for_removal).
				h::{'button.uk-button[type=submit]'}(
					h::icon('trash-o').$L->complete_plugin_removal,
					[
						'formaction' => "$a->action/remove"
					]
				)
			) : '')
		);
	}
	static function components_storages () {
		$Config = Config::instance();
		$L      = Language::instance();
		$Page   = Page::instance();
		$a      = Index::instance();
		$rc     = Route::instance()->route;
		if (isset($rc[2])) {
			$a->cancel_button_back = true;
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
					$Page->title(
						$rc[2] == 'edit' ? $L->editing_of_storage($Config->storage[$rc[3]]['host'].'/'.$Config->storage[$rc[3]]['connection']) :
							$L->adding_of_storage
					);
					$a->content(
						h::{'h2.cs-center'}(
							$rc[2] == 'edit' ? $L->editing_of_storage($Config->storage[$rc[3]]['host'].'/'.$Config->storage[$rc[3]]['connection']) :
								$L->adding_of_storage
						).
						static::vertical_table(
							[
								h::info('storage_url'),
								h::input(
									[
										'name'  => 'storage[url]',
										'value' => $rc[2] == 'edit' ? $storage['url'] : ''
									]
								)
							],
							[
								h::info('storage_host'),
								h::input(
									[
										'name'  => 'storage[host]',
										'value' => $rc[2] == 'edit' ? $storage['host'] : ''
									]
								)
							],
							[
								h::info('storage_connection'),
								h::select(
									[
										'in' => _mb_substr(get_files_list(ENGINES.'/Storage', '/^[^_].*?\.php$/i', 'f'), 0, -4)
									],
									[
										'name'     => 'storage[connection]',
										'selected' => $rc[2] == 'edit' ? $storage['connection'] : '',
										'size'     => 5
									]
								)
							],
							[
								h::info('storage_user'),
								h::input(
									[
										'name'  => 'storage[user]',
										'value' => $rc[2] == 'edit' ? $storage['user'] : ''
									]
								)
							],
							[
								h::info('storage_pass'),
								h::input(
									[
										'name'  => 'storage[password]',
										'value' => $rc[2] == 'edit' ? $storage['password'] : ''
									]
								)
							]
						).
						h::{'input[type=hidden]'}(
							[
								'name'  => 'mode',
								'value' => $rc[2] == 'edit' ? 'edit' : 'add'
							]
						).
						(
						isset($rc[3])
							? h::{'input[type=hidden]'}(
							[
								'name'  => 'storage_id',
								'value' => $rc[3]
							]
						)
							: ''
						).
						h::{'button.uk-button'}(
							$L->test_connection,
							[
								'onMouseDown' => "cs.storage_test();"
							]
						)
					);
					break;
				case 'delete':
					$a->buttons = false;
					$modules    = [];
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
								h::{'input[type=hidden]'}(
									[
										'name'  => 'mode',
										'value' => 'delete'
									]
								).
								h::{'input[type=hidden]'}(
									[
										'name'  => 'storage',
										'value' => $rc[3]
									]
								)
							).
							h::{'button.uk-button[type=submit]'}($L->yes)
						);
					}
			}
		} else {
			$a->buttons    = false;
			$storages_list = [];
			$Core          = Core::instance();
			$storages      = $Config->storage;
			if (!empty($storages)) {
				foreach ($storages as $i => &$storage_data) {
					$storages_list[] = [
						[
							($i ?
								h::{'a.uk-button.cs-button-compact'}(
									h::icon('pencil'),
									[
										'href'       => "$a->action/edit/$i",
										'data-title' => "$L->edit $L->storage"
									]
								).
								h::{'a.uk-button.cs-button-compact'}(
									h::icon('trash-o'),
									[
										'href'       => "$a->action/delete/$i",
										'data-title' => "$L->delete $L->storage"
									]
								).
								h::{'a.uk-button.cs-button-compact'}(
									h::icon('signal'),
									[
										'onMouseDown' => "cs.storage_test($i);",
										'data-title'  => $L->test_connection
									]
								) : '-'),
							[
								'class' => $i ? '' : 'text-primary'
							]
						],
						[
							[
								$i ? $storage_data['url'] : $Core->storage_url ?: url_by_source(PUBLIC_STORAGE),
								$i ? $storage_data['host'] : $Core->storage_host,
								$i ? $storage_data['connection'] : $Core->storage_type,
								$i ? $storage_data['user'] : $Core->storage_user ?: '-'
							],
							[
								'class' => $i ? '' : 'text-primary'
							]
						]
					];
				}
				unset($i, $storage_data);
			}
			unset($storages);
			$a->content(
				static::list_center_table(
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
	}
}
