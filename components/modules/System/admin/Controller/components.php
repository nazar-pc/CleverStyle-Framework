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
	cs\DB,
	cs\Index,
	cs\Language,
	h;
trait components {
	static function components_blocks () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_blocks_list()
		);
	}
	static function components_databases () {
		$Config              = Config::instance();
		$L                   = Language::instance();
		$Index               = Index::instance();
		$Index->apply_button = true;
		$Index->content(
			h::cs_system_admin_databases_list().
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
			)
		);
	}
	static function components_modules () {
		$L              = Language::instance();
		$Index          = Index::instance();
		$Index->buttons = false;
		$Index->content(
			h::cs_system_admin_modules_list().
			h::{'button[is=cs-button][icon=refresh][type=submit]'}(
				$L->update_modules_list,
				[
					'tooltip' => $L->update_modules_list_info,
					'name'    => 'update_modules_list'
				]
			)
		);
	}
	static function components_plugins () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_plugins_list()
		);
	}
	static function components_storages () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_storages_list()
		);
	}
}
