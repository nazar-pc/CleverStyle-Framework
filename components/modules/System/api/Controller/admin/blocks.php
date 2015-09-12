<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Config,
	cs\Page,
	cs\Permission,
	cs\Text;
trait blocks {
	static function admin_blocks_get ($route_ids) {
		$Config = Config::instance();
		$Page   = Page::instance();
		$Text   = Text::instance();
		$db_id  = $Config->module('System')->db('texts');
		$blocks = $Config->components['blocks'];
		foreach ($blocks as &$block) {
			$block['title']   = $Text->process($db_id, $block['title'], true);
			$block['content'] = $block['content'] ? $Text->process($db_id, $block['content'], true) : '';
		}
		unset($block);
		if (!isset($route_ids[0])) {
			$Page->json($blocks ?: []);
			return;
		}
		$block = static::get_block_by_index($route_ids[0]);
		if (!$block) {
			error_code(404);
			return;
		}
		$Page->json(
			[
				'title'    => $block['title'],
				'type'     => $block['type'],
				'active'   => (int)$block['active'],
				'template' => $block['template'],
				'start'    => date('Y-m-d\TH:i', $block['start'] ?: TIME),
				'expire'   => [
					'date'  => date('Y-m-d\TH:i', $block['expire'] ?: TIME),
					'state' => (int)($block['expire'] != 0)
				],
				'content'  => $block['content']
			]
		);
		error_code(404);
	}
	static function admin_blocks_post () {
		static::save_block_data($_POST);
	}
	static function admin_blocks_put ($route_ids) {
		if (!$route_ids[0]) {
			error_code(400);
			return;
		}
		static::save_block_data($_POST, $route_ids[0]);
	}
	static function admin_blocks_delete ($route_ids) {
		if (!$route_ids[0]) {
			error_code(400);
			return;
		}
		$Config     = Config::instance();
		$db_id      = $Config->module('System')->db('texts');
		$Permission = Permission::instance();
		$Text       = Text::instance();
		foreach ($Config->components['blocks'] as $i => &$block) {
			if ($block['index'] == $route_ids[0]) {
				unset($Config->components['blocks'][$i]);
				break;
			}
		}
		/** @noinspection PhpUndefinedVariableInspection */
		if ($block['index'] != $route_ids[0]) {
			error_code(404);
			return;
		}
		$block_permission = $Permission->get(null, 'Block', $block['index']);
		if ($block_permission) {
			$Permission->del($block_permission[0]['id']);
		}
		$Text->del($db_id, 'System/Config/blocks/title', $block['index']);
		$Text->del($db_id, 'System/Config/blocks/content', $block['index']);
		if (!$Config->save()) {
			error_code(500);
		}
	}
	static function admin_blocks_templates () {
		Page::instance()->json(
			_mb_substr(get_files_list(TEMPLATES.'/blocks', '/^block\..*?\.(php|html)$/i', 'f'), 6)
		);
	}
	static function admin_blocks_types () {
		Page::instance()->json(
			array_merge(['html', 'raw_html'], _mb_substr(get_files_list(BLOCKS, '/^block\..*?\.php$/i', 'f'), 6, -4))
		);
	}
	/**
	 * @param array     $block_new
	 * @param false|int $index Index of existing block, if not specified - new block being added
	 *
	 * @return bool
	 */
	protected static function save_block_data ($block_new, $index = false) {
		$Config = Config::instance();
		$db_id  = $Config->module('System')->db('texts');
		$Text   = Text::instance();
		$block  = [
			'position' => 'floating',
			'type'     => xap($block_new['type']),
			'index'    => substr(TIME, 3)
		];
		if ($index) {
			$block = static::get_block_by_index($index);
			if (!$block) {
				error_code(404);
				return;
			}
		}
		$block['title']    = $Text->set($db_id, 'System/Config/blocks/title', $block['index'], $block_new['title']);
		$block['active']   = $block_new['active'];
		$block['type']     = $block_new['type'];
		$block['template'] = $block_new['template'];
		$block['start']    = $block_new['start'];
		$block['start']    = strtotime($block_new['start']);
		$block['expire']   = $block_new['expire']['state'] ? strtotime($block_new['expire']['date']) : 0;
		$block['content']  = '';
		if ($block['type'] == 'html') {
			$block['content'] = $Text->set($db_id, 'System/Config/blocks/content', $block['index'], xap($block_new['content'], true));
		} elseif ($block['type'] == 'raw_html') {
			$block['content'] = $Text->set($db_id, 'System/Config/blocks/content', $block['index'], $block_new['content']);
		}
		if (!$index) {
			$Config->components['blocks'][] = $block;
			Permission::instance()->add('Block', $block['index']);
		}
		if (!$Config->save()) {
			error_code(500);
		}
	}
	/**
	 * @param int $index
	 *
	 * @return array|false
	 */
	protected static function get_block_by_index ($index) {
		foreach (Config::instance()->components['blocks'] as &$block) {
			if ($block['index'] == $index) {
				return $block;
			}
		}
		return false;
	}
}
