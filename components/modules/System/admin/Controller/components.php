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
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_databases_list()
		);
	}
	static function components_modules () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_modules_list()
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
